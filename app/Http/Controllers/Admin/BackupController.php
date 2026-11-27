<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupMonitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupController extends Controller
{
    public function index()
    {
        $backups = BackupMonitoring::orderBy('started_at', 'desc')->paginate(20);
        $recentBackups = BackupMonitoring::recent(7)->get();
        
        // Get backup statistics
        $backupStats = [
            'total_backups' => BackupMonitoring::count(),
            'successful_backups' => BackupMonitoring::successful()->count(),
            'failed_backups' => BackupMonitoring::failed()->count(),
            'last_backup' => BackupMonitoring::orderBy('started_at', 'desc')->first(),
            'total_size' => BackupMonitoring::successful()->sum('size_bytes'),
            'success_rate' => BackupMonitoring::count() > 0 
                ? round((BackupMonitoring::successful()->count() / BackupMonitoring::count()) * 100, 2) 
                : 0,
        ];

        $lastBackup = $backupStats['last_backup'];

        return view('admin.backup.index', compact('backups', 'backupStats', 'recentBackups', 'lastBackup'));
    }

    public function create()
    {
        return view('admin.backup.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'backup_type' => 'required|in:manual,daily,weekly,monthly',
            'disks' => 'required|array',
            'disks.*' => 'string',
        ]);

        try {
            // Create backup monitoring record
            $backup = BackupMonitoring::create([
                'backup_type' => $request->backup_type,
                'status' => 'started',
                'disk' => implode(',', $request->disks),
                'started_at' => now(),
                'metadata' => [
                    'initiated_by' => auth()->user()->name,
                    'ip_address' => $request->ip(),
                ],
            ]);

            // Run backup command
            $exitCode = Artisan::call('backup:run', [
                '--only-to-disk' => $request->disks[0],
                '--disable-notifications' => false,
            ]);

            if ($exitCode === 0) {
                // Get backup info
                $backupDestination = BackupDestination::create($request->disks[0], config('backup.backup.name'));
                $backupFiles = $backupDestination->backups();
                
                if ($backupFiles->isNotEmpty()) {
                    $latestBackup = $backupFiles->first();
                    
                    $backup->update([
                        'status' => 'completed',
                        'path' => $latestBackup->path(),
                        'size_bytes' => $latestBackup->size(),
                        'files' => [$latestBackup->path()],
                        'completed_at' => now(),
                    ]);
                }
            } else {
                $backup->update([
                    'status' => 'failed',
                    'error_message' => Artisan::output(),
                    'completed_at' => now(),
                ]);
            }

            return redirect()->route('admin.backups.index')
                ->with('success', 'Backup completed successfully.');

        } catch (\Exception $e) {
            if (isset($backup)) {
                $backup->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }

            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function show(BackupMonitoring $backup)
    {
        return view('admin.backup.show', compact('backup'));
    }

    public function download(BackupMonitoring $backup)
    {
        if (!$backup->isSuccessful() || !$backup->path) {
            abort(404, 'Backup file not available.');
        }

        $disk = explode(',', $backup->disk)[0]; // Get first disk
        
        if (!Storage::disk($disk)->exists($backup->path)) {
            abort(404, 'Backup file not found.');
        }

        return Storage::disk($disk)->download($backup->path);
    }

    public function destroy(BackupMonitoring $backup)
    {
        try {
            // Delete backup file if exists
            if ($backup->path) {
                $disks = explode(',', $backup->disk);
                foreach ($disks as $disk) {
                    if (Storage::disk($disk)->exists($backup->path)) {
                        Storage::disk($disk)->delete($backup->path);
                    }
                }
            }

            $backup->delete();

            return redirect()->route('admin.backup.index')
                ->with('success', 'Backup deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }

    public function cleanup()
    {
        try {
            $exitCode = Artisan::call('backup:clean');

            if ($exitCode === 0) {
                return back()->with('success', 'Old backups cleaned up successfully.');
            } else {
                return back()->with('error', 'Backup cleanup failed: ' . Artisan::output());
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Backup cleanup failed: ' . $e->getMessage());
        }
    }

    public function list()
    {
        $backups = [];
        
        foreach (config('backup.backup.destination.disks') as $disk) {
            $backupDestination = BackupDestination::create($disk, config('backup.backup.name'));
            $backupFiles = $backupDestination->backups();
            
            foreach ($backupFiles as $backupFile) {
                $backups[] = [
                    'disk' => $disk,
                    'path' => $backupFile->path(),
                    'size' => $backupFile->size(),
                    'date' => $backupFile->date(),
                    'size_formatted' => $this->formatBytes($backupFile->size()),
                ];
            }
        }

        // Sort by date (newest first)
        usort($backups, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return view('admin.backup.list', compact('backups'));
    }

    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
