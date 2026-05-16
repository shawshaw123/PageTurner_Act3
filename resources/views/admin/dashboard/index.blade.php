@extends('layouts.app')

@section('title', 'Admin Dashboard - PageTurner')

@section('content')
<div class="container-fluid">
    <!-- Header with Lab 6 Info -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            <p class="text-muted mb-0">Laboratory Activity 6 - Advanced Data Management</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="refreshAllWidgets()" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-sync"></i> Refresh All
            </button>
            <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-search"></i> Audit Logs
            </a>
        </div>
    </div>

    <!-- Lab 6 Feature Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
        
        <!-- Data Management Widget -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center mb-4">
                <div class="p-3 bg-blue-500 rounded-full">
                    <i class="fas fa-database text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">Data Management</h3>
                    <p class="text-sm text-gray-500">Import, Export & Processing</p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Recent Imports</span>
                    <a href="{{ route('admin.import-export.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        View All →
                    </a>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Recent Exports</span>
                    <a href="{{ route('admin.import-export.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        View All →
                    </a>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Queue Status</span>
                    <span class="text-sm font-medium {{ $queueStats['failed_jobs'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $queueStats['failed_jobs'] }} failed jobs
                    </span>
                </div>
                <div class="pt-3 border-t border-gray-200">
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.import-export.books.import') }}" class="flex items-center justify-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                            <i class="fas fa-upload mr-2"></i>
                            Import Books
                        </a>
                        <a href="{{ route('admin.import-export.books.export') }}" class="flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            <i class="fas fa-download mr-2"></i>
                            Export Books
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Status Widget -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center mb-4">
                <div class="p-3 bg-green-500 rounded-full">
                    <i class="fas fa-shield-alt text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">Backup System</h3>
                    <p class="text-sm text-gray-500">Automated Backups & Monitoring</p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Last Backup</span>
                    <span class="text-sm font-medium {{ $backupStats['last_backup'] && $backupStats['last_backup']->status == 'completed' ? 'text-green-600' : 'text-red-600' }}">
                        @if($backupStats['last_backup'])
                            {{ $backupStats['last_backup']->started_at->format('M j, H:i') }} - 
                            <span class="badge bg-{{ $backupStats['last_backup']->status == 'completed' ? 'success' : 'danger' }}">
                                {{ $backupStats['last_backup']->status }}
                            </span>
                        @else
                            No backups yet
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Success Rate</span>
                    <span class="text-sm font-medium {{ $backupStats['success_rate'] >= 90 ? 'text-green-600' : ($backupStats['success_rate'] >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $backupStats['success_rate'] }}%
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Total Backups</span>
                    <span class="text-sm font-medium text-gray-800">{{ $backupStats['total_backups'] }}</span>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.backup.index') }}" class="btn btn-success btn-sm w-full">
                        <i class="fas fa-cog"></i> Manage Backups
                    </a>
                </div>
            </div>
        </div>

        <!-- Audit Log Widget -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center mb-4">
                <div class="p-3 bg-yellow-500 rounded-full">
                    <i class="fas fa-search text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">Audit Logs</h3>
                    <p class="text-sm text-gray-500">Security & Compliance Tracking</p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Recent Events</span>
                    <a href="{{ route('admin.audit.index') }}" class="text-yellow-600 hover:text-yellow-800 font-medium">
                        View All →
                    </a>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Security Alerts</span>
                    <span class="text-sm font-medium {{ $auditStats['security_alerts'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $auditStats['security_alerts'] }} alerts
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Total Audits</span>
                    <span class="text-sm font-medium text-gray-800">{{ number_format($auditStats['total_audits']) }}</span>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-warning btn-sm w-full">
                        <i class="fas fa-list"></i> View Audit Logs
                    </a>
                </div>
            </div>
        </div>

        <!-- API Rate Limiting Widget -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center mb-4">
                <div class="p-3 bg-purple-500 rounded-full">
                    <i class="fas fa-tachometer-alt text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">API Rate Limits</h3>
                    <p class="text-sm text-gray-500">Usage Monitoring & Control</p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Current Hour</span>
                    <span class="text-sm font-medium text-gray-800">{{ $apiStats['current_hour']['requests'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Rate Limit Hits</span>
                    <span class="text-sm font-medium {{ $apiStats['current_hour']['rate_limit_hits'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $apiStats['current_hour']['rate_limit_hits'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Unique Users</span>
                    <span class="text-sm font-medium text-gray-800">{{ $apiStats['last_24_hours']['unique_users'] }}</span>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.api-rate-limits.index') }}" class="btn btn-purple btn-sm w-full">
                        <i class="fas fa-chart-line"></i> Manage Rate Limits
                    </a>
                </div>
            </div>
        </div>

        <!-- System Health Widget -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <div class="flex items-center mb-4">
                <div class="p-3 bg-red-500 rounded-full">
                    <i class="fas fa-heartbeat text-white text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">System Health</h3>
                    <p class="text-sm text-gray-500">Performance & Monitoring</p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">System Status</span>
                    <span class="text-sm font-medium {{ $systemHealth['status'] == 'healthy' ? 'text-green-600' : ($systemHealth['status'] == 'warning' ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ ucfirst($systemHealth['status']) }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Disk Usage</span>
                    <span class="text-sm font-medium {{ $systemHealth['disk_usage'] > 90 ? 'text-red-600' : ($systemHealth['disk_usage'] > 80 ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ round($systemHealth['disk_usage']) }}%
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Memory Usage</span>
                    <span class="text-sm font-medium text-gray-800">{{ $systemHealth['memory_usage'] }}%</span>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.dashboard.system-monitoring') }}" class="btn btn-red btn-sm w-full">
                        <i class="fas fa-medkit"></i> System Monitoring
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Actions Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-rocket"></i> Quick Actions</h3>
            <div class="grid grid-cols-3 gap-3">
                <!-- Lab 6 Quick Actions -->
                <a href="{{ route('books.create') }}" class="btn btn-indigo p-3 text-center">
                    <i class="fas fa-plus-circle"></i>
                    <span class="block text-sm font-medium">Add New Book</span>
                </a>
                <a href="{{ route('books.index') }}" class="btn btn-indigo p-3 text-center">
                    <i class="fas fa-list"></i>
                    <span class="block text-sm font-medium">Manage Books</span>
                </a>
                <a href="{{ route('categories.create') }}" class="btn btn-indigo p-3 text-center">
                    <i class="fas fa-plus-circle"></i>
                    <span class="block text-sm font-medium">Add Category</span>
                </a>
                <a href="{{ route('categories.index') }}" class="btn btn-indigo p-3 text-center">
                    <i class="fas fa-list"></i>
                    <span class="block text-sm font-medium">Manage Categories</span>
                </a>
                
                <!-- Order Management -->
                <a href="{{ route('orders.index') }}" class="btn btn-green p-3 text-center">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="block text-sm font-medium">Manage Orders</span>
                </a>
                <a href="{{ route('orders.create') }}" class="btn btn-green p-3 text-center">
                    <i class="fas fa-plus-circle"></i>
                    <span class="block text-sm font-medium">Add Order</span>
                </a>
                
                <!-- User Management -->
                <a href="{{ route('admin.users.index') }}" class="btn btn-purple p-3 text-center">
                    <i class="fas fa-users"></i>
                    <span class="block text-sm font-medium">Manage Users</span>
                </a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-purple p-3 text-center">
                    <i class="fas fa-user-plus"></i>
                    <span class="block text-sm font-medium">Add User</span>
                </a>
                
                <!-- Lab 6 Features -->
                <a href="{{ route('admin.import.index') }}" class="btn btn-orange p-3 text-center">
                    <i class="fas fa-upload"></i>
                    <span class="block text-sm font-medium">Import Books</span>
                </a>
                <a href="{{ route('admin.export.index') }}" class="btn btn-orange p-3 text-center">
                    <i class="fas fa-download"></i>
                    <span class="block text-sm font-medium">Export Data</span>
                </a>
                <a href="{{ route('admin.backup.index') }}" class="btn btn-orange p-3 text-center">
                    <i class="fas fa-shield-alt"></i>
                    <span class="block text-sm font-medium">Backup Management</span>
                </a>
                <a href="{{ route('admin.audit.index') }}" class="btn btn-orange p-3 text-center">
                    <i class="fas fa-search"></i>
                    <span class="block text-sm font-medium">Audit Logs</span>
                </a>
                <a href="{{ route('admin.api-rate-limits.index') }}" class="btn btn-orange p-3 text-center">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="block text-sm font-medium">API Rate Limits</span>
                </a>
                <a href="{{ route('admin.dashboard.data-management') }}" class="btn btn-orange p-3 text-center">
                    <i class="fas fa-chart-bar"></i>
                    <span class="block text-sm font-medium">Data Management Dashboard</span>
                </a>
                <a href="{{ route('admin.dashboard.system-monitoring') }}" class="btn btn-orange p-3 text-center">
                    <i class="fas fa-heartbeat"></i>
                    <span class="block text-sm font-medium">System Monitoring</span>
                </a>
                
                <!-- Migration Fix -->
                <a href="javascript:void(0)" onclick="showMigrationHelper()" class="btn btn-red p-3 text-center">
                    <i class="fas fa-wrench"></i>
                    <span class="block text-sm font-medium">Fix Migration</span>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-gray-500">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-chart-bar"></i> Statistics</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Total Books</span>
                    <span class="text-sm font-medium text-gray-800">{{ $stats['total_books'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Total Orders</span>
                    <span class="text-sm font-medium text-gray-800">{{ $stats['total_orders'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Total Users</span>
                    <span class="text-sm font-medium text-gray-800">{{ $stats['total_users'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Pending Orders</span>
                    <span class="text-sm font-medium text-orange-600">{{ $stats['pending_orders'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Low Stock Books</span>
                    <span class="text-sm font-medium text-red-600">{{ $stats['low_stock_books'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-clock"></i> Recent Activity</h3>
            <div class="flex gap-2">
                <a href="{{ route('admin.import.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    <i class="fas fa-upload"></i> Imports
                </a>
                <a href="{{ route('admin.export.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    <i class="fas fa-download"></i> Exports
                </a>
                <a href="{{ route('admin.audit.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    <i class="fas fa-search"></i> Audit Logs
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentImports->take(3) as $import)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge bg-blue-100 text-blue-800">Import</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    {{ $import->filename }} ({{ $import->processed_rows }}/{{ $import->total_rows }} rows)
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge bg-{{ $import->status == 'completed' ? 'green' : ($import->status == 'failed' ? 'red' : 'yellow') }}">
                                    {{ $import->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $import->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.import.show', $import->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    
                    @foreach($recentExports->take(2) as $export)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge bg-green-100 text-green-800">Export</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    {{ $export->model_type }} ({{ $export->format }})
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge bg-{{ $export->status == 'completed' ? 'green' : ($export->status == 'failed' ? 'red' : 'yellow') }}">
                                    {{ $export->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $export->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($export->isCompleted() && $export->download_url)
                                    <a href="{{ $export->download_url }}" class="text-indigo-600 hover:text-indigo-900">
                                        Download
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function refreshAllWidgets() {
    fetch('{{ route('admin.dashboard.refresh-health') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Reload the page to show updated data
        location.reload();
    })
    .catch(error => {
        console.error('Error refreshing widgets:', error);
    });
}

function showMigrationHelper() {
    // Create modal with migration fix instructions
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-wrench text-orange-500 mr-2"></i>
                Fix Migration Issue
            </h3>
            <div class="space-y-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="text-yellow-800 font-bold mb-2">Step 1: Fix Migration</h4>
                    <p class="text-yellow-700 mb-3">The migration conflict needs to be resolved first.</p>
                    <div class="bg-white rounded p-3 border border-yellow-300">
                        <h5 class="font-bold text-yellow-800 mb-2">Run these commands in your terminal:</h5>
                        <div class="bg-gray-900 text-gray-100 p-3 rounded font-mono text-sm">
                            <div class="mb-2 text-yellow-300">php artisan tinker</div>
                            <div class="text-yellow-300">use Illuminate\\Support\\Facades\\DB;</div>
                            <div class="text-yellow-300">DB::table('migrations')->insert([</div>
                            <div class="text-yellow-300">    'migration' => '2024_03_05_000000_add_status_to_reviews_table',</div>
                            <div class="text-yellow-300">    'batch' => 4</div>
                            <div class="text-yellow-300">]);</div>
                            <div class="text-yellow-300">exit;</div>
                        </div>
                        <button onclick="copyToClipboard('php artisan tinker\\nuse Illuminate\\\\Support\\\\Facades\\\\DB;\\nDB::table(\\'migrations\\')->insert([\\n    \\'migration\\' => \\'2024_03_05_000000_add_status_to_reviews_table\\',\\n    \\'batch\\' => 4\\n]);\\nexit;')" 
                                class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">
                            <i class="fas fa-copy mr-2"></i>Copy Commands
                        </button>
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-blue-800 font-bold mb-2">Step 2: Run Remaining Migrations</h4>
                    <p class="text-blue-700 mb-3">After fixing the migration, run the remaining migrations.</p>
                    <div class="bg-white rounded p-3 border border-blue-300">
                        <div class="bg-gray-900 text-gray-100 p-3 rounded font-mono text-sm mb-2">
                            <div class="text-blue-300">php artisan migrate</div>
                        </div>
                        <button onclick="window.location.href='{{ route('admin.dashboard.index') }}'" 
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                            <i class="fas fa-play mr-2"></i>Run Migrations
                        </button>
                    </div>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="text-green-800 font-bold mb-2">Step 3: Start Application</h4>
                    <p class="text-green-700 mb-3">Once migrations are complete, start the application.</p>
                    <div class="bg-white rounded p-3 border border-green-300">
                        <div class="bg-gray-900 text-gray-100 p-3 rounded font-mono text-sm mb-2">
                            <div class="text-green-300">php artisan serve</div>
                        </div>
                        <button onclick="window.location.href='http://localhost:8000'" 
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                            <i class="fas fa-rocket mr-2"></i>Start Application
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end mt-4">
                <button onclick="closeMigrationHelper()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.innerHTML = '<i class="fas fa-check mr-2"></i>Copied to clipboard!';
        document.body.appendChild(toast);
        
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
}

function closeMigrationHelper() {
    const modal = document.querySelector('.fixed.inset-0');
    if (modal) {
        document.body.removeChild(modal);
    }
}
</script>
@endsection
