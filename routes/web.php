<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

// Temporary admin check route
Route::get('/check-admin', function() {
    try {
        // Check database connection
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $output = "<h2>Admin Account Check</h2>";
        $output .= "<p>✅ Database connection: SUCCESS</p>";
        $output .= "<p>📋 Database: " . \Illuminate\Support\Facades\DB::connection()->getDatabaseName() . "</p>";
        
        // Check admin user
        $admin = \App\Models\User::where('email', 'admin@pageturner.com')->first();
        
        if ($admin) {
            $output .= "<h3>✅ Admin User Found:</h3>";
            $output .= "<ul>";
            $output .= "<li>Name: " . $admin->name . "</li>";
            $output .= "<li>Email: " . $admin->email . "</li>";
            $output .= "<li>Role: " . $admin->role . "</li>";
            $output .= "<li>Email Verified: " . ($admin->email_verified_at ? 'Yes' : 'No') . "</li>";
            $output .= "<li>Created: " . $admin->created_at . "</li>";
            $output .= "</ul>";
            
            // Test password
            if (\Illuminate\Support\Facades\Hash::check('admin123', $admin->password)) {
                $output .= "<p style='color: green;'>✅ Password 'admin123' verification: PASSED</p>";
                $output .= "<p><strong>Login should work!</strong></p>";
            } else {
                $output .= "<p style='color: red;'>❌ Password 'admin123' verification: FAILED</p>";
                $output .= "<p>Fixing password...</p>";
                
                $admin->password = \Illuminate\Support\Facades\Hash::make('admin123');
                $admin->email_verified_at = now();
                $admin->save();
                
                $output .= "<p style='color: green;'>✅ Password fixed!</p>";
            }
        } else {
            $output .= "<h3>❌ Admin User Not Found!</h3>";
            $output .= "<p>Creating admin user...</p>";
            
            $admin = \App\Models\User::create([
                'name' => 'Admin User',
                'email' => 'admin@pageturner.com',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
            
            $output .= "<p style='color: green;'>✅ Admin user created!</p>";
        }
        
        $output .= "<h3>🔑 Login Credentials:</h3>";
        $output .= "<table border='1' cellpadding='5'>";
        $output .= "<tr><td>Email:</td><td>admin@pageturner.com</td></tr>";
        $output .= "<tr><td>Password:</td><td>admin123</td></tr>";
        $output .= "<tr><td>Role:</td><td>admin</td></tr>";
        $output .= "<tr><td>Email Verified:</td><td>Yes</td></tr>";
        $output .= "</table>";
        
        $output .= "<h3>🚀 Next Steps:</h3>";
        $output .= "<ol>";
        $output .= "<li><a href='/login'>Go to Login Page</a></li>";
        $output .= "<li>Login with credentials above</li>";
        $output .= "<li>Enable 2FA for security</li>";
        $output .= "</ol>";
        
        return $output;
        
    } catch (Exception $e) {
        return "<h2>❌ Error</h2><p style='color: red;'>Error: " . $e->getMessage() . "</p><p>Check your XAMPP MySQL configuration</p>";
    }
});

// Simple fix route
Route::get('/fix-reviews', function() {
    return '<h1>Review Status Fix</h1>
           <p>Run this command in your terminal:</p>
           <pre>php artisan tinker --execute="
$reviews = App\Models\Review::whereNull(\'status\')->get();
foreach($reviews as $review) {
    $review->update([\'status\' => \'approved\']);
}
echo \'Fixed \' . $reviews->count() . \' reviews\';
"</pre>';
});

// Temporary review status check route
Route::get('/check-reviews', function() {
    try {
        $output = "<h2>Review Status Check</h2>";
        
        // Get all reviews
        $reviews = \App\Models\Review::with('book', 'user')->get();
        
        $output .= "<p>Total Reviews: " . $reviews->count() . "</p>";
        
        // Update reviews without status
        $reviewsWithoutStatus = \App\Models\Review::whereNull('status')->get();
        if ($reviewsWithoutStatus->count() > 0) {
            $output .= "<p style='color: orange;'>Updating " . $reviewsWithoutStatus->count() . " reviews without status...</p>";
            
            foreach ($reviewsWithoutStatus as $review) {
                $review->update(['status' => 'approved']);
                $output .= "<p>✅ Updated review ID: {$review->id}</p>";
            }
        }
        
        // Show status summary
        $approved = \App\Models\Review::where('status', 'approved')->count();
        $rejected = \App\Models\Review::where('status', 'rejected')->count();
        $nullStatus = \App\Models\Review::whereNull('status')->count();
        
        $output .= "<h3>📊 Status Summary:</h3>";
        $output .= "<ul>";
        $output .= "<li>Approved: $approved</li>";
        $output .= "<li>Rejected: $rejected</li>";
        $output .= "<li>No Status: $nullStatus</li>";
        $output .= "</ul>";
        
        // Show recent reviews
        $output .= "<h3>Recent Reviews:</h3>";
        $recentReviews = \App\Models\Review::with('book', 'user')->take(5)->get();
        
        foreach ($recentReviews as $review) {
            $output .= "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            $output .= "<p><strong>Book:</strong> " . $review->book->title . "</p>";
            $output .= "<p><strong>Reviewer:</strong> " . $review->user->name . "</p>";
            $output .= "<p><strong>Rating:</strong> " . $review->rating . " stars</p>";
            $output .= "<p><strong>Status:</strong> " . ($review->status ?? 'NULL') . "</p>";
            $output .= "</div>";
        }
        
        $output .= "<p><a href='/'>Back to Home</a></p>";
        
        return $output;
        
    } catch (Exception $e) {
        return "<h2>❌ Error</h2><p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
});

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Book browsing (public)
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// Category browsing (public)
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

// ─── Authenticated Routes ────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // User Dashboard (customer)
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Lab 6 - User Data Portability Routes
    Route::prefix('user')->group(function () {
        Route::get('/data-portability', [\App\Http\Controllers\User\DataPortabilityController::class, 'index'])->name('user.data-portability.index');
        Route::post('/export-personal-data', [\App\Http\Controllers\User\DataPortabilityController::class, 'exportPersonalData'])->name('user.data-portability.export-personal');
        Route::post('/export-order-history', [\App\Http\Controllers\User\DataPortabilityController::class, 'exportOrderHistory'])->name('user.data-portability.export-orders');
        Route::post('/export-reading-history', [\App\Http\Controllers\User\DataPortabilityController::class, 'exportReadingHistory'])->name('user.data-portability.export-reading');
        Route::get('/data-portability/download/{export}', [\App\Http\Controllers\User\DataPortabilityController::class, 'download'])->name('user.data-portability.download');
        Route::delete('/delete-account', [\App\Http\Controllers\User\DataPortabilityController::class, 'deleteAccount'])->name('user.data-portability.delete-account');
    });

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Routes requiring email verification
    Route::middleware('verified')->group(function () {
        // Review routes
        Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        
        // Admin review management routes
        Route::put('/reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
        Route::put('/reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');

        // Order routes
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::delete('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/track', [OrderController::class, 'track'])->name('orders.track');
        Route::get('/orders/statistics', [OrderController::class, 'statistics'])->name('orders.statistics');

        // Cart routes
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add/{book}', [CartController::class, 'add'])->name('cart.add');
        Route::put('/cart/update/{book}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/remove/{book}', [CartController::class, 'remove'])->name('cart.remove');
        Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
        Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        Route::post('/cart/checkout', [CartController::class, 'processOrder'])->name('cart.process');
    });
});

// ─── Admin Routes ────────────────────────────────────────────────
Route::middleware(['auth', 'admin', 'verified'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Lab 6 - Data Management Routes
    Route::prefix('admin')->group(function () {
        // Import/Export Management
        Route::get('/import', [\App\Http\Controllers\Admin\ImportController::class, 'index'])->name('admin.import.index');
        Route::post('/import', [\App\Http\Controllers\Admin\ImportController::class, 'store'])->name('admin.import.store');
        Route::get('/import/template/{type}', [\App\Http\Controllers\Admin\ImportController::class, 'downloadTemplate'])->name('admin.import.template');
        Route::get('/import/{import}', [\App\Http\Controllers\Admin\ImportController::class, 'show'])->name('admin.import.show');
        
        Route::get('/export', [\App\Http\Controllers\Admin\ExportController::class, 'index'])->name('admin.export.index');
        Route::post('/export', [\App\Http\Controllers\Admin\ExportController::class, 'store'])->name('admin.export.store');
        Route::get('/export/download/{export}', [\App\Http\Controllers\Admin\ExportController::class, 'download'])->name('admin.export.download');
        Route::get('/export/{export}', [\App\Http\Controllers\Admin\ExportController::class, 'show'])->name('admin.export.show');
        Route::delete('/export/{export}', [\App\Http\Controllers\Admin\ExportController::class, 'destroy'])->name('admin.export.destroy');
        
        // Backup Management
        Route::get('/backup', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('admin.backup.index');
        Route::post('/backup', [\App\Http\Controllers\Admin\BackupController::class, 'store'])->name('admin.backup.store');
        Route::get('/backup/download/{backup}', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('admin.backup.download');
        Route::delete('/backup/{backup}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('admin.backup.destroy');
        Route::post('/backup/clean', [\App\Http\Controllers\Admin\BackupController::class, 'clean'])->name('admin.backup.clean');
        
        // Audit Log Management
        Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('admin.audit.index');
        Route::get('/audit/{audit}', [\App\Http\Controllers\Admin\AuditController::class, 'show'])->name('admin.audit.show');
        Route::get('/audit/export', [\App\Http\Controllers\Admin\AuditController::class, 'export'])->name('admin.audit.export');
        Route::get('/audit/statistics', [\App\Http\Controllers\Admin\AuditController::class, 'statistics'])->name('admin.audit.statistics');
        
        // API Rate Limiting
        Route::get('/api-rate-limits', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'index'])->name('admin.api-rate-limits.index');
        Route::get('/api-rate-limits/{rateLimit}', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'show'])->name('admin.api-rate-limits.show');
        Route::post('/api-rate-limits/clear-user/{userId}', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'clearUserLimit'])->name('admin.api-rate-limits.clearUser');
        Route::post('/api-rate-limits/clear-ip/{ip}', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'clearIpLimit'])->name('admin.api-rate-limits.clearIp');
        Route::post('/api-rate-limits/set-custom/{userId}', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'setCustomLimit'])->name('admin.api-rate-limits.setCustom');
        Route::post('/api-rate-limits/block-user/{userId}', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'blockUser'])->name('admin.api-rate-limits.blockUser');
        Route::post('/api-rate-limits/block-ip/{ip}', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'blockIp'])->name('admin.api-rate-limits.blockIp');
        Route::post('/api-rate-limits/unblock-user/{userId}', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'unblockUser'])->name('admin.api-rate-limits.unblockUser');
        Route::post('/api-rate-limits/unblock-ip/{ip}', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'unblockIp'])->name('admin.api-rate-limits.unblockIp');
        Route::get('/api-rate-limits/statistics', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'statistics'])->name('admin.api-rate-limits.statistics');
        Route::get('/api-rate-limits/export', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'export'])->name('admin.api-rate-limits.export');
        Route::delete('/api-rate-limits/cleanup', [\App\Http\Controllers\Admin\ApiRateLimitController::class, 'cleanup'])->name('admin.api-rate-limits.cleanup');
        
        // Import/Export Management
        Route::get('/import-export', [\App\Http\Controllers\Admin\ImportExportController::class, 'index'])->name('admin.import-export.index');
        Route::get('/import-export/books/import', [\App\Http\Controllers\Admin\ImportExportController::class, 'importBooks'])->name('admin.import-export.books.import');
        Route::post('/import-export/books/import', [\App\Http\Controllers\Admin\ImportExportController::class, 'processBooksImport'])->name('admin.import-export.books.process');
        Route::get('/import-export/books/export', [\App\Http\Controllers\Admin\ImportExportController::class, 'exportBooks'])->name('admin.import-export.books.export');
        Route::post('/import-export/books/export', [\App\Http\Controllers\Admin\ImportExportController::class, 'processBooksExport'])->name('admin.import-export.books.process');
        Route::get('/import-export/download/{exportLog}', [\App\Http\Controllers\Admin\ImportExportController::class, 'downloadExport'])->name('admin.import-export.download');
        Route::get('/import-export/template', [\App\Http\Controllers\Admin\ImportExportController::class, 'downloadTemplate'])->name('admin.import-export.template');
        Route::get('/import-export/import/{importLog}', [\App\Http\Controllers\Admin\ImportExportController::class, 'showImportLog'])->name('admin.import-export.import.show');
        Route::get('/import-export/export/{exportLog}', [\App\Http\Controllers\Admin\ImportExportController::class, 'showExportLog'])->name('admin.import-export.export.show');
        Route::get('/import-export/import/{importLog}/progress', [\App\Http\Controllers\Admin\ImportExportController::class, 'getImportProgress'])->name('admin.import-export.import.progress');
        Route::get('/import-export/export/{exportLog}/progress', [\App\Http\Controllers\Admin\ImportExportController::class, 'getExportProgress'])->name('admin.import-export.export.progress');
        
        // Enhanced Dashboard Routes
        Route::get('/dashboard/data-management', [\App\Http\Controllers\Admin\DashboardController::class, 'dataManagement'])->name('admin.dashboard.data-management');
        Route::get('/dashboard/system-monitoring', [\App\Http\Controllers\Admin\DashboardController::class, 'systemMonitoring'])->name('admin.dashboard.system-monitoring');
        Route::get('/dashboard/api-stats', [\App\Http\Controllers\Admin\DashboardController::class, 'apiStats'])->name('admin.dashboard.api-stats');
        Route::get('/dashboard/audit-stats', [\App\Http\Controllers\Admin\DashboardController::class, 'auditStats'])->name('admin.dashboard.audit-stats');
        Route::post('/dashboard/refresh-health', [\App\Http\Controllers\Admin\DashboardController::class, 'refreshSystemHealth'])->name('admin.dashboard.refresh-health');
    });

    // Category management
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Book management
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    
    // Admin stock management
    Route::post('/books/{book}/out-of-stock', [BookController::class, 'markOutOfStock'])->name('books.outOfStock');
    Route::post('/books/{book}/restock', [BookController::class, 'restock'])->name('books.restock');

    // Order status management (admin)
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
});

// Public wildcard routes (Show) - Must be after specific routes like /create
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// ─── AI Chat Routes (Lab 8) ──────────────────────────────────────
Route::get('/ai/chat', [App\Http\Controllers\AIChatController::class, 'index'])->name('ai.chat');
Route::post('/ai/chat/send', [App\Http\Controllers\AIChatController::class, 'sendMessage'])->name('ai.chat.send');
Route::get('/ai/chat/history', [App\Http\Controllers\AIChatController::class, 'getHistory'])->name('ai.chat.history');
Route::post('/ai/chat/new', [App\Http\Controllers\AIChatController::class, 'newConversation'])->name('ai.chat.new');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/ai/dashboard', [App\Http\Controllers\AIChatController::class, 'dashboard'])->name('admin.ai.dashboard');
});

require __DIR__.'/auth.php';
