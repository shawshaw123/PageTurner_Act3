@extends('layouts.app')

@section('title', 'Backup Details - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="fas fa-database text-info"></i> Backup Details</h1>
                    <p class="text-muted mb-0">ID: {{ $backup->id }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.backup.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Monitoring Information</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Backup Type:</th>
                                <td><span class="badge bg-primary">{{ strtoupper($backup->backup_type) }}</span></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($backup->status == 'completed')
                                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> Completed</span>
                                    @elseif($backup->status == 'failed')
                                        <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Failed</span>
                                    @elseif($backup->status == 'started')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-spinner fa-spin"></i> In Progress</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($backup->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Storage Disk:</th>
                                <td><code>{{ $backup->disk }}</code></td>
                            </tr>
                            <tr>
                                <th>File Path:</th>
                                <td>{{ $backup->path ?? 'Not generated' }}</td>
                            </tr>
                            <tr>
                                <th>File Size:</th>
                                <td>{{ $backup->getSizeFormattedAttribute() }}</td>
                            </tr>
                            <tr>
                                <th>Started At:</th>
                                <td>{{ $backup->started_at->format('M j, Y H:i:s') }}</td>
                            </tr>
                            @if($backup->completed_at)
                                <tr>
                                    <th>Completed At:</th>
                                    <td>{{ $backup->completed_at->format('M j, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td>{{ $backup->duration_seconds ?? ($backup->completed_at->diffInSeconds($backup->started_at)) }} seconds</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            @if($backup->error_message)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Error Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="bg-dark text-light p-3 rounded">
                            <pre class="mb-0" style="white-space: pre-wrap; font-size: 0.85rem;">{{ $backup->error_message }}</pre>
                        </div>
                    </div>
                </div>
            @endif

            @if($backup->files && count($backup->files) > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Backup Files</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($backup->files as $file)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <code>{{ $file }}</code>
                                    <i class="fas fa-file-archive text-secondary"></i>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-cog"></i> Actions</h5>
                </div>
                <div class="card-body">
                    @if($backup->status == 'completed' && $backup->path)
                        <a href="{{ route('admin.backup.download', $backup->id) }}" class="btn btn-success btn-lg btn-block w-100 mb-3">
                            <i class="fas fa-cloud-download-alt"></i> Download Backup
                        </a>
                    @endif

                    <form action="{{ route('admin.backup.destroy', $backup->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this backup record and its files?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-block w-100">
                            <i class="fas fa-trash"></i> Delete Record
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-microchip"></i> Metadata</h5>
                </div>
                <div class="card-body">
                    @if($backup->metadata)
                        <div class="list-group list-group-flush">
                            @foreach($backup->metadata as $key => $value)
                                <div class="list-group-item px-0">
                                    <small class="text-muted d-block">{{ ucfirst(str_replace('_', ' ', $key)) }}</small>
                                    <strong>{{ is_array($value) ? json_encode($value) : $value }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No metadata available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-info {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
}
</style>
@endsection
