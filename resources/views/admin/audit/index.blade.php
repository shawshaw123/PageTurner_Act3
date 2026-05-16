@extends('layouts.app')

@section('title', 'Audit Logs - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-search"></i> Audit Logs</h1>
                <div>
                    <form action="{{ route('admin.audit.export') }}" method="GET" class="d-inline">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download"></i> Export Logs
                        </button>
                    </form>
                    <a href="{{ route('admin.audit.statistics') }}" class="btn btn-info ml-2">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-filter"></i> Filter Audit Logs</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.audit.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="user_id" class="form-label">User</label>
                                    <select name="user_id" id="user_id" class="form-control">
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="event" class="form-label">Event</label>
                                    <select name="event" id="event" class="form-control">
                                        <option value="">All Events</option>
                                        <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                                        <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                                        <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                                        <option value="login" {{ request('event') == 'login' ? 'selected' : '' }}>Login</option>
                                        <option value="logout" {{ request('event') == 'logout' ? 'selected' : '' }}>Logout</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="auditable_type" class="form-label">Model</label>
                                    <select name="auditable_type" id="auditable_type" class="form-control">
                                        <option value="">All Models</option>
                                        <option value="App\Models\User" {{ request('auditable_type') == 'App\Models\User' ? 'selected' : '' }}>User</option>
                                        <option value="App\Models\Book" {{ request('auditable_type') == 'App\Models\Book' ? 'selected' : '' }}>Book</option>
                                        <option value="App\Models\Order" {{ request('auditable_type') == 'App\Models\Order' ? 'selected' : '' }}>Order</option>
                                        <option value="App\Models\Review" {{ request('auditable_type') == 'App\Models\Review' ? 'selected' : '' }}>Review</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_from" class="form-label">Date From</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_to" class="form-label">Date To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary ml-2">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
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
                    <h5><i class="fas fa-list"></i> Audit Log Entries</h5>
                </div>
                <div class="card-body">
                    @if($audits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Event</th>
                                        <th>Model</th>
                                        <th>Model ID</th>
                                        <th>IP Address</th>
                                        <th>User Agent</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($audits as $audit)
                                        <tr>
                                            <td>{{ $audit->id }}</td>
                                            <td>
                                                @if($audit->user)
                                                    {{ $audit->user->name }}
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $audit->event == 'created' ? 'success' : ($audit->event == 'updated' ? 'warning' : ($audit->event == 'deleted' ? 'danger' : 'info')) }}">
                                                    {{ $audit->event }}
                                                </span>
                                            </td>
                                            <td>{{ class_basename($audit->auditable_type) }}</td>
                                            <td>{{ $audit->auditable_id ?? '-' }}</td>
                                            <td>{{ $audit->ip_address }}</td>
                                            <td>
                                                <small class="text-muted">{{ Str::limit($audit->user_agent, 50) }}</small>
                                            </td>
                                            <td>{{ $audit->created_at->format('M j, Y H:i:s') }}</td>
                                            <td>
                                                <a href="{{ route('admin.audit.show', $audit->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $audits->links() }}
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No audit logs found matching your criteria.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
