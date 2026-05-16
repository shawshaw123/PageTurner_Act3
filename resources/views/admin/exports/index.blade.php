@extends('layouts.app')

@section('title', 'Export Management - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="fas fa-file-export text-success"></i> Export Management</h1>
                    <p class="text-muted mb-0">Export data with advanced filtering and format options</p>
                </div>
                <div>
                    <button onclick="refreshExports()" class="btn btn-outline-success">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Exports Section -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-success text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Recent Exports</h5>
                </div>
                <div class="card-body">
                    @if($exports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-hashtag"></i></th>
                                        <th><i class="fas fa-user"></i> User</th>
                                        <th><i class="fas fa-cube"></i> Model</th>
                                        <th><i class="fas fa-file"></i> Format</th>
                                        <th><i class="fas fa-filter"></i> Filters</th>
                                        <th><i class="fas fa-database"></i> Records</th>
                                        <th><i class="fas fa-info-circle"></i> Status</th>
                                        <th><i class="fas fa-clock"></i> Created</th>
                                        <th><i class="fas fa-hourglass-half"></i> Expires</th>
                                        <th><i class="fas fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exports as $export)
                                        <tr>
                                            <td class="font-weight-bold">{{ $export->id }}</td>
                                            <td>
                                                @if($export->user)
                                                    <div class="d-flex align-items-center">
                                                        <img src="https://ui-avatars.com/api/?name={{ $export->user->name }}&background=random" 
                                                             class="rounded-circle me-2" width="24" height="24">
                                                        <span>{{ $export->user->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-robot"></i> System
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $export->model_type }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @switch($export->format)
                                                        @case('xlsx')
                                                            <i class="fas fa-file-excel text-success me-2"></i>
                                                            <span class="badge bg-success">XLSX</span>
                                                            @break
                                                        @case('csv')
                                                            <i class="fas fa-file-csv text-primary me-2"></i>
                                                            <span class="badge bg-primary">CSV</span>
                                                            @break
                                                        @case('pdf')
                                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                                            <span class="badge bg-danger">PDF</span>
                                                            @break
                                                    @endswitch
                                                </div>
                                            </td>
                                            <td>
                                                @if($export->filters)
                                                    <div class="filter-tags">
                                                        @foreach(json_decode($export->filters, true) as $key => $value)
                                                            @if($value)
                                                                <span class="badge bg-light text-dark me-1">{{ $key }}: {{ $value }}</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">No filters</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary font-weight-bold">{{ number_format($export->total_records) }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($export->status == 'completed')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Completed
                                                    </span>
                                                @elseif($export->status == 'failed')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times-circle"></i> Failed
                                                    </span>
                                                @elseif($export->status == 'processing')
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-spinner fa-spin"></i> Processing
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-pause-circle"></i> {{ $export->status }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">{{ $export->created_at->format('M j, H:i') }}</small>
                                            </td>
                                            <td class="text-center">
                                                @if($export->expires_at)
                                                    <div>
                                                        <small class="text-muted">{{ $export->expires_at->format('M j, H:i') }}</small>
                                                        @if($export->expires_at->isPast())
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-exclamation-triangle"></i> Expired
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.export.show', $export->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($export->isCompleted() && $export->download_url)
                                                        <a href="{{ $export->download_url }}" class="btn btn-sm btn-outline-success" title="Download File">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @endif
                                                    @if($export->status == 'processing')
                                                        <button class="btn btn-sm btn-outline-warning" disabled>
                                                            <i class="fas fa-spinner fa-spin"></i> Processing
                                                        </button>
                                                    @endif
                                                    <button onclick="deleteExport({{ $export->id }})" class="btn btn-sm btn-outline-danger" title="Delete Export">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $exports->links() }}
                    @else
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-file-export fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Exports Yet</h5>
                                <p class="text-muted">Create your first export using the form below</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Export Form Section -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="mb-0"><i class="fas fa-file-export"></i> Create New Export</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.export.store') }}" method="POST" id="exportForm">
                        @csrf
                        <div class="row">
                            <!-- Model Selection -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="model_type" class="form-label font-weight-bold">
                                        <i class="fas fa-cube text-primary"></i> Select Model
                                    </label>
                                    <select name="model_type" id="model_type" class="form-control" required onchange="updateExportOptions()">
                                        <option value="">Choose model...</option>
                                        <option value="books">
                                            <i class="fas fa-book"></i> Books
                                        </option>
                                        <option value="orders">
                                            <i class="fas fa-shopping-cart"></i> Orders
                                        </option>
                                        <option value="users">
                                            <i class="fas fa-users"></i> Users
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Format Selection -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="format" class="form-label font-weight-bold">
                                        <i class="fas fa-file text-primary"></i> Export Format
                                    </label>
                                    <select name="format" id="format" class="form-control" required>
                                        <option value="xlsx">
                                            <i class="fas fa-file-excel text-success"></i> Excel (XLSX)
                                        </option>
                                        <option value="csv">
                                            <i class="fas fa-file-csv text-primary"></i> CSV
                                        </option>
                                        <option value="pdf">
                                            <i class="fas fa-file-pdf text-danger"></i> PDF
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Date Range -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_range" class="form-label font-weight-bold">
                                        <i class="fas fa-calendar text-primary"></i> Date Range
                                    </label>
                                    <select name="date_range" id="date_range" class="form-control" onchange="updateDateFields()">
                                        <option value="all">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="yesterday">Yesterday</option>
                                        <option value="this_week">This Week</option>
                                        <option value="last_week">Last Week</option>
                                        <option value="this_month">This Month</option>
                                        <option value="last_month">Last Month</option>
                                        <option value="this_year">This Year</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Advanced Options -->
                        <div class="row mt-3">
                            <!-- Custom Date Range (Hidden by default) -->
                            <div id="customDateRange" class="col-12" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_from" class="form-label">
                                                <i class="fas fa-calendar-alt text-primary"></i> From Date
                                            </label>
                                            <input type="date" name="date_from" id="date_from" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_to" class="form-label">
                                                <i class="fas fa-calendar-alt text-primary"></i> To Date
                                            </label>
                                            <input type="date" name="date_to" id="date_to" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Column Selection (Dynamic) -->
                            <div id="columnSelection" class="col-12" style="display: none;">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-columns text-primary"></i> Select Columns to Export
                                    </label>
                                    <div id="columnCheckboxes" class="row">
                                        <!-- Columns will be populated dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                                    <i class="fas fa-download"></i> Start Export
                                </button>
                                <button type="button" onclick="previewExport()" class="btn btn-outline-info btn-lg px-5 ml-2">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.filter-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.empty-state {
    padding: 3rem;
}

.column-checkbox {
    margin-right: 15px;
    margin-bottom: 10px;
}

.column-checkbox label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: normal;
}
</style>

<script>
function updateExportOptions() {
    const modelType = document.getElementById('model_type').value;
    const columnSelection = document.getElementById('columnSelection');
    const columnCheckboxes = document.getElementById('columnCheckboxes');
    
    // Show/hide column selection based on model
    if (modelType) {
        columnSelection.style.display = 'block';
        updateColumnCheckboxes(modelType);
    } else {
        columnSelection.style.display = 'none';
    }
}

function updateDateFields() {
    const dateRange = document.getElementById('date_range').value;
    const customDateRange = document.getElementById('customDateRange');
    
    if (dateRange === 'custom') {
        customDateRange.style.display = 'block';
    } else {
        customDateRange.style.display = 'none';
    }
}

function updateColumnCheckboxes(modelType) {
    const columnCheckboxes = document.getElementById('columnCheckboxes');
    
    const columns = {
        books: [
            { name: 'id', label: 'ID', checked: true },
            { name: 'title', label: 'Title', checked: true },
            { name: 'author', label: 'Author', checked: true },
            { name: 'isbn', label: 'ISBN', checked: true },
            { name: 'price', label: 'Price', checked: true },
            { name: 'stock_quantity', label: 'Stock', checked: true },
            { name: 'description', label: 'Description', checked: false },
            { name: 'created_at', label: 'Created At', checked: false }
        ],
        orders: [
            { name: 'id', label: 'Order ID', checked: true },
            { name: 'user_id', label: 'Customer', checked: true },
            { name: 'total_amount', label: 'Total Amount', checked: true },
            { name: 'status', label: 'Status', checked: true },
            { name: 'created_at', label: 'Order Date', checked: true }
        ],
        users: [
            { name: 'id', label: 'User ID', checked: true },
            { name: 'name', label: 'Name', checked: true },
            { name: 'email', label: 'Email', checked: true },
            { name: 'role', label: 'Role', checked: true },
            { name: 'created_at', label: 'Created At', checked: true },
            { name: 'email_verified_at', label: 'Email Verified', checked: false }
        ]
    };
    
    const modelColumns = columns[modelType] || [];
    
    columnCheckboxes.innerHTML = modelColumns.map(col => `
        <div class="col-md-3 column-checkbox">
            <label>
                <input type="checkbox" name="columns[]" value="${col.name}" ${col.checked ? 'checked' : ''}>
                <span>${col.label}</span>
            </label>
        </div>
    `).join('');
}

function previewExport() {
    const form = document.getElementById('exportForm');
    const formData = new FormData(form);
    
    // Create preview window
    const previewWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
    
    // Write preview content
    previewWindow.document.write(`
        <html>
            <head>
                <title>Export Preview</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .preview-item { border-bottom: 1px solid #eee; padding: 10px 0; }
                    .preview-label { font-weight: bold; color: #333; }
                </style>
            </head>
            <body>
                <h3>Export Preview</h3>
                <div class="preview-item">
                    <div class="preview-label">Model:</div> ${formData.get('model_type') || 'Not selected'}
                </div>
                <div class="preview-item">
                    <div class="preview-label">Format:</div> ${formData.get('format') || 'Not selected'}
                </div>
                <div class="preview-item">
                    <div class="preview-label">Date Range:</div> ${formData.get('date_range') || 'Not selected'}
                </div>
                <div class="preview-item">
                    <div class="preview-label">Selected Columns:</div> 
                    ${Array.from(formData.getAll('columns[]')).map(col => col.value).join(', ') || 'All columns'}
                </div>
            </body>
        </html>
    `);
}

function refreshExports() {
    location.reload();
}

function deleteExport(exportId) {
    if (confirm('Are you sure you want to delete this export?')) {
        fetch(`/admin/export/${exportId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        }).then(() => {
            refreshExports();
        }).catch(error => {
            console.error('Error deleting export:', error);
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateExportOptions();
});
</script>
@endsection
