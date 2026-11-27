@extends('layouts.app')

@section('title', 'Import Books')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <svg class="w-10 h-10 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
            </svg>
            <div>
                <h1 class="text-3xl font-bold">Import Books</h1>
                <p class="text-green-100 mt-2">Bulk import books from Excel or CSV files</p>
            </div>
        </div>
    </div>

    <!-- Import Form -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6">
            <form action="{{ route('admin.import-export.books.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- File Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select File
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-green-500 transition-colors" id="dropZone">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" />
                        </svg>
                        <div class="mt-4">
                            <label for="file" class="cursor-pointer">
                                <span class="mt-2 block text-sm font-medium text-gray-900">
                                    Click to upload or drag and drop
                                </span>
                                <span class="mt-1 block text-xs text-gray-500">
                                    XLSX, CSV up to 10MB
                                </span>
                            </label>
                            <input id="file" name="file" type="file" class="sr-only" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Supported formats: Excel (.xlsx, .xls) and CSV (.csv)
                        </p>
                    </div>
                    @error('file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Import Options -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Import Options</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="update_existing" name="update_existing" type="checkbox" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            </div>
                            <div class="ml-3">
                                <label for="update_existing" class="text-sm font-medium text-gray-700">
                                    Update existing books
                                </label>
                                <p class="text-xs text-gray-500">
                                    If checked, existing books with matching ISBN will be updated instead of being skipped
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Required Fields -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Required Columns</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Your file must contain the following columns (case-sensitive):</p>
                                <ul class="list-disc list-inside mt-1 space-y-1">
                                    <li><code class="bg-blue-100 px-1 rounded">ISBN</code> - Valid ISBN-10 or ISBN-13 format</li>
                                    <li><code class="bg-blue-100 px-1 rounded">Title</code> - Book title (max 255 characters)</li>
                                    <li><code class="bg-blue-100 px-1 rounded">Author</code> - Author name (max 255 characters)</li>
                                    <li><code class="bg-blue-100 px-1 rounded">Price</code> - Positive number (max 9999.99)</li>
                                    <li><code class="bg-blue-100 px-1 rounded">Stock</code> - Non-negative integer</li>
                                    <li><code class="bg-blue-100 px-1 rounded">Category</code> - Must match existing category name</li>
                                    <li><code class="bg-blue-100 px-1 rounded">Description</code> - Optional, max 2000 characters</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.import-export.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        Start Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Template Download -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Download Template</h3>
            <p class="text-sm text-gray-600 mb-4">
                Download our Excel template to ensure your file has the correct format and required columns.
            </p>
            <a href="{{ route('admin.import-export.template') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download Excel Template
            </a>
        </div>
    </div>

    <!-- Recent Imports -->
    @if(isset($recentImports) && $recentImports->count() > 0)
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Imports</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($recentImports as $import)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                @if($import->status === 'completed')
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @elseif($import->status === 'failed')
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">{{ $import->original_filename }}</p>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $import->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $import->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $import->status === 'processing' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                        {{ ucfirst($import->status) }}
                                    </span>
                                </div>
                                <div class="mt-1 flex items-center space-x-4 text-xs text-gray-500">
                                    <span>{{ $import->created_at->format('M j, Y g:i A') }}</span>
                                    @if($import->total_rows > 0)
                                        <span>{{ $import->successful_rows }} / {{ $import->total_rows }} rows</span>
                                    @endif
                                    @if($import->duration)
                                        <span>{{ $import->duration }}s</span>
                                    @endif
                                </div>
                                @if($import->status === 'completed' && $import->failed_rows > 0)
                                    <div class="mt-2">
                                        <a href="{{ route('admin.import-export.import.show', $import->id) }}" class="text-xs text-blue-600 hover:text-blue-800">
                                            View {{ $import->failed_rows }} errors →
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<script>
// File upload drag and drop
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('file');

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-green-500', 'bg-green-50');
});

dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-green-500', 'bg-green-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-green-500', 'bg-green-50');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        updateFileName(files[0].name);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        updateFileName(e.target.files[0].name);
    }
});

function updateFileName(fileName) {
    const fileNameDisplay = dropZone.querySelector('span.text-sm.font-medium.text-gray-900');
    if (fileNameDisplay) {
        fileNameDisplay.textContent = `Selected: ${fileName}`;
    }
}
</script>
@endsection
