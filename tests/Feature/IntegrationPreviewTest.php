<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_the_mock_screens_for_all_six_integrations(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $response = $this->actingAs($admin)->get(route('admin.integration-preview'));

        $response->assertOk()->assertSee('Mock data only');
        foreach (['IP-1', 'IP-2', 'IP-3', 'IP-4', 'IP-5', 'IP-6'] as $code) {
            $response->assertSee($code);
        }
    }

    public function test_buyers_cannot_open_the_preview(): void
    {
        $buyer = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);

        $this->actingAs($buyer)->get(route('admin.integration-preview'))->assertForbidden();
    }
}
