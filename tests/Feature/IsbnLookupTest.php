<?php

namespace Tests\Feature;

use App\Integrations\IsbnLookup\CoverDownloader;
use App\Integrations\IsbnLookup\Isbn;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** IP-1: Google Books lookup by ISBN on the Add Listing form. */
class IsbnLookupTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google_books.key' => 'test-key']);
        $this->category = Category::create(['name' => 'Fiction']);
        $this->seller = User::factory()->create(['role' => 'seller', 'seller_status' => 'approved', 'store_name' => 'ISBN Store', 'email_verified_at' => now()]);
    }

    private function fakeGoogleBooks(array $items): void
    {
        Http::fake(['www.googleapis.com/books/v1/volumes*' => Http::response(['totalItems' => count($items), 'items' => $items])]);
    }

    private function hailMary(): array
    {
        return ['volumeInfo' => [
            'title' => 'Project Hail Mary',
            'authors' => ['Andy Weir'],
            'publisher' => 'Ballantine Books',
            'publishedDate' => '2021-05-04',
            'description' => '<p>A lone astronaut must save the earth.</p>',
            'pageCount' => 496,
            'language' => 'en',
            'imageLinks' => ['thumbnail' => 'http://books.google.com/books/content?id=abc&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api'],
        ]];
    }

    public function test_isbn_validation_checks_the_check_digit(): void
    {
        $this->assertTrue(Isbn::isValid('978-0-593-13520-4'));
        $this->assertTrue(Isbn::isValid('0306406152'));
        $this->assertTrue(Isbn::isValid('080442957X'));
        $this->assertFalse(Isbn::isValid('9780593135205'));
        $this->assertFalse(Isbn::isValid('1234567890'));
        $this->assertFalse(Isbn::isValid('abc'));
    }

    public function test_lookup_returns_title_author_and_cover(): void
    {
        $this->fakeGoogleBooks([$this->hailMary()]);

        $this->actingAs($this->seller)->getJson('/books/isbn-lookup?isbn=978-0-593-13520-4')
            ->assertOk()
            ->assertJsonPath('data.isbn', '9780593135204')
            ->assertJsonPath('data.title', 'Project Hail Mary')
            ->assertJsonPath('data.author', 'Andy Weir')
            ->assertJsonPath('data.publisher', 'Ballantine Books')
            ->assertJsonPath('data.description', 'A lone astronaut must save the earth.')
            ->assertJsonPath('data.cover_url', 'https://books.google.com/books/content?id=abc&printsec=frontcover&img=1&zoom=1&source=gbs_api');

        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'q=isbn%3A9780593135204') && str_contains($r->url(), 'key=test-key'));
    }

    public function test_lookup_results_are_cached(): void
    {
        $this->fakeGoogleBooks([$this->hailMary()]);

        $this->actingAs($this->seller)->getJson('/books/isbn-lookup?isbn=9780593135204')->assertOk();
        $this->actingAs($this->seller)->getJson('/books/isbn-lookup?isbn=9780593135204')->assertOk();

        Http::assertSentCount(1);
    }

    public function test_invalid_isbn_is_rejected_without_calling_google(): void
    {
        Http::fake();

        $this->actingAs($this->seller)->getJson('/books/isbn-lookup?isbn=1234567890')->assertStatus(422);
        Http::assertNothingSent();
    }

    public function test_unknown_isbn_returns_404(): void
    {
        $this->fakeGoogleBooks([]);

        $this->actingAs($this->seller)->getJson('/books/isbn-lookup?isbn=9780306406157')->assertNotFound();
    }

    public function test_google_errors_return_502_with_a_message(): void
    {
        Http::fake(['www.googleapis.com/*' => Http::response(['error' => ['message' => 'quota']], 429)]);

        $this->actingAs($this->seller)->getJson('/books/isbn-lookup?isbn=9780593135204')
            ->assertStatus(502)
            ->assertJsonPath('message', 'Google Books limit reached. Please try again later or fill in the details manually.');
    }

    public function test_buyers_cannot_use_the_lookup(): void
    {
        $buyer = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);

        $this->actingAs($buyer)->getJson('/books/isbn-lookup?isbn=9780593135204')->assertForbidden();
    }

    public function test_listing_saves_looked_up_cover_and_timing(): void
    {
        Storage::fake('public');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        Http::fake(['books.google.com/*' => Http::response($png, 200, ['Content-Type' => 'image/png'])]);

        $this->actingAs($this->seller)->post('/books', [
            'category_id' => $this->category->id,
            'title' => 'Project Hail Mary',
            'author' => 'Andy Weir',
            'publisher' => 'Ballantine Books',
            'isbn' => '9780593135204',
            'price' => 899,
            'stock_quantity' => 3,
            'cover_url' => 'https://books.google.com/books/content?id=abc&img=1',
            'isbn_lookup_used' => 1,
            'form_started_at' => now()->subSeconds(42)->getTimestampMs(),
        ])->assertRedirect(route('seller.books'));

        $book = Book::where('isbn', '9780593135204')->firstOrFail();
        $this->assertNotNull($book->cover_image);
        Storage::disk('public')->assertExists($book->cover_image);
        $this->assertTrue((bool) $book->isbn_lookup_used);
        $this->assertSame('Ballantine Books', $book->publisher);
        $this->assertEqualsWithDelta(42, $book->listing_seconds, 2);
    }

    public function test_cover_is_only_downloaded_from_google_hosts(): void
    {
        $this->assertTrue(CoverDownloader::isAllowedUrl('https://books.google.com/books/content?id=1'));
        $this->assertFalse(CoverDownloader::isAllowedUrl('http://books.google.com/books/content?id=1'));
        $this->assertFalse(CoverDownloader::isAllowedUrl('https://evil.example.com/x.png'));
        $this->assertFalse(CoverDownloader::isAllowedUrl('https://books.google.com.evil.com/x.png'));
        $this->assertFalse(CoverDownloader::isAllowedUrl('https://127.0.0.1/x.png'));

        Http::fake();
        $this->actingAs($this->seller)->post('/books', [
            'category_id' => $this->category->id, 'title' => 'T', 'author' => 'A', 'isbn' => '9780306406157',
            'price' => 1, 'stock_quantity' => 1, 'cover_url' => 'http://169.254.169.254/latest/meta-data',
        ])->assertRedirect();
        Http::assertNothingSent();
        $this->assertNull(Book::where('isbn', '9780306406157')->value('cover_image'));
    }

    public function test_two_sellers_can_list_the_same_isbn_but_not_twice_each(): void
    {
        $other = User::factory()->create(['role' => 'seller', 'seller_status' => 'approved', 'email_verified_at' => now()]);
        $data = ['category_id' => $this->category->id, 'title' => 'Same Book', 'author' => 'A', 'isbn' => '9780306406157', 'price' => 10, 'stock_quantity' => 1];

        $this->actingAs($this->seller)->post('/books', $data)->assertSessionHasNoErrors();
        $this->actingAs($other)->post('/books', $data)->assertSessionHasNoErrors();
        $this->actingAs($this->seller)->post('/books', $data)->assertSessionHasErrors('isbn');

        $this->assertSame(2, Book::where('isbn', '9780306406157')->count());
    }

    public function test_test_lookup_command_reports_the_hit_rate(): void
    {
        $this->fakeGoogleBooks([$this->hailMary()]);

        $this->artisan('isbn:test-lookup', ['isbns' => ['9780593135204']])
            ->expectsOutputToContain('1 of 1 ISBNs (100%) returned title, author and cover')
            ->assertSuccessful();
    }
}
