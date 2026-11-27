<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** REST API (Laravel Sanctum): authentication, role rules and responses. */
class ApiTest extends TestCase
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
            'store_name' => $role === 'seller' ? 'API Store' : null,
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

    // ─── Authentication ─────────────────────────────────────────

    /**
     * Objective 5: every protected endpoint rejects an unauthenticated request
     * with 401. The endpoints are read from the route list, so a new endpoint
     * added without protection is caught here too.
     */
    public function test_every_protected_endpoint_returns_401_without_token(): void
    {
        $book = $this->book($this->user('seller'));
        $order = Order::create(['user_id' => $this->user()->id, 'total_amount' => 1, 'status' => 'pending']);
        $params = ['book' => $book->id, 'category' => $this->category->id, 'order' => $order->id];

        $protected = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/v1/')
                && in_array('auth:sanctum', $route->gatherMiddleware(), true));

        $this->assertCount(12, $protected);

        foreach ($protected as $route) {
            $method = collect($route->methods())->reject(fn ($m) => $m === 'HEAD')->first();
            $uri = preg_replace_callback('/\{(\w+)\}/', fn ($m) => $params[$m[1]], $route->uri());

            // No Accept header, as a plain client would send.
            $this->call($method, '/' . $uri)
                ->assertStatus(401)
                ->assertJson(['message' => 'Unauthenticated.']);
        }
    }

    public function test_invalid_token_returns_401(): void
    {
        $this->withHeader('Authorization', 'Bearer not-a-real-token')
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_login_returns_token_that_works(): void
    {
        $user = $this->user();

        $token = $this->postJson('/api/v1/auth/token', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertCreated()->assertJsonPath('token_type', 'Bearer')->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        $user = $this->user();

        $this->postJson('/api/v1/auth/token', ['email' => $user->email, 'password' => 'wrong'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_two_factor_accounts_cannot_get_token_with_password_alone(): void
    {
        $user = $this->user('customer', ['two_factor_secret' => 'secret', 'two_factor_confirmed_at' => now()]);

        $this->postJson('/api/v1/auth/token', ['email' => $user->email, 'password' => 'password'])
            ->assertForbidden();
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_logout_revokes_token(): void
    {
        $user = $this->user();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/auth/logout')->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }

    // ─── Public catalog ─────────────────────────────────────────

    public function test_catalog_is_public_and_hides_suspended_sellers(): void
    {
        $visible = $this->book($this->user('seller'), ['title' => 'Visible Book']);
        $hidden = $this->book($this->user('seller', ['seller_status' => 'suspended']), ['title' => 'Hidden Book']);

        $this->getJson('/api/v1/books')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Visible Book'])
            ->assertJsonMissing(['title' => 'Hidden Book'])
            ->assertJsonStructure(['data' => [['id', 'isbn', 'title', 'price', 'seller' => ['store_name']]], 'meta', 'links']);

        $this->getJson("/api/v1/books/{$visible->id}")->assertOk()->assertJsonPath('data.title', 'Visible Book');
        $this->getJson("/api/v1/books/{$hidden->id}")->assertNotFound();
    }

    public function test_categories_are_public(): void
    {
        $this->getJson('/api/v1/categories')->assertOk()->assertJsonFragment(['name' => 'Fiction']);
    }

    // ─── Books ──────────────────────────────────────────────────

    public function test_approved_seller_can_create_listing_via_api(): void
    {
        $seller = $this->user('seller');
        Sanctum::actingAs($seller);

        $this->postJson('/api/v1/books', [
            'category_id' => $this->category->id,
            'title' => 'Noli Me Tangere',
            'author' => 'Jose Rizal',
            'isbn' => '9789710810736',
            'price' => 350,
            'stock_quantity' => 5,
        ])->assertCreated()->assertJsonPath('data.seller.id', $seller->id);

        $this->assertDatabaseHas('books', ['isbn' => '9789710810736', 'seller_id' => $seller->id]);
    }

    public function test_buyer_cannot_create_listing_via_api(): void
    {
        Sanctum::actingAs($this->user());

        $this->postJson('/api/v1/books', ['title' => 'x'])->assertForbidden();
    }

    public function test_seller_cannot_update_another_sellers_listing_via_api(): void
    {
        $book = $this->book($this->user('seller'));
        Sanctum::actingAs($this->user('seller'));

        $this->patchJson("/api/v1/books/{$book->id}", ['price' => 1])->assertForbidden();
        $this->assertEquals(100, $book->fresh()->price);
    }

    public function test_seller_can_partially_update_own_listing(): void
    {
        $seller = $this->user('seller');
        $book = $this->book($seller);
        Sanctum::actingAs($seller);

        $this->patchJson("/api/v1/books/{$book->id}", ['price' => 80])
            ->assertOk()
            ->assertJsonPath('data.price', 80);
    }

    public function test_invalid_listing_returns_422_with_errors(): void
    {
        Sanctum::actingAs($this->user('seller'));

        $this->postJson('/api/v1/books', ['title' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'author', 'isbn', 'price', 'category_id']);
    }

    // ─── Categories ─────────────────────────────────────────────

    public function test_only_admin_can_create_category(): void
    {
        Sanctum::actingAs($this->user('seller'));
        $this->postJson('/api/v1/categories', ['name' => 'Poetry'])->assertForbidden();

        Sanctum::actingAs($this->user('admin'));
        $this->postJson('/api/v1/categories', ['name' => 'Poetry'])->assertCreated()->assertJsonPath('data.name', 'Poetry');
    }

    // ─── Orders ─────────────────────────────────────────────────

    public function test_buyer_places_order_split_by_seller(): void
    {
        $bookA = $this->book($sellerA = $this->user('seller'), ['price' => 100]);
        $bookB = $this->book($sellerB = $this->user('seller'), ['price' => 40]);
        $buyer = $this->user();
        Sanctum::actingAs($buyer);

        $this->postJson('/api/v1/orders', [
            'items' => [['book_id' => $bookA->id, 'quantity' => 2], ['book_id' => $bookB->id, 'quantity' => 1]],
            'shipping_address' => '123 Katipunan Ave, Quezon City',
            'payment_method' => 'cash_on_delivery',
        ])->assertCreated()->assertJsonCount(2, 'data');

        $this->assertEquals(200, Order::where('seller_id', $sellerA->id)->value('total_amount'));
        $this->assertEquals(40, Order::where('seller_id', $sellerB->id)->value('total_amount'));
        $this->assertSame(8, $bookA->fresh()->stock_quantity);
    }

    public function test_order_for_more_than_stock_is_rejected(): void
    {
        $book = $this->book($this->user('seller'), ['stock_quantity' => 1]);
        Sanctum::actingAs($this->user());

        $this->postJson('/api/v1/orders', [
            'items' => [['book_id' => $book->id, 'quantity' => 5]],
            'shipping_address' => '123 Katipunan Ave, Quezon City',
            'payment_method' => 'cash_on_delivery',
        ])->assertStatus(422);

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $book->fresh()->stock_quantity);
    }

    public function test_orders_are_scoped_to_the_user(): void
    {
        $seller = $this->user('seller');
        $buyer = $this->user();
        $mine = Order::create(['user_id' => $buyer->id, 'seller_id' => $seller->id, 'total_amount' => 10, 'status' => 'pending']);
        $other = Order::create(['user_id' => $this->user()->id, 'seller_id' => $this->user('seller')->id, 'total_amount' => 20, 'status' => 'pending']);

        Sanctum::actingAs($buyer);
        $this->getJson('/api/v1/orders')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $mine->id);
        $this->getJson("/api/v1/orders/{$other->id}")->assertForbidden();

        Sanctum::actingAs($seller);
        $this->getJson('/api/v1/orders')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $mine->id);

        Sanctum::actingAs($this->user('admin'));
        $this->getJson('/api/v1/orders')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_seller_updates_order_status_but_buyer_cannot(): void
    {
        $seller = $this->user('seller');
        $buyer = $this->user();
        $order = Order::create(['user_id' => $buyer->id, 'seller_id' => $seller->id, 'total_amount' => 10, 'status' => 'pending']);

        Sanctum::actingAs($buyer);
        $this->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'completed'])->assertForbidden();

        Sanctum::actingAs($seller);
        $this->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'shipped'])
            ->assertOk()
            ->assertJsonPath('data.status', 'shipped');
    }

    // ─── Pages ──────────────────────────────────────────────────

    public function test_swagger_docs_are_admin_only(): void
    {
        $this->get('/admin/api-docs')->assertRedirect(route('login'));

        $this->actingAs($this->user('admin'))->get('/admin/api-docs')->assertOk()->assertSee('swagger-ui', false);
        $this->actingAs($this->user('admin'))->get('/admin/api-docs/openapi.json')->assertOk()->assertJsonPath('openapi', '3.0.3');

        $this->actingAs($this->user('seller'))->get('/admin/api-docs')->assertForbidden();
        $this->actingAs($this->user())->get('/admin/api-docs/openapi.json')->assertForbidden();
    }

    /** The Swagger spec documents every API route with the right methods and security. */
    public function test_swagger_spec_documents_every_api_route(): void
    {
        $spec = $this->actingAs($this->user('admin'))->getJson('/admin/api-docs/openapi.json')->json();

        $routes = collect(Route::getRoutes()->getRoutes())->filter(fn ($r) => str_starts_with($r->uri(), 'api/v1/'));
        $this->assertNotEmpty($routes);

        foreach ($routes as $route) {
            $path = '/' . substr($route->uri(), strlen('api/v1/'));
            $this->assertArrayHasKey($path, $spec['paths'], "{$path} is missing from the Swagger spec");

            $protected = in_array('auth:sanctum', $route->gatherMiddleware(), true);
            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                $operation = $spec['paths'][$path][strtolower($method)] ?? null;
                $this->assertNotNull($operation, "{$method} {$path} is missing from the Swagger spec");
                $this->assertSame($protected, isset($operation['security']), "{$method} {$path}: security in the spec does not match the route");
                if ($protected) {
                    $this->assertArrayHasKey('401', $operation['responses'], "{$method} {$path} should document 401");
                }
            }
        }
    }

    public function test_user_can_create_and_revoke_token_on_website(): void
    {
        $user = $this->user();

        $this->actingAs($user)->post('/api-tokens', ['name' => 'Postman'])
            ->assertRedirect(route('api-tokens.index'))
            ->assertSessionHas('new_token');
        $token = $user->tokens()->first();
        $this->assertSame('Postman', $token->name);

        $this->actingAs($user)->delete("/api-tokens/{$token->id}")->assertRedirect();
        $this->assertSame(0, $user->tokens()->count());
    }
}
