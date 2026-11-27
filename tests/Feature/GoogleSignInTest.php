<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

/** IP-6: Google sign-in through Laravel Socialite. */
class GoogleSignInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google.client_id' => 'test-client', 'services.google.client_secret' => 'test-secret']);
    }

    private function googleReturns(string $id, string $email, string $name = 'Juan Dela Cruz', bool $verified = true): void
    {
        $googleUser = (new GoogleUser())
            ->setRaw(['sub' => $id, 'email' => $email, 'email_verified' => $verified, 'name' => $name])
            ->map(['id' => $id, 'name' => $name, 'email' => $email]);

        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_redirect_sends_the_visitor_to_google(): void
    {
        $response = $this->get('/auth/google/redirect');

        $response->assertRedirect();
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/auth', $response->headers->get('Location'));
        $this->assertStringContainsString('client_id=test-client', $response->headers->get('Location'));
    }

    public function test_google_sign_in_is_hidden_when_not_configured(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/auth/google/redirect')->assertNotFound();
        $this->get('/login')->assertOk()->assertDontSee('Sign in with Google');
    }

    public function test_login_page_shows_the_google_button_when_configured(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in with Google');
    }

    public function test_new_google_user_gets_a_verified_buyer_account(): void
    {
        $this->googleReturns('google-123', 'Juan@Example.com');

        $this->get('/auth/google/callback?code=abc&state=xyz')->assertRedirect(route('user.dashboard'));

        $user = User::where('email', 'juan@example.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertSame('google-123', $user->google_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_account_is_linked_by_email(): void
    {
        $seller = User::factory()->create(['email' => 'seller@example.com', 'role' => 'seller', 'seller_status' => 'approved', 'email_verified_at' => now()]);
        $this->googleReturns('google-999', 'seller@example.com');

        $this->get('/auth/google/callback?code=abc')->assertRedirect(route('seller.dashboard'));

        $this->assertSame('google-999', $seller->fresh()->google_id);
        $this->assertAuthenticatedAs($seller);
        $this->assertSame(1, User::where('email', 'seller@example.com')->count());
    }

    public function test_unverified_google_email_is_refused(): void
    {
        $this->googleReturns('google-1', 'someone@example.com', verified: false);

        $this->get('/auth/google/callback?code=abc')->assertRedirect(route('login'))->assertSessionHas('error');
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_two_factor_still_applies_to_google_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'secure@example.com', 'email_verified_at' => now(),
            'two_factor_secret' => 'secret', 'two_factor_confirmed_at' => now(),
        ]);
        $this->googleReturns('google-2fa', 'secure@example.com');

        $this->get('/auth/google/callback?code=abc')->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
        $this->assertSame($user->id, session('2fa:user:id'));
    }

    public function test_cancelled_google_sign_in_returns_to_login(): void
    {
        $this->get('/auth/google/callback?error=access_denied')->assertRedirect(route('login'))->assertSessionHas('error');
    }
}
