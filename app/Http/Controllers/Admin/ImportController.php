<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\BooksImport;
use App\Models\ImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        $imports = ImportLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.imports.index', compact('imports'));
    }

    public function create()
    {
        return view('admin.imports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'model_type' => 'required|in:books,users',
            'update_existing' => 'boolean',
        ]);

        $file = $request->file('file');
        $filename = 'imports/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Store the file
        $path = $file->storeAs('imports', $filename, 'local');
        
        // Create import log
        $importLog = ImportLog::create([
            'user_id' => Auth::id(),
            'model_type' => $request->model_type,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'total_rows' => 0,
            'file_path' => $path,
            'options' => [
                'update_existing' => $request->boolean('update_existing', false),
            ],
        ]);

        // Queue the import
        try {
            if ($request->model_type === 'books') {
                Excel::queueImport(new BooksImport($importLog, $request->only('update_existing')), $path);
            }
            // Add other import types here as needed

            return redirect()->route('admin.import.show', $importLog)
                ->with('success', 'Import has been queued for processing.');
        } catch (\Exception $e) {
            $importLog->update([
                'status' => 'failed',
                'errors' => [['general' => $e->getMessage()]],
                'completed_at' => now(),
            ]);

            return back()->with('error', 'Failed to start import: ' . $e->getMessage());
        }
    }

    public function show(ImportLog $import)
    {
        $import->load('user');
        return view('admin.imports.show', compact('import'));
    }

    public function downloadTemplate($type)
    {
        $filename = match($type) {
            'books' => 'books_import_template.xlsx',
            default => abort(404),
        };

        $path = storage_path("app/templates/{$filename}");
        
        if (!file_exists($path)) {
            $this->createTemplate($type);
        }

        return response()->download($path);
    }

    protected function createTemplate($type)
    {
        $templatePath = storage_path("app/templates");
        
        if (!is_dir($templatePath)) {
            mkdir($templatePath, 0755, true);
        }

        switch ($type) {
            case 'books':
                $headers = ['ISBN', 'Title', 'Author', 'Price', 'Stock', 'Category', 'Description'];
                $sampleData = [
                    ['978-0-321-76572-3', 'Sample Book Title', 'Sample Author', '19.99', '50', 'Fiction', 'This is a sample book description.'],
                    ['978-0-13-468599-1', 'Another Book', 'Another Author', '24.99', '25', 'Non-Fiction', 'Another sample description.'],
                ];
                
                // Create Excel file using Laravel Excel
                return Excel::download(
                    new class($headers, $sampleData) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
                        protected $headers;
                        protected $data;
                        
                        public function __construct($headers, $data) {
                            $this->headers = $headers;
                            $this->data = $data;
                        }
                        
                        public function array(): array {
                            return $this->data;
                        }
                        
                        public function headings(): array {
                            return $this->headers;
                        }
                    },
                    'books_import_template.xlsx'
                );
        }
    }

    public function destroy(ImportLog $import)
    {
        // Delete file if exists
        if ($import->file_path && Storage::disk('local')->exists($import->file_path)) {
            Storage::disk('local')->delete($import->file_path);
        }

        $import->delete();

        return redirect()->route('admin.imports.index')
            ->with('success', 'Import log deleted successfully.');
    }
}
