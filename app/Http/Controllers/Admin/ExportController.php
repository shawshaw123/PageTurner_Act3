<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BooksExport;
use App\Http\Controllers\Controller;
use App\Models\ExportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function index()
    {
        $exports = ExportLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.exports.index', compact('exports'));
    }

    public function create()
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        return view('admin.exports.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'model_type' => 'required|in:books,orders,users',
            'format' => 'required|in:xlsx,csv,pdf',
            'columns' => 'required|array',
            'columns.*' => 'string',
            'filters' => 'sometimes|array',
        ]);

        // Create export log
        $exportLog = ExportLog::create([
            'user_id' => Auth::id(),
            'model_type' => $request->model_type,
            'format' => $request->format,
            'columns' => $request->columns,
            'filters' => $request->filters ?? [],
            'total_records' => 0,
            'status' => 'pending',
        ]);

        // Generate filename
        $filename = 'exports/' . Str::uuid() . '.' . $request->format;
        $exportLog->file_path = $filename;
        $exportLog->save();

        try {
            if ($request->model_type === 'books') {
                $export = new BooksExport($request->filters ?? [], $request->columns);
                
                if ($request->format === 'pdf') {
                    // For PDF exports, we'll handle them differently
                    $this->generatePdfExport($exportLog, $export);
                } else {
                    // Queue Excel/CSV exports
                    Excel::queue($export, $filename);
                }
            }
            // Add other export types here

            return redirect()->route('admin.export.show', $exportLog)
                ->with('success', 'Export has been queued for processing.');

        } catch (\Exception $e) {
            $exportLog->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);

            return back()->with('error', 'Failed to start export: ' . $e->getMessage());
        }
    }

    public function show(ExportLog $export)
    {
        $export->load('user');
        return view('admin.exports.show', compact('export'));
    }

    public function download(ExportLog $export)
    {
        if (!$export->isCompleted() || $export->isExpired()) {
            abort(404, 'Export not available for download.');
        }

        $filePath = storage_path('app/' . $export->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'Export file not found.');
        }

        $originalName = Str::slug($export->model_type) . '_export_' . $export->created_at->format('Y-m-d_H-i-s') . '.' . $export->format;
        
        return response()->download($filePath, $originalName);
    }

    protected function generatePdfExport(ExportLog $exportLog, $export)
    {
        // Update status to processing
        $exportLog->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            // Get the data
            $data = $export->query()->get();
            
            // Update total records
            $exportLog->update(['total_records' => $data->count()]);

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.pdf.books', [
                'books' => $data,
                'columns' => $exportLog->columns,
                'filters' => $exportLog->filters,
            ]);

            // Save PDF
            Storage::disk('local')->put($exportLog->file_path, $pdf->output());

            // Update export log
            $exportLog->update([
                'status' => 'completed',
                'completed_at' => now(),
                'download_url' => route('admin.export.download', $exportLog),
                'expires_at' => now()->addDays(7), // Expire after 7 days
            ]);

        } catch (\Exception $e) {
            $exportLog->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);
        }
    }

    public function destroy(ExportLog $export)
    {
        // Delete file if exists
        if ($export->file_path && Storage::disk('local')->exists($export->file_path)) {
            Storage::disk('local')->delete($export->file_path);
        }

        $export->delete();

        return redirect()->route('admin.exports.index')
            ->with('success', 'Export log deleted successfully.');
    }

    public function cleanupExpired()
    {
        $expiredExports = ExportLog::where('expires_at', '<', now())->get();
        
        foreach ($expiredExports as $export) {
            if ($export->file_path && Storage::disk('local')->exists($export->file_path)) {
                Storage::disk('local')->delete($export->file_path);
            }
            $export->delete();
        }

        return back()->with('success', 'Cleaned up ' . $expiredExports->count() . ' expired exports.');
    }
}
