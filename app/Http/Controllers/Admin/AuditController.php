<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use App\Models\AuditArchive;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::with(['user', 'auditable']);

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by event type
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by auditable type
        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', 'App\\Models\\' . ucfirst($request->auditable_type));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by IP address or URL
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%");
            });
        }

        $audits = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get statistics
        $stats = [
            'total_audits' => Audit::count(),
            'today_audits' => Audit::whereDate('created_at', today())->count(),
            'critical_events' => Audit::whereIn('event', ['deleted', 'updated'])->count(),
            'unique_users' => Audit::distinct('user_id')->count('user_id'),
        ];

        return view('admin.audit.index', compact('audits', 'stats'));
    }

    public function show(Audit $audit)
    {
        $audit->load(['user', 'auditable']);
        
        // Parse old and new values for better display
        $oldValues = json_decode($audit->old_values, true) ?: [];
        $newValues = json_decode($audit->new_values, true) ?: [];

        // Calculate changes
        $changes = [];
        foreach ($oldValues as $key => $oldValue) {
            $newValue = $newValues[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return view('admin.audit.show', compact('audit', 'oldValues', 'newValues', 'changes'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf',
            'filters' => 'sometimes|array',
        ]);

        $query = Audit::with(['user', 'auditable']);

        // Apply filters
        if ($request->filled('filters.user_id')) {
            $query->where('user_id', $request->filters['user_id']);
        }

        if ($request->filled('filters.event')) {
            $query->where('event', $request->filters['event']);
        }

        if ($request->filled('filters.auditable_type')) {
            $query->where('auditable_type', 'App\\Models\\' . ucfirst($request->filters['auditable_type']));
        }

        if ($request->filled('filters.date_from')) {
            $query->whereDate('created_at', '>=', $request->filters['date_from']);
        }

        if ($request->filled('filters.date_to')) {
            $query->whereDate('created_at', '<=', $request->filters['date_to']);
        }

        $audits = $query->orderBy('created_at', 'desc')->get();

        if ($request->format === 'csv') {
            return $this->exportCsv($audits);
        } else {
            return $this->exportPdf($audits);
        }
    }

    protected function exportCsv($audits)
    {
        $filename = 'audit_trail_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($audits) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'ID',
                'User',
                'Event',
                'Model Type',
                'Model ID',
                'IP Address',
                'URL',
                'User Agent',
                'Created At',
            ]);

            // Data rows
            foreach ($audits as $audit) {
                fputcsv($file, [
                    $audit->id,
                    $audit->user ? $audit->user->name : 'System',
                    $audit->event,
                    class_basename($audit->auditable_type),
                    $audit->auditable_id,
                    $audit->ip_address,
                    $audit->url,
                    $audit->user_agent,
                    $audit->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportPdf($audits)
    {
        $filename = 'audit_trail_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.audits.pdf', [
            'audits' => $audits,
            'generated_at' => now(),
        ]);

        return $pdf->download($filename);
    }

    public function archives()
    {
        $archives = AuditArchive::orderBy('archived_at', 'desc')->paginate(20);
        
        return view('admin.audit.archives', compact('archives'));
    }

    public function downloadArchive(AuditArchive $archive)
    {
        $filePath = storage_path('app/' . $archive->archive_file);
        
        if (!file_exists($filePath)) {
            abort(404, 'Archive file not found.');
        }

        return response()->download($filePath, 'audit_archive_' . $archive->archived_at->format('Y-m-d') . '.json');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $audits = Audit::with(['user', 'auditable'])
            ->where(function ($q) use ($query) {
                $q->where('event', 'like', "%{$query}%")
                  ->orWhere('auditable_type', 'like', "%{$query}%")
                  ->orWhere('url', 'like', "%{$query}%")
                  ->orWhere('ip_address', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($audits->map(function ($audit) {
            return [
                'id' => $audit->id,
                'event' => $audit->event,
                'model_type' => class_basename($audit->auditable_type),
                'model_id' => $audit->auditable_id,
                'user' => $audit->user ? $audit->user->name : 'System',
                'created_at' => $audit->created_at->format('Y-m-d H:i:s'),
                'url' => $audit->url,
            ];
        }));
    }

    public function statistics()
    {
        $stats = [
            'total_audits' => Audit::count(),
            'today_audits' => Audit::whereDate('created_at', today())->count(),
            'this_week_audits' => Audit::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month_audits' => Audit::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            
            'events' => Audit::selectRaw('event, COUNT(*) as count')
                           ->groupBy('event')
                           ->orderBy('count', 'desc')
                           ->get(),
            
            'models' => Audit::selectRaw('auditable_type, COUNT(*) as count')
                           ->groupBy('auditable_type')
                           ->orderBy('count', 'desc')
                           ->get()
                           ->map(function ($item) {
                               return [
                                   'type' => class_basename($item->auditable_type),
                                   'count' => $item->count,
                               ];
                           }),
            
            'users' => Audit::with('user')
                           ->selectRaw('user_id, COUNT(*) as count')
                           ->groupBy('user_id')
                           ->orderBy('count', 'desc')
                           ->limit(10)
                           ->get()
                           ->map(function ($item) {
                               return [
                                   'user' => $item->user ? $item->user->name : 'System',
                                   'count' => $item->count,
                               ];
                           }),
        ];

        return response()->json($stats);
    }
}
