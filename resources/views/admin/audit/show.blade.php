@extends('layouts.app')

@section('title', 'Audit Details - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="fas fa-shield-alt text-primary"></i> Audit Details</h1>
                    <p class="text-muted mb-0">Audit ID: {{ $audit->id }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Logs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Event Context</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Event:</th>
                                <td>
                                    <span class="badge bg-{{ $audit->event == 'deleted' ? 'danger' : ($audit->event == 'created' ? 'success' : 'warning') }} font-weight-bold">
                                        {{ strtoupper($audit->event) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Model:</th>
                                <td><code>{{ class_basename($audit->auditable_type) }}</code> (ID: {{ $audit->auditable_id }})</td>
                            </tr>
                            <tr>
                                <th>User:</th>
                                <td>
                                    @if($audit->user)
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name={{ $audit->user->name }}&background=random" 
                                                 class="rounded-circle me-2" width="24" height="24">
                                            <span>{{ $audit->user->name }}</span>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary">System</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>IP Address:</th>
                                <td><span class="badge bg-light text-dark border">{{ $audit->ip_address }}</span></td>
                            </tr>
                            <tr>
                                <th>URL:</th>
                                <td><small class="text-break">{{ $audit->url }}</small></td>
                            </tr>
                            <tr>
                                <th>User Agent:</th>
                                <td><small class="text-muted">{{ $audit->user_agent }}</small></td>
                            </tr>
                            <tr>
                                <th>Recorded At:</th>
                                <td>{{ $audit->created_at->format('M j, Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Changes Trail</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="20%">Field</th>
                                    <th width="40%">Old Value</th>
                                    <th width="40%">New Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($changes as $field => $change)
                                    <tr>
                                        <td class="font-weight-bold">{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                        <td class="bg-light-danger">
                                            <div class="text-danger strike-through">{{ is_array($change['old']) ? json_encode($change['old']) : (is_null($change['old']) ? 'NULL' : $change['old']) }}</div>
                                        </td>
                                        <td class="bg-light-success">
                                            <div class="text-success">{{ is_array($change['new']) ? json_encode($change['new']) : (is_null($change['new']) ? 'NULL' : $change['new']) }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    @if($audit->event == 'created')
                                        @foreach($newValues as $field => $value)
                                            <tr>
                                                <td class="font-weight-bold text-muted">{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                                <td>-</td>
                                                <td class="text-success">{{ is_array($value) ? json_encode($value) : (is_null($value) ? 'NULL' : $value) }}</td>
                                            </tr>
                                        @endforeach
                                    @elseif($audit->event == 'deleted')
                                        @foreach($oldValues as $field => $value)
                                            <tr>
                                                <td class="font-weight-bold text-muted">{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                                <td class="text-danger strike-through">{{ is_array($value) ? json_encode($value) : (is_null($value) ? 'NULL' : $value) }}</td>
                                                <td>-</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No data changes recorded for this event.</td>
                                        </tr>
                                    @endif
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
}
.strike-through {
    text-decoration: line-through;
    opacity: 0.7;
}
.bg-light-danger {
    background-color: rgba(231, 74, 59, 0.05);
}
.bg-light-success {
    background-color: rgba(28, 200, 138, 0.05);
}
</style>
@endsection
