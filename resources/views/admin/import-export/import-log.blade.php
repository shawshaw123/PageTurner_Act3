@extends('layouts.app')

@section('title', 'Import Log Details')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-10 h-10 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold">Import Log Details</h1>
                    <p class="text-green-100 mt-2">{{ $importLog->original_filename }}</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                    {{ $importLog->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $importLog->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $importLog->status === 'processing' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                    {{ ucfirst($importLog->status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Rows</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $importLog->total_rows }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Successful</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $importLog->successful_rows }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Failed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $importLog->failed_rows }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Duration</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $importLog->duration }}s</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-medium text-gray-900">Progress</h3>
            <span class="text-sm text-gray-500">{{ $importLog->progress_percentage }}% Complete</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-gradient-to-r from-green-500 to-teal-600 h-3 rounded-full transition-all duration-300" 
                 style="width: {{ $importLog->progress_percentage }}%"></div>
        </div>
        <div class="mt-2 flex justify-between text-xs text-gray-500">
            <span>{{ $importLog->processed_rows }} rows processed</span>
            <span>{{ $importLog->success_rate }}% success rate</span>
        </div>
    </div>

    <!-- Import Details -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Import Details</h3>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">File Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $importLog->original_filename }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Model Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($importLog->model_type) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Started At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $importLog->started_at ? $importLog->started_at->format('M j, Y g:i A') : 'Not started' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $importLog->completed_at ? $importLog->completed_at->format('M j, Y g:i A') : 'In progress' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">User</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $importLog->user ? $importLog->user->name : 'System' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Options</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($importLog->options)
                            @if($importLog->options['update_existing'] ?? false)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Update Existing
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Skip Duplicates
                                </span>
                            @endif
                        @else
                            Default settings
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Errors Section -->
    @if($importLog->failed_rows > 0 && $importLog->errors)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Import Errors ({{ $importLog->failed_rows }})</h3>
            <div class="flex items-center space-x-2">
                <button onclick="toggleAllErrors()" class="text-sm text-blue-600 hover:text-blue-800">
                    <span id="toggleText">Show All</span>
                </button>
                <button onclick="exportErrors()" class="text-sm text-green-600 hover:text-green-800">
                    Export Errors
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="space-y-4" id="errorsContainer">
                @foreach($importLog->errors as $index => $error)
                    <div class="border border-red-200 rounded-lg overflow-hidden {{ $index >= 5 ? 'hidden error-item' : '' }}">
                        <div class="bg-red-50 px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm font-medium text-red-800">
                                    Row {{ $error['row'] }}: {{ $error['error'] }}
                                </span>
                            </div>
                            <button onclick="toggleErrorDetails({{ $index }})" class="text-red-600 hover:text-red-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="px-4 py-3 bg-white border-t border-red-200 hidden" id="errorDetails{{ $index }}">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Row Data:</h4>
                            <div class="bg-gray-50 rounded p-3">
                                <pre class="text-xs text-gray-600 overflow-x-auto">{{ json_encode($error['data'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if(count($importLog->errors) > 5)
            <div class="mt-4 text-center">
                <button onclick="toggleAllErrors()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <span id="toggleButtonText">Show {{ count($importLog->errors) - 5 }} More Errors</span>
                </button>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="flex justify-between">
        <a href="{{ route('admin.import-export.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
            ← Back to Data Management
        </a>
        
        @if($importLog->status === 'failed')
            <button onclick="retryImport()" class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                Retry Import
            </button>
        @endif
    </div>
</div>

<script>
let allErrorsVisible = false;

function toggleErrorDetails(index) {
    const details = document.getElementById(`errorDetails${index}`);
    const button = event.currentTarget;
    const icon = button.querySelector('svg');
    
    if (details.classList.contains('hidden')) {
        details.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        details.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleAllErrors() {
    const hiddenErrors = document.querySelectorAll('.error-item');
    const toggleText = document.getElementById('toggleText');
    const toggleButtonText = document.getElementById('toggleButtonText');
    
    if (!allErrorsVisible) {
        hiddenErrors.forEach(error => error.classList.remove('hidden'));
        toggleText.textContent = 'Show Less';
        if (toggleButtonText) {
            toggleButtonText.textContent = 'Show Less';
        }
        allErrorsVisible = true;
    } else {
        hiddenErrors.forEach((error, index) => {
            if (index >= 5) error.classList.add('hidden');
        });
        toggleText.textContent = 'Show All';
        if (toggleButtonText) {
            const remainingCount = {{ count($importLog->errors) }} - 5;
            toggleButtonText.textContent = `Show ${remainingCount} More Errors`;
        }
        allErrorsVisible = false;
    }
}

function exportErrors() {
    const errors = @json($importLog->errors);
    const csvContent = "data:text/csv;charset=utf-8," 
        + "Row,Error,Data\n"
        + errors.map(error => 
            `${error.row},"${error.error}","${JSON.stringify(error.data).replace(/"/g, '""')}"`
        ).join("\n");
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "import_errors_{{ $importLog->id }}.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function retryImport() {
    if (confirm('This will retry the import with the same file and settings. Continue?')) {
        // Implement retry logic here
        window.location.href = '{{ route('admin.import-export.books.import') }}';
    }
}

// Auto-refresh for processing imports
@if($importLog->status === 'processing')
setTimeout(() => {
    window.location.reload();
}, 5000);
@endif
</script>
@endsection
