@extends('layouts.app')

@section('title', 'Data Portability - My Account')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-download"></i> Data Portability</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>GDPR Compliance:</strong> You have the right to access and export all your personal data. 
                        Your data will be provided in machine-readable format for portability.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h5 class="text-primary"><i class="fas fa-user"></i></h5>
                                    <h6>Personal Data</h6>
                                    <p class="text-muted">Complete profile and account information</p>
                                    <form action="{{ route('user.data-portability.export-personal-data') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-download"></i> Export Personal Data
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h5 class="text-success"><i class="fas fa-shopping-cart"></i></h5>
                                    <h6>Order History</h6>
                                    <p class="text-muted">All your orders and purchase history</p>
                                    <form action="{{ route('user.data-portability.export-order-history') }}" method="POST">
                                        @csrf
                                        <div class="form-group mb-2">
                                            <label for="format" class="form-label">Format</label>
                                            <select name="format" class="form-control">
                                                <option value="xlsx">Excel (XLSX)</option>
                                                <option value="csv">CSV</option>
                                                <option value="pdf">PDF Invoice</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-download"></i> Export Orders
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h5 class="text-warning"><i class="fas fa-book"></i></h5>
                                    <h6>Reading History</h6>
                                    <p class="text-muted">Books you've purchased and reviewed</p>
                                    <form action="{{ route('user.data-portability.export-reading-history') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-download"></i> Export Reading History
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h5 class="text-info"><i class="fas fa-clock"></i></h5>
                                    <h6>Export History</h6>
                                    <p class="text-muted">Previous data export requests</p>
                                    <a href="#" class="btn btn-info" onclick="loadExportHistory()">
                                        <i class="fas fa-history"></i> View History
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5><i class="fas fa-exclamation-triangle"></i> Account Deletion</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action cannot be undone. Your account will be anonymized and all personal data will be permanently deleted.
                    </div>
                    
                    <form action="{{ route('user.data-portability.delete-account') }}" method="POST" onsubmit="return confirmAccountDeletion()">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">Confirm Password</label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                    <small class="form-text text-muted">Enter your password to confirm account deletion</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirmation" class="form-label">Type Confirmation</label>
                                    <input type="text" name="confirmation" id="confirmation" class="form-control" required>
                                    <small class="form-text text-muted">Type <strong>DELETE</strong> to confirm</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-user-times"></i> Delete My Account
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Export History Modal -->
    <div class="modal fade" id="exportHistoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="exportHistoryContent">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Loading export history...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmAccountDeletion() {
    var password = document.getElementById('password').value;
    var confirmation = document.getElementById('confirmation').value;
    
    if (!password) {
        alert('Please enter your password');
        return false;
    }
    
    if (confirmation !== 'DELETE') {
        alert('Please type DELETE to confirm account deletion');
        return false;
    }
    
    return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.');
}

function loadExportHistory() {
    fetch('/user/data-portability/history')
        .then(response => response.json())
        .then(data => {
            var content = '';
            
            if (data.exports && data.exports.length > 0) {
                content += '<div class="table-responsive"><table class="table"><thead><tr>';
                content += '<th>Type</th><th>Format</th><th>Status</th><th>Created</th><th>Expires</th><th>Actions</th>';
                content += '</tr></thead><tbody>';
                
                data.exports.forEach(function(export) {
                    content += '<tr>';
                    content += '<td>' + export.model_type + '</td>';
                    content += '<td><span class="badge bg-primary">' + export.format.toUpperCase() + '</span></td>';
                    content += '<td><span class="badge bg-' + (export.status === 'completed' ? 'success' : 'warning') + '">' + export.status + '</span></td>';
                    content += '<td>' + new Date(export.created_at).toLocaleString() + '</td>';
                    content += '<td>' + (export.expires_at ? new Date(export.expires_at).toLocaleString() : 'Never') + '</td>';
                    content += '<td>';
                    if (export.status === 'completed' && export.download_url) {
                        content += '<a href="' + export.download_url + '" class="btn btn-sm btn-success">Download</a>';
                    }
                    content += '</td></tr>';
                });
                
                content += '</tbody></table></div>';
            } else {
                content += '<div class="alert alert-info">No export history found.</div>';
            }
            
            document.getElementById('exportHistoryContent').innerHTML = content;
        });
    
    $('#exportHistoryModal').modal('show');
}
</script>
@endsection
