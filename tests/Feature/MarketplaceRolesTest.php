<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Role-based marketplace behaviour: sellers, listing ownership, per-seller
 * orders and admin approval of sellers.
 */
class MarketplaceRolesTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->category = Category::create(['name' => 'Fiction', 'description' => 'Stories']);
    }

    private function user(string $role = 'customer', array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'role' => $role,
            'email_verified_at' => now(),
            'seller_status' => $role === 'seller' ? 'approved' : null,
            'store_name' => $role === 'seller' ? 'Test Store' : null,
        ]);
    }

    private function book(?User $seller, array $attributes = []): Book
    {
        return Book::factory()->create($attributes + [
            'seller_id' => $seller?->id,
            'category_id' => $this->category->id,
            'is_active' => true,
            'stock_quantity' => 10,
            'price' => 100,
        ]);
    }

    private function listingData(array $overrides = []): array
    {
        return $overrides + [
            'category_id' => $this->category->id,
            'title' => 'A New Listing',
            'author' => 'Some Author',
            'isbn' => '9780000000001',
            'price' => 250,
            'stock_quantity' => 3,
            'description' => 'Good condition.',
        ];
    }

    // ─── Registration ───────────────────────────────────────────

    public function test_registering_as_seller_creates_pending_seller(): void
    {
        $this->post('/register', [
            'name' => 'New Seller',
            'email' => 'new.seller@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_type' => 'seller',
            'store_name' => 'Corner Books',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'new.seller@example.com')->first();
        $this->assertSame('seller', $user->role);
        $this->assertSame('pending', $user->seller_status);
        $this->assertSame('Corner Books', $user->store_name);
    }

    public function test_registering_as_buyer_creates_customer(): void
    {
        $this->post('/register', [
            'name' => 'New Buyer',
            'email' => 'new.buyer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_type' => 'buyer',
        ]);

        $user = User::where('email', 'new.buyer@example.com')->first();
        $this->assertSame('customer', $user->role);
        $this->assertNull($user->seller_status);
    }

    public function test_seller_registration_requires_store_name(): void
    {
        $this->post('/register', [
            'name' => 'No Store',
            'email' => 'nostore@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_type' => 'seller',
        ])->assertSessionHasErrors('store_name');
    }

    public function test_registration_cannot_choose_admin_role(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_type' => 'buyer',
            'role' => 'admin',
        ]);

        $this->assertSame('customer', User::where('email', 'sneaky@example.com')->value('role'));
    }

    // ─── Listings ───────────────────────────────────────────────

    public function test_approved_seller_can_create_listing_they_own(): void
    {
        $seller = $this->user('seller');

        $this->actingAs($seller)->post('/books', $this->listingData())
            ->assertRedirect(route('seller.books'));

        $this->assertDatabaseHas('books', ['isbn' => '9780000000001', 'seller_id' => $seller->id]);
    }

    public function test_pending_seller_cannot_create_listing(): void
    {
        $seller = $this->user('seller', ['seller_status' => 'pending']);

        $this->actingAs($seller)->post('/books', $this->listingData())->assertForbidden();
        $this->assertDatabaseMissing('books', ['isbn' => '9780000000001']);
    }

    public function test_buyer_cannot_create_listing(): void
    {
        $this->actingAs($this->user())->get('/books/create')->assertForbidden();
    }

    public function test_seller_can_edit_own_listing(): void
    {
        $seller = $this->user('seller');
        $book = $this->book($seller);

        $this->actingAs($seller)
            ->put("/books/{$book->id}", $this->listingData(['isbn' => $book->isbn, 'price' => 199]))
            ->assertRedirect(route('books.show', $book));

        $this->assertEquals(199, $book->fresh()->price);
    }

    public function test_seller_cannot_edit_or_delete_another_sellers_listing(): void
    {
        $owner = $this->user('seller');
        $other = $this->user('seller');
        $book = $this->book($owner);

        $this->actingAs($other)->get("/books/{$book->id}/edit")->assertForbidden();
        $this->actingAs($other)->put("/books/{$book->id}", $this->listingData(['isbn' => $book->isbn]))->assertForbidden();
        $this->actingAs($other)->delete("/books/{$book->id}")->assertForbidden();
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_seller_listings_page_shows_only_own_books(): void
    {
        $seller = $this->user('seller');
        $mine = $this->book($seller, ['title' => 'Mine Only']);
        $theirs = $this->book($this->user('seller'), ['title' => 'Someone Else']);

        $this->actingAs($seller)->get('/seller/books')
            ->assertOk()
            ->assertSee($mine->title)
            ->assertDontSee($theirs->title);
    }

    // ─── Visibility ─────────────────────────────────────────────

    public function test_suspended_sellers_listings_are_hidden_from_catalog(): void
    {
        $active = $this->book($this->user('seller'), ['title' => 'Visible Book']);
        $hidden = $this->book($this->user('seller', ['seller_status' => 'suspended']), ['title' => 'Hidden Book']);

        $this->get('/books')->assertOk()->assertSee($active->title)->assertDontSee($hidden->title);
        $this->get("/books/{$hidden->id}")->assertNotFound();
    }

    public function test_visitor_can_browse_catalog_and_book_page(): void
    {
        $book = $this->book($this->user('seller'), ['title' => 'Public Book']);

        $this->get('/books')->assertOk()->assertSee('Public Book');
        $this->get("/books/{$book->id}")->assertOk()->assertSee('Test Store');
    }

    // ─── Checkout and orders ────────────────────────────────────

    public function test_checkout_creates_one_order_per_seller(): void
    {
        $sellerA = $this->user('seller');
        $sellerB = $this->user('seller');
        $bookA = $this->book($sellerA, ['price' => 100]);
        $bookB = $this->book($sellerB, ['price' => 50]);
        $buyer = $this->user();

        $this->actingAs($buyer)
            ->withSession(['cart' => [$bookA->id => 2, $bookB->id => 1]])
            ->post('/cart/checkout', ['shipping_address' => '123 Test Street, Quezon City', 'payment_method' => 'cash_on_delivery'])
            ->assertRedirect(route('orders.index'));

        $orders = Order::where('user_id', $buyer->id)->get();
        $this->assertCount(2, $orders);
        $this->assertEquals(200, $orders->firstWhere('seller_id', $sellerA->id)->total_amount);
        $this->assertEquals(50, $orders->firstWhere('seller_id', $sellerB->id)->total_amount);
        $this->assertCount(1, $orders->pluck('checkout_reference')->unique());
        $this->assertSame(8, $bookA->fresh()->stock_quantity);
        $this->assertSame(9, $bookB->fresh()->stock_quantity);
    }

    public function test_seller_cannot_add_to_cart(): void
    {
        $seller = $this->user('seller');
        $book = $this->book($this->user('seller'));

        $this->actingAs($seller)->post("/cart/add/{$book->id}")->assertSessionHas('error');
        $this->assertEmpty(session('cart', []));
    }

    public function test_seller_sees_only_their_own_orders(): void
    {
        $seller = $this->user('seller');
        $buyer = $this->user();
        $mine = Order::create(['user_id' => $buyer->id, 'seller_id' => $seller->id, 'total_amount' => 10, 'status' => 'pending']);
        $theirs = Order::create(['user_id' => $buyer->id, 'seller_id' => $this->user('seller')->id, 'total_amount' => 20, 'status' => 'pending']);

        $this->actingAs($seller)->get('/orders')->assertOk()->assertSee("#{$mine->id}");
        $this->actingAs($seller)->get("/orders/{$mine->id}")->assertOk();
        $this->actingAs($seller)->get("/orders/{$theirs->id}")->assertForbidden();
    }

    public function test_seller_can_update_status_of_own_order_only(): void
    {
        $seller = $this->user('seller');
        $buyer = $this->user();
        $mine = Order::create(['user_id' => $buyer->id, 'seller_id' => $seller->id, 'total_amount' => 10, 'status' => 'pending']);
        $theirs = Order::create(['user_id' => $buyer->id, 'seller_id' => $this->user('seller')->id, 'total_amount' => 20, 'status' => 'pending']);

        $this->actingAs($seller)->patch("/orders/{$mine->id}/status", ['status' => 'shipped'])->assertRedirect();
        $this->assertSame('shipped', $mine->fresh()->status);

        $this->actingAs($seller)->patch("/orders/{$theirs->id}/status", ['status' => 'shipped'])->assertForbidden();
        $this->assertSame('pending', $theirs->fresh()->status);
    }

    public function test_buyer_cannot_update_order_status(): void
    {
        $buyer = $this->user();
        $order = Order::create(['user_id' => $buyer->id, 'seller_id' => $this->user('seller')->id, 'total_amount' => 10, 'status' => 'pending']);

        $this->actingAs($buyer)->put("/orders/{$order->id}/status", ['status' => 'completed'])->assertForbidden();
        $this->assertSame('pending', $order->fresh()->status);
    }

    // ─── Admin seller management ────────────────────────────────

    public function test_admin_can_approve_and_suspend_seller(): void
    {
        $admin = $this->user('admin');
        $seller = $this->user('seller', ['seller_status' => 'pending']);

        $this->actingAs($admin)->get('/admin/sellers')->assertOk()->assertSee('Test Store');

        $this->actingAs($admin)->post("/admin/sellers/{$seller->id}/approve")->assertRedirect();
        $this->assertSame('approved', $seller->fresh()->seller_status);

        $this->actingAs($admin)->post("/admin/sellers/{$seller->id}/suspend")->assertRedirect();
        $this->assertSame('suspended', $seller->fresh()->seller_status);
    }

    public function test_non_admin_cannot_manage_sellers(): void
    {
        $seller = $this->user('seller', ['seller_status' => 'pending']);

        $this->actingAs($seller)->get('/admin/sellers')->assertForbidden();
        $this->actingAs($seller)->post("/admin/sellers/{$seller->id}/approve")->assertForbidden();
        $this->assertSame('pending', $seller->fresh()->seller_status);
    }

    public function test_pending_seller_dashboard_explains_status_and_blocks_listings_page(): void
    {
        $seller = $this->user('seller', ['seller_status' => 'pending']);

        $this->actingAs($seller)->get('/seller/dashboard')->assertOk()->assertSee('awaiting approval');
        $this->actingAs($seller)->get('/seller/books')->assertRedirect(route('seller.dashboard'));
    }

    public function test_seller_dashboard_shows_stats(): void
    {
        $seller = $this->user('seller');
        $this->book($seller, ['stock_quantity' => 2, 'title' => 'Almost Gone']);

        $this->actingAs($seller)->get('/seller/dashboard')->assertOk()->assertSee('Almost Gone');
    }

    public function test_buyer_cannot_open_seller_dashboard(): void
    {
        $this->actingAs($this->user())->get('/seller/dashboard')->assertForbidden();
    }
}
