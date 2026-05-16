@extends('layouts.app')

@section('title', 'Import Details - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="fas fa-cloud-upload-alt text-primary"></i> Import Details</h1>
                    <p class="text-muted mb-0">ID: {{ $import->id }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.import.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Import Information</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Model Type:</th>
                                <td><span class="badge bg-info text-dark">{{ ucfirst($import->model_type) }}</span></td>
                            </tr>
                            <tr>
                                <th>Original File:</th>
                                <td><code>{{ $import->original_filename }}</code></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($import->status == 'completed')
                                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> Completed</span>
                                    @elseif($import->status == 'failed')
                                        <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Failed</span>
                                    @elseif($import->status == 'processing')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-spinner fa-spin"></i> Processing</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($import->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Total Rows:</th>
                                <td>{{ number_format($import->total_rows) }}</td>
                            </tr>
                            <tr>
                                <th>Processed:</th>
                                <td><span class="text-primary font-weight-bold">{{ number_format($import->processed_rows) }}</span></td>
                            </tr>
                            <tr>
                                <th>Successful:</th>
                                <td><span class="text-success font-weight-bold">{{ number_format($import->successful_rows) }}</span></td>
                            </tr>
                            <tr>
                                <th>Failed:</th>
                                <td><span class="text-danger font-weight-bold">{{ number_format($import->failed_rows) }}</span></td>
                            </tr>
                            <tr>
                                <th>Requested At:</th>
                                <td>{{ $import->created_at->format('M j, Y H:i:s') }}</td>
                            </tr>
                            @if($import->completed_at)
                                <tr>
                                    <th>Completed At:</th>
                                    <td>{{ $import->completed_at->format('M j, Y H:i:s') }} ({{ $import->completed_at->diffForHumans($import->created_at) }} duration)</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            @if($import->errors && count($import->errors) > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Import Errors</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">Row</th>
                                        <th>Error Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($import->errors as $error)
                                        <tr>
                                            <td>{{ $error['row'] ?? 'N/A' }}</td>
                                            <td class="text-danger">{{ $error['message'] ?? (isset($error['general']) ? $error['general'] : json_encode($error)) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Progress Monitoring</h5>
                </div>
                <div class="card-body">
                    @php
                        $percentage = $import->total_rows > 0 ? ($import->processed_rows / $import->total_rows) * 100 : 0;
                        if ($import->status == 'completed') $percentage = 100;
                    @endphp
                    <div class="text-center mb-3">
                        <h3 class="mb-0">{{ round($percentage, 1) }}%</h3>
                        <small class="text-muted">Overall Completion</small>
                    </div>
                    <div class="progress mb-4" style="height: 10px;">
                        <div class="progress-bar {{ $import->status == 'failed' ? 'bg-danger' : ($import->status == 'completed' ? 'bg-success' : 'bg-primary stripe-animated') }}" 
                             role="progressbar" style="width: {{ $percentage }}%"></div>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-check text-success mr-2"></i> Success Rate</span>
                            <span class="font-weight-bold">{{ $import->processed_rows > 0 ? round(($import->successful_rows / $import->processed_rows) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-times text-danger mr-2"></i> Failure Rate</span>
                            <span class="font-weight-bold">{{ $import->processed_rows > 0 ? round(($import->failed_rows / $import->processed_rows) * 100, 1) : 0 }}%</span>
                        </div>
                    </div>

                    @if($import->status == 'processing')
                        <button onclick="window.location.reload()" class="btn btn-primary btn-block w-100 mt-4">
                            <i class="fas fa-sync"></i> Refresh Progress
                        </button>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-user-circle"></i> Requester Info</h5>
                </div>
                <div class="card-body text-center">
                    <img src="https://ui-avatars.com/api/?name={{ $import->user->name }}&background=random" 
                         class="rounded-circle mb-3" width="64" height="64">
                    <h6>{{ $import->user->name }}</h6>
                    <small class="text-muted">{{ $import->user->email }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.stripe-animated {
    background-image: linear-gradient(45deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);
    background-size: 40px 40px;
    animation: progress-bar-stripes 1s linear infinite;
}
</style>
@endsection
