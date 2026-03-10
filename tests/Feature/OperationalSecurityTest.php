<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        config()->set('security.tokens.revoke_existing_on_login', false);
        config()->set('cors.allowed_origins', ['http://localhost', 'http://127.0.0.1:3000', 'http://127.0.0.1:5173']);

        parent::tearDown();
    }

    public function test_health_live_returns_basic_status(): void
    {
        $this->getJson('/health/live')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['status', 'app', 'version', 'environment', 'timestamp']);
    }

    public function test_health_ready_returns_database_check_status(): void
    {
        $this->getJson('/health/ready')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database.status', 'ok');
    }

    public function test_cors_preflight_allows_configured_origin_for_api(): void
    {
        config()->set('cors.allowed_origins', ['http://frontend.test']);
        config()->set('cors.allowed_methods', ['*']);
        config()->set('cors.allowed_headers', ['*']);
        config()->set('cors.supports_credentials', true);

        $this->call('OPTIONS', '/api/v1/login', [], [], [], [
            'HTTP_ORIGIN' => 'http://frontend.test',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type, Authorization',
        ])->assertStatus(204)
            ->assertHeader('Access-Control-Allow-Origin', 'http://frontend.test');
    }

    public function test_login_can_revoke_existing_tokens_when_policy_is_enabled(): void
    {
        config()->set('security.tokens.revoke_existing_on_login', true);

        $user = User::factory()->create([
            'email' => 'revoke@example.com',
            'password' => bcrypt('password'),
        ]);

        $user->createToken('old_token');
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->postJson('/api/v1/login', [
            'email' => 'revoke@example.com',
            'password' => 'password',
        ])->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}
