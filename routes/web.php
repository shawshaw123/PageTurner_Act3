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

require __DIR__.'/auth.php';
