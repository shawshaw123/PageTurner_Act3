@extends('layouts.app')

@section('title', 'Backup Management - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-database"></i> Backup Management</h1>
                <div>
                    <form action="{{ route('admin.backup.store') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-play"></i> Run Backup Now
                        </button>
                    </form>
                    <form action="{{ route('admin.backup.clean') }}" method="POST" class="d-inline ml-2">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-trash"></i> Clean Old Backups
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history"></i> Recent Backups</h5>
                </div>
                <div class="card-body">
                    @if($backups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Size</th>
                                        <th>Duration</th>
                                        <th>Started</th>
                                        <th>Completed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $backup->backup_type == 'daily' ? 'info' : ($backup->backup_type == 'weekly' ? 'warning' : 'secondary') }}">
                                                    {{ strtoupper($backup->backup_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($backup->status == 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($backup->status == 'failed')
                                                    <span class="badge bg-danger">Failed</span>
                                                @elseif($backup->status == 'running')
                                                    <span class="badge bg-warning">Running</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $backup->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $backup->getSizeFormattedAttribute() }}</td>
                                            <td>
                                                @if($backup->duration_seconds)
                                                    {{ gmdate('H:i:s', $backup->duration_seconds) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $backup->started_at->format('M j, Y H:i:s') }}</td>
                                            <td>
                                                @if($backup->completed_at)
                                                    {{ $backup->completed_at->format('M j, Y H:i:s') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($backup->status == 'completed' && $backup->file_path)
                                                    <a href="{{ route('admin.backup.download', $backup->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                @endif
                                                @if($backup->status == 'running')
                                                    <button class="btn btn-sm btn-warning" disabled>
                                                        <i class="fas fa-spinner fa-spin"></i> Running
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $backups->links() }}
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No backups found. Run your first backup using the button above.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Backup Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <h4 class="text-primary">{{ $backupStats['total_backups'] }}</h4>
                            <small>Total Backups</small>
                        </div>
                        <div class="col-12 mb-3">
                            <h4 class="text-success">{{ $backupStats['successful_backups'] }}</h4>
                            <small>Successful</small>
                        </div>
                        <div class="col-12 mb-3">
                            <h4 class="text-danger">{{ $backupStats['failed_backups'] }}</h4>
                            <small>Failed</small>
                        </div>
                        <div class="col-12">
                            <h4 class="text-info">{{ $backupStats['success_rate'] }}%</h4>
                            <small>Success Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-cog"></i> Backup Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Schedule</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-clock text-info"></i> Daily: 02:00 AM</li>
                                <li><i class="fas fa-calendar-week text-warning"></i> Weekly: Sunday 02:00 AM</li>
                                <li><i class="fas fa-calendar-alt text-secondary"></i> Monthly: 1st 02:00 AM</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Retention Policy</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-database text-primary"></i> 7 Daily Backups</li>
                                <li><i class="fas fa-database text-warning"></i> 4 Weekly Backups</li>
                                <li><i class="fas fa-database text-secondary"></i> 12 Monthly Backups</li>
                            </ul>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <strong>Last Backup:</strong>
                                @if($lastBackup)
                                    {{ $lastBackup->started_at->format('M j, Y H:i') }} - 
                                    <span class="badge bg-{{ $lastBackup->status == 'completed' ? 'success' : 'danger' }}">
                                        {{ $lastBackup->status }}
                                    </span>
                                @else
                                    <span class="text-muted">No backups run yet</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
