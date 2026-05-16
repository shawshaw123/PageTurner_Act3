@extends('layouts.app')

@section('title', 'API Rate Limits - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-tachometer-alt"></i> API Rate Limits</h1>
                <div>
                    <form action="{{ route('admin.api-rate-limits.export') }}" method="GET" class="d-inline">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download"></i> Export Data
                        </button>
                    </form>
                    <a href="{{ route('admin.api-rate-limits.statistics') }}" class="btn btn-info ml-2">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                    <button onclick="location.reload()" class="btn btn-secondary ml-2">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-filter"></i> Filter Rate Limits</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.api-rate-limits.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="identifier_type" class="form-label">Identifier Type</label>
                                    <select name="identifier_type" id="identifier_type" class="form-control">
                                        <option value="">All Types</option>
                                        <option value="user" {{ request('identifier_type') == 'user' ? 'selected' : '' }}>User</option>
                                        <option value="ip" {{ request('identifier_type') == 'ip' ? 'selected' : '' }}>IP Address</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tier" class="form-label">Tier</label>
                                    <select name="tier" id="tier" class="form-control">
                                        <option value="">All Tiers</option>
                                        <option value="public" {{ request('tier') == 'public' ? 'selected' : '' }}>Public</option>
                                        <option value="auth" {{ request('tier') == 'auth' ? 'selected' : '' }}>Auth</option>
                                        <option value="standard" {{ request('tier') == 'standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="premium" {{ request('tier') == 'premium' ? 'selected' : '' }}>Premium</option>
                                        <option value="admin" {{ request('tier') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="endpoint" class="form-label">Endpoint</label>
                                    <input type="text" name="endpoint" id="endpoint" class="form-control" value="{{ request('endpoint') }}" placeholder="e.g., /api/books">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="show_hits_only" class="form-label">Show Hits Only</label>
                                    <select name="show_hits_only" id="show_hits_only" class="form-control">
                                        <option value="">All Requests</option>
                                        <option value="1" {{ request('show_hits_only') == '1' ? 'selected' : '' }}>Rate Limit Hits Only</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_from" class="form-label">Date From</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_to" class="form-label">Date To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                                <a href="{{ route('admin.api-rate-limits.index') }}" class="btn btn-secondary ml-2">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Rate Limit Entries</h5>
                </div>
                <div class="card-body">
                    @if($rateLimits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Identifier</th>
                                        <th>Type</th>
                                        <th>Endpoint</th>
                                        <th>Tier</th>
                                        <th>Requests</th>
                                        <th>Limit</th>
                                        <th>Usage</th>
                                        <th>Window Start</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rateLimits as $rateLimit)
                                        <tr>
                                            <td>{{ $rateLimit->identifier }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $rateLimit->identifier_type }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $rateLimit->endpoint }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $rateLimit->tier == 'admin' ? 'danger' : ($rateLimit->tier == 'premium' ? 'warning' : ($rateLimit->tier == 'standard' ? 'success' : 'secondary')) }}">
                                                    {{ $rateLimit->tier }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($rateLimit->requests_count) }}</td>
                                            <td>{{ number_format($rateLimit->limit) }}</td>
                                            <td>
                                                @php
                                                    $usagePercent = $rateLimit->limit > 0 ? ($rateLimit->requests_count / $rateLimit->limit) * 100 : 0;
                                                @endphp
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-{{ $usagePercent >= 90 ? 'danger' : ($usagePercent >= 70 ? 'warning' : 'success') }}" 
                                                         role="progressbar" 
                                                         style="width: {{ min($usagePercent, 100) }}%">
                                                        {{ round($usagePercent, 1) }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $rateLimit->window_start->format('M j, Y H:i:s') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @if($rateLimit->identifier_type == 'user')
                                                        <button onclick="confirmClearUserLimit({{ $rateLimit->identifier }})" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-eraser"></i> Clear
                                                        </button>
                                                        <button onclick="confirmBlockUser({{ $rateLimit->identifier }})" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-ban"></i> Block
                                                        </button>
                                                    @endif
                                                    
                                                    @if($rateLimit->identifier_type == 'ip')
                                                        <button onclick="confirmClearIpLimit('{{ $rateLimit->identifier }}')" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-eraser"></i> Clear
                                                        </button>
                                                        <button onclick="confirmBlockIp('{{ $rateLimit->identifier }}')" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-ban"></i> Block
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $rateLimits->links() }}
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No rate limit entries found matching your criteria.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmClearUserLimit(userId) {
    if (confirm('Are you sure you want to clear the rate limit for this user?')) {
        fetch(`/admin/api-rate-limits/clear-user/${userId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}

function confirmBlockUser(userId) {
    if (confirm('Are you sure you want to block this user from API access?')) {
        fetch(`/admin/api-rate-limits/block-user/${userId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}

function confirmClearIpLimit(ip) {
    if (confirm('Are you sure you want to clear the rate limit for this IP address?')) {
        fetch(`/admin/api-rate-limits/clear-ip/${ip}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}

function confirmBlockIp(ip) {
    if (confirm('Are you sure you want to block this IP address from API access?')) {
        fetch(`/admin/api-rate-limits/block-ip/${ip}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}
</script>
@endsection
