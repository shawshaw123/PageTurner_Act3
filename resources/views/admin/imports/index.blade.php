@extends('layouts.app')

@section('title', 'Import Management - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="fas fa-cloud-upload-alt text-primary"></i> Import Management</h1>
                    <p class="text-muted mb-0">Bulk import data with validation and progress tracking</p>
                </div>
                <div>
                    <a href="{{ route('admin.import.template', ['type' => 'books']) }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-download"></i> Download Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Imports Section -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Recent Imports</h5>
                </div>
                <div class="card-body">
                    @if($imports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-hashtag"></i></th>
                                        <th><i class="fas fa-user"></i> User</th>
                                        <th><i class="fas fa-cube"></i> Model</th>
                                        <th><i class="fas fa-file"></i> File</th>
                                        <th><i class="fas fa-database"></i> Total</th>
                                        <th><i class="fas fa-check-circle"></i> Processed</th>
                                        <th><i class="fas fa-times-circle"></i> Failed</th>
                                        <th><i class="fas fa-info-circle"></i> Status</th>
                                        <th><i class="fas fa-clock"></i> Time</th>
                                        <th><i class="fas fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($imports as $import)
                                        <tr>
                                            <td class="font-weight-bold">{{ $import->id }}</td>
                                            <td>
                                                @if($import->user)
                                                    <div class="d-flex align-items-center">
                                                        <img src="https://ui-avatars.com/api/?name={{ $import->user->name }}&background=random" 
                                                             class="rounded-circle me-2" width="24" height="24">
                                                        <span>{{ $import->user->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-robot"></i> System
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $import->model_type }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-file-excel text-success me-2"></i>
                                                    <span class="text-truncate" style="max-width: 150px;" title="{{ $import->filename }}">
                                                        {{ $import->filename }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ number_format($import->total_rows) }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $import->processed_rows > 0 ? 'success' : 'secondary' }}">
                                                    {{ number_format($import->processed_rows) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $import->failed_rows > 0 ? 'danger' : 'secondary' }}">
                                                    {{ number_format($import->failed_rows) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($import->status == 'completed')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Completed
                                                    </span>
                                                @elseif($import->status == 'failed')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times-circle"></i> Failed
                                                    </span>
                                                @elseif($import->status == 'processing')
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-spinner fa-spin"></i> Processing
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-pause-circle"></i> {{ $import->status }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">{{ $import->created_at->format('M j, H:i') }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.import.show', $import->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($import->file_path)
                                                        <a href="{{ asset('storage/' . $import->file_path) }}" class="btn btn-sm btn-outline-success" title="Download File">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $imports->links() }}
                    @else
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Imports Yet</h5>
                                <p class="text-muted">Start by importing books using the form below</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Import Form Section -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-success text-white">
                    <h5 class="mb-0"><i class="fas fa-cloud-upload-alt"></i> Import Books</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.import.store') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        <div class="row">
                            <!-- File Upload Section -->
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="file" class="form-label font-weight-bold">
                                        <i class="fas fa-file-upload text-primary"></i> Select File
                                    </label>
                                    <div class="input-group">
                                        <div class="custom-file-upload">
                                            <input type="file" name="file" id="file" class="form-control" required 
                                                   accept=".xlsx,.xls,.csv" onchange="updateFileName()">
                                            <label for="file" class="custom-file-label">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span id="fileName">Choose file...</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Supported formats: XLSX, XLS, CSV | Maximum size: 10MB
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Options Section -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="update_existing" class="form-label font-weight-bold">
                                        <i class="fas fa-cog text-primary"></i> Import Options
                                    </label>
                                    <select name="update_existing" id="update_existing" class="form-control">
                                        <option value="0">
                                            <i class="fas fa-skip"></i> Skip duplicates
                                        </option>
                                        <option value="1">
                                            <i class="fas fa-sync"></i> Update existing records
                                        </option>
                                    </select>
                                    <div class="mt-2">
                                        <small class="form-text text-muted">
                                            <i class="fas fa-question-circle"></i> How to handle existing records
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Progress Section -->
                        <div id="importProgress" class="row mt-4" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-spinner fa-spin"></i> Import in Progress...</h6>
                                    <div class="progress mb-3">
                                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                             role="progressbar" style="width: 0%">
                                            0%
                                        </div>
                                    </div>
                                    <div id="progressDetails" class="text-center">
                                        <p class="mb-0">Processing: <span id="processedCount">0</span> / <span id="totalCount">0</span> records</p>
                                        <p class="mb-0">Success: <span id="successCount">0</span> | Failed: <span id="failedCount">0</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                                    <i class="fas fa-cloud-upload-alt"></i> Start Import
                                </button>
                                <a href="{{ route('admin.import.template', ['type' => 'books']) }}" class="btn btn-outline-secondary btn-lg px-5 ml-2">
                                    <i class="fas fa-file-download"></i> Download Template
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.custom-file-upload {
    position: relative;
    display: inline-block;
    width: 100%;
}

.custom-file-upload input[type="file"] {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.custom-file-label {
    position: relative;
    display: inline-block;
    padding: 12px 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
    text-align: left;
    transition: all 0.3s ease;
}

.custom-file-label:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #667eea 100%);
}

.custom-file-label i {
    margin-right: 8px;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.empty-state {
    padding: 3rem;
}

.progress-bar-animated {
    background-image: linear-gradient(45deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);
    background-size: 40px 40px;
    animation: progress-bar-stripes 1s linear infinite;
}
</style>

<script>
function updateFileName() {
    const fileInput = document.getElementById('file');
    const fileNameSpan = document.getElementById('fileName');
    
    if (fileInput.files.length > 0) {
        const fileName = fileInput.files[0].name;
        fileNameSpan.textContent = fileName;
    } else {
        fileNameSpan.textContent = 'Choose file...';
    }
}

// Simulate progress (in real implementation, this would be updated via WebSocket/polling)
function simulateProgress() {
    const progressSection = document.getElementById('importProgress');
    const progressBar = document.getElementById('progressBar');
    const submitBtn = document.getElementById('submitBtn');
    
    progressSection.style.display = 'block';
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 10;
        if (progress > 100) progress = 100;
        
        progressBar.style.width = progress + '%';
        progressBar.textContent = progress + '%';
        
        document.getElementById('processedCount').textContent = Math.floor(progress * 50);
        document.getElementById('totalCount').textContent = 1000;
        document.getElementById('successCount').textContent = Math.floor(progress * 45);
        document.getElementById('failedCount').textContent = Math.floor(progress * 5);
        
        if (progress >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                location.reload();
            }, 2000);
        }
    }, 500);
}

document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();
    simulateProgress();
});
</script>
@endsection
