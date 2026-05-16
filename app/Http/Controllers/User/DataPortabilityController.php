<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ExportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;

class DataPortabilityController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get user's export history
        $exportHistory = ExportLog::where('user_id', $user->id)
                                ->where('model_type', 'orders')
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();

        return view('user.data-portability.index', compact('exportHistory'));
    }

    public function exportPersonalData()
    {
        $user = Auth::user();
        
        // Create export log
        $exportLog = ExportLog::create([
            'user_id' => $user->id,
            'model_type' => 'personal_data',
            'format' => 'json',
            'columns' => ['profile', 'orders', 'reviews'],
            'filters' => ['user_id' => $user->id],
            'total_records' => 1,
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            // Prepare personal data
            $personalData = [
                'profile' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'orders' => $user->orders()->with(['items.book', 'items.review'])->get()->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'total_amount' => $order->total_amount,
                        'items' => $order->items->map(function ($item) {
                            return [
                                'book_title' => $item->book->title,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'total' => $item->quantity * $item->price,
                            ];
                        }),
                        'created_at' => $order->created_at,
                    ];
                }),
                'reviews' => $user->reviews()->with('book')->get()->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'book_title' => $review->book->title,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at,
                    ];
                }),
            ];

            // Generate filename
            $filename = 'exports/personal_data_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.json';
            
            // Save to storage
            Storage::disk('local')->put($filename, json_encode($personalData, JSON_PRETTY_PRINT));

            // Update export log
            $exportLog->update([
                'status' => 'completed',
                'file_path' => $filename,
                'download_url' => route('user.data-portability.download', $exportLog->id),
                'expires_at' => now()->addDays(7),
                'completed_at' => now(),
            ]);

            return redirect()->route('user.data-portability.index')
                ->with('success', 'Your personal data export is ready for download.');

        } catch (\Exception $e) {
            $exportLog->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);

            return back()->with('error', 'Failed to export personal data: ' . $e->getMessage());
        }
    }

    public function exportOrderHistory(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,csv,pdf',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $user = Auth::user();
        
        // Create export log
        $exportLog = ExportLog::create([
            'user_id' => $user->id,
            'model_type' => 'orders',
            'format' => $request->format,
            'columns' => ['id', 'order_number', 'status', 'total_amount', 'created_at'],
            'filters' => [
                'user_id' => $user->id,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
            'total_records' => $user->orders()->count(),
            'status' => 'pending',
        ]);

        // Generate filename
        $filename = 'exports/orders_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.' . $request->format;
        $exportLog->file_path = $filename;
        $exportLog->save();

        try {
            if ($request->format === 'pdf') {
                $this->generateOrderPdf($exportLog, $user, $request->only('date_from', 'date_to'));
            } else {
                // Queue Excel/CSV export
                $filters = array_merge($request->only('date_from', 'date_to'), [
                    'customer_id' => $user->id,
                ]);
                
                Excel::queue(
                    new OrdersExport($filters, $exportLog->columns),
                    $filename
                );
            }

            return redirect()->route('user.data-portability.index')
                ->with('success', 'Your order history export is being processed.');

        } catch (\Exception $e) {
            $exportLog->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);

            return back()->with('error', 'Failed to start order export: ' . $e->getMessage());
        }
    }

    public function exportReadingHistory()
    {
        $user = Auth::user();
        
        // Create export log
        $exportLog = ExportLog::create([
            'user_id' => $user->id,
            'model_type' => 'reading_history',
            'format' => 'json',
            'columns' => ['purchases', 'reviews', 'browsing'],
            'filters' => ['user_id' => $user->id],
            'total_records' => 1,
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            // Get reading history data
            $readingHistory = [
                'purchases' => $user->orders()->with('items.book')->get()->flatMap(function ($order) {
                    return $order->items->map(function ($item) use ($order) {
                        return [
                            'book_title' => $item->book->title,
                            'author' => $item->book->author,
                            'isbn' => $item->book->isbn,
                            'purchase_date' => $order->created_at,
                            'price' => $item->price,
                            'quantity' => $item->quantity,
                        ];
                    });
                }),
                'reviews' => $user->reviews()->with('book')->get()->map(function ($review) {
                    return [
                        'book_title' => $review->book->title,
                        'author' => $review->book->author,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'review_date' => $review->created_at,
                    ];
                }),
                'export_date' => now()->toISOString(),
            ];

            // Generate filename
            $filename = 'exports/reading_history_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.json';
            
            // Save to storage
            Storage::disk('local')->put($filename, json_encode($readingHistory, JSON_PRETTY_PRINT));

            // Update export log
            $exportLog->update([
                'status' => 'completed',
                'file_path' => $filename,
                'download_url' => route('user.data-portability.download', $exportLog->id),
                'expires_at' => now()->addDays(7),
                'completed_at' => now(),
            ]);

            return redirect()->route('user.data-portability.index')
                ->with('success', 'Your reading history export is ready for download.');

        } catch (\Exception $e) {
            $exportLog->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);

            return back()->with('error', 'Failed to export reading history: ' . $e->getMessage());
        }
    }

    public function download(ExportLog $export)
    {
        // Ensure user can only download their own exports
        if ($export->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (!$export->isCompleted() || $export->isExpired()) {
            abort(404, 'Export not available for download.');
        }

        $filePath = storage_path('app/' . $export->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'Export file not found.');
        }

        $originalName = $export->model_type . '_export_' . $export->created_at->format('Y-m-d_H-i-s') . '.' . $export->format;
        
        return response()->download($filePath, $originalName);
    }

    protected function generateOrderPdf($exportLog, $user, $filters)
    {
        // Update status to processing
        $exportLog->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            // Get orders
            $query = $user->orders()->with(['items.book']);
            
            if (!empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }
            
            if (!empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }
            
            $orders = $query->get();

            // Update total records
            $exportLog->update(['total_records' => $orders->count()]);

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.pdf.user_orders', [
                'user' => $user,
                'orders' => $orders,
                'filters' => $filters,
                'export_date' => now(),
            ]);

            // Save PDF
            Storage::disk('local')->put($exportLog->file_path, $pdf->output());

            // Update export log
            $exportLog->update([
                'status' => 'completed',
                'completed_at' => now(),
                'download_url' => route('user.data-portability.download', $exportLog->id),
                'expires_at' => now()->addDays(7),
            ]);

        } catch (\Exception $e) {
            $exportLog->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);
        }
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE',
        ]);

        $user = Auth::user();

        // Verify password
        if (!\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        try {
            // Log the account deletion
            \App\Services\AuditService::logSecurity('account_deleted', 'User requested account deletion', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
            ]);

            // Anonymize user data instead of deleting to maintain data integrity
            $user->update([
                'name' => 'Deleted User',
                'email' => 'deleted_' . $user->id . '@deleted.com',
                'password' => \Hash::make(str_random(32)),
                'email_verified_at' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]);

            // Log out user
            Auth::logout();

            return redirect()->route('login')
                ->with('success', 'Your account has been deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete account: ' . $e->getMessage());
        }
    }
}
