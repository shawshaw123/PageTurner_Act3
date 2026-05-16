@extends('layouts.app')

@section('title', 'Export Details - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="fas fa-file-export text-success"></i> Export Details</h1>
                    <p class="text-muted mb-0">ID: {{ $export->id }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.export.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-success text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Export Information</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Model Type:</th>
                                <td><span class="badge bg-info text-dark">{{ ucfirst($export->model_type) }}</span></td>
                            </tr>
                            <tr>
                                <th>Format:</th>
                                <td><span class="badge bg-primary">{{ strtoupper($export->format) }}</span></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($export->status == 'completed')
                                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> Completed</span>
                                    @elseif($export->status == 'failed')
                                        <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Failed</span>
                                    @elseif($export->status == 'processing')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-spinner fa-spin"></i> Processing</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($export->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Total Records:</th>
                                <td>{{ number_format($export->total_records) }}</td>
                            </tr>
                            <tr>
                                <th>Requested At:</th>
                                <td>{{ $export->created_at->format('M j, Y H:i:s') }} ({{ $export->created_at->diffForHumans() }})</td>
                            </tr>
                            @if($export->started_at)
                                <tr>
                                    <th>Started At:</th>
                                    <td>{{ $export->started_at->format('M j, Y H:i:s') }}</td>
                                </tr>
                            @endif
                            @if($export->completed_at)
                                <tr>
                                    <th>Completed At:</th>
                                    <td>{{ $export->completed_at->format('M j, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td>{{ $export->duration }} seconds</td>
                                </tr>
                            @endif
                            @if($export->expires_at)
                                <tr>
                                    <th>Expires At:</th>
                                    <td>{{ $export->expires_at->format('M j, Y H:i:s') }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filters & Columns</h5>
                </div>
                <div class="card-body">
                    <h6>Selected Columns</h6>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @foreach($export->columns as $column)
                            <span class="badge bg-light text-dark border">{{ $column }}</span>
                        @endforeach
                    </div>

                    <h6>Applied Filters</h6>
                    @if($export->filters && count($export->filters) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Filter</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($export->filters as $key => $value)
                                        @if($value)
                                            <tr>
                                                <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                                <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No filters were applied to this export.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-download"></i> Download Actions</h5>
                </div>
                <div class="card-body text-center py-4">
                    @if($export->status == 'completed')
                        <i class="fas fa-file-excel fa-4x text-success mb-3"></i>
                        <h5>Ready for Download</h5>
                        <p class="text-muted mb-4">This export was successfully generated and is available for download until {{ $export->expires_at->format('M j') }}.</p>
                        <a href="{{ route('admin.export.download', $export->id) }}" class="btn btn-success btn-lg btn-block w-100">
                            <i class="fas fa-cloud-download-alt"></i> Download Now
                        </a>
                    @elseif($export->status == 'failed')
                        <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                        <h5>Export Failed</h5>
                        <p class="text-muted">Something went wrong while generating this export. Please try again or contact support.</p>
                        <button onclick="window.location.reload()" class="btn btn-outline-primary w-100">
                            <i class="fas fa-sync"></i> Retry Export
                        </button>
                    @elseif($export->status == 'processing' || $export->status == 'pending')
                        <div class="spinner-border text-warning mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <h5>Processing...</h5>
                        <p class="text-muted">Your data is being gathered. This may take a few moments depending on the amount of records.</p>
                        <button onclick="window.location.reload()" class="btn btn-outline-info w-100">
                            <i class="fas fa-sync"></i> Refresh Status
                        </button>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-user-circle"></i> Requester Info</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name={{ $export->user->name }}&background=random" 
                             class="rounded-circle me-3" width="48" height="48">
                        <div>
                            <h6 class="mb-0">{{ $export->user->name }}</h6>
                            <small class="text-muted">{{ $export->user->email }}</small>
                            <br>
                            <span class="badge bg-secondary mt-1">{{ ucfirst($export->user->role) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}
.gap-2 {
    gap: 0.5rem;
}
</style>
@endsection
