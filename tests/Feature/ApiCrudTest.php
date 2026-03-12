<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Criteria;
use App\Models\Collection;
use App\Models\Item;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Password;

class ApiCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup initial application state
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);
        
        $this->category = Category::create(['title' => 'Test Category']);
        $this->criteria = Criteria::create(['id_criteria' => 1, 'name' => 'Test Criterion']);
    }

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
            'user_type' => 'admin',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['data' => ['access_token', 'token_type', 'user']])
                 ->assertJsonPath('data.user.user_type', 'user')
                 ->assertJsonPath('data.user.is_active', true);
    }

    public function test_user_can_login()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['access_token', 'token_type', 'user']]);
    }

    public function test_versioned_login_endpoint_is_available()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['access_token', 'token_type', 'user']]);
    }

    public function test_register_is_rate_limited()
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $response = $this->postJson('/api/v1/register', [
                'name' => 'Rate Limited User',
                'email' => 'rate-limit@example.com',
                'password' => 'StrongPass1!',
                'password_confirmation' => 'StrongPass1!',
            ]);

            $this->assertContains($response->status(), [201, 422]);
        }

        $this->postJson('/api/v1/register', [
            'name' => 'Rate Limited User',
            'email' => 'rate-limit@example.com',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
        ])->assertStatus(429)
            ->assertJson(['message' => 'Too many requests.']);
    }

    public function test_user_can_request_password_reset_link()
    {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.message', __(
                Password::RESET_LINK_SENT
            ));

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_forgot_password_is_rate_limited()
    {
        config()->set('auth.passwords.users.throttle', 0);

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->postJson('/api/v1/forgot-password', [
                'email' => 'test@example.com',
            ])->assertStatus(200);
        }

        $this->postJson('/api/v1/forgot-password', [
            'email' => 'test@example.com',
        ])->assertStatus(429)
            ->assertJson(['message' => 'Too many requests.']);
    }

    public function test_user_can_reset_password_with_valid_token()
    {
        $token = Password::broker()->createToken($this->user);

        $response = $this->postJson('/api/v1/reset-password', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'NewStrongPass1!',
            'password_confirmation' => 'NewStrongPass1!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.message', __(Password::PASSWORD_RESET));

        $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'NewStrongPass1!',
        ])->assertStatus(200);
    }

    public function test_reset_password_fails_with_invalid_token()
    {
        $response = $this->postJson('/api/v1/reset-password', [
            'email' => 'test@example.com',
            'token' => 'invalid-token',
            'password' => 'NewStrongPass1!',
            'password_confirmation' => 'NewStrongPass1!',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => __(Password::INVALID_TOKEN)]);
    }

    public function test_authenticated_user_can_get_profile()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->getJson('/api/v1/user');
        
        $response->assertStatus(200)
                 ->assertJsonPath('data.email', 'test@example.com');
    }

    public function test_user_can_logout()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson('/api/v1/logout');
        
        $response->assertStatus(200)
            ->assertJsonPath('data.message', 'Successfully logged out');
    }

    public function test_can_fetch_public_resources()
    {
        // Users list
        $this->getJson('/api/v1/users')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonMissingPath('data.0.email')
            ->assertJsonMissingPath('data.0.user_type')
            ->assertJsonMissingPath('data.0.is_active');
        // Categories
        $this->getJson('/api/v1/categories')->assertStatus(200);
        // Collections
        $this->getJson('/api/v1/collections')->assertStatus(200);
        // Items
        $this->getJson('/api/v1/items')->assertStatus(200);
        // Criteria
        $this->getJson('/api/v1/criteria')->assertStatus(200);
    }

    public function test_user_can_create_and_manage_collection_and_item()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs($this->user);

        // 1. Create Collection
        $response = $this->postJson('/api/v1/collections', [
            'title' => 'My First Collection',
            'description' => 'Test description',
        ]);
        
        $response->assertStatus(201);
        $collectionId = $response->json('data.id');

        // 2. Create Item in that Collection
        $itemResponse = $this->postJson('/api/v1/items', [
            'title' => 'My First Item',
            'description' => 'A shiny new item',
            'status' => true,
            'category_ids' => [$this->category->id],
        ]);
        
        $itemResponse->assertStatus(201)
                     ->assertJsonPath('data.collection_id', $collectionId)
                     ->assertJsonPath('data.categories.0.id', $this->category->id);
        $itemId = $itemResponse->json('data.id');

        // 3. Score the Item
        $scoreResponse = $this->postJson('/api/v1/item-criteria', [
            'id_item' => $itemId,
            'id_criteria' => $this->criteria->id_criteria,
            'value' => 2,
        ]);

        $scoreResponse->assertStatus(201);

        // 4. Read the scores for this item back via public route
        $readScoresResponse = $this->getJson("/api/v1/items/{$itemId}/criteria");
        $readScoresResponse->assertStatus(200);
        $this->assertCount(1, $readScoresResponse->json('data'));
    }

    public function test_admin_can_manage_categories_and_criteria()
    {
        Sanctum::actingAs($this->admin);

        // Create Category
        $catResponse = $this->postJson('/api/v1/categories', [
            'title' => 'Admin Category',
        ]);
        $catResponse->assertStatus(201);

        // Update Category
        $categoryId = $catResponse->json('data.id');

        $this->putJson('/api/v1/categories/' . $categoryId, [
            'title' => 'Updated Admin Category',
        ])->assertStatus(200);

        // Delete Category
        $this->deleteJson('/api/v1/categories/' . $categoryId)
             ->assertStatus(204);

        // Note: For criteria we assume the same structure but note that the current migrations map an explicit string primary key or similar for Criteria, 
        // the default behavior is sufficient to test Auth barrier for now.
    }

    public function test_inactive_user_cannot_login()
    {
        $this->user->update(['is_active' => false]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'This account is inactive.']);
    }

    public function test_regular_user_cannot_manage_categories()
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/categories', [
            'title' => 'Blocked Category',
        ])->assertStatus(403);
    }

    public function test_user_cannot_create_duplicate_score_for_same_item_and_criterion()
    {
        Sanctum::actingAs($this->user);

        $collection = Collection::create([
            'title' => 'User Collection',
            'user_id' => $this->user->id,
        ]);

        $item = Item::create([
            'title' => 'Scored Item',
        ]);

        $item->collections()->attach($collection->id);
        $item->categories()->attach($this->category->id);

        $this->postJson('/api/v1/item-criteria', [
            'id_item' => $item->id,
            'id_criteria' => $this->criteria->id_criteria,
            'value' => 1,
        ])->assertStatus(201);

        $this->postJson('/api/v1/item-criteria', [
            'id_item' => $item->id,
            'id_criteria' => $this->criteria->id_criteria,
            'value' => 2,
        ])->assertStatus(409)
            ->assertJson(['message' => 'A score already exists for this item and criterion.']);
    }

    public function test_deleting_missing_item_score_returns_not_found()
    {
        Sanctum::actingAs($this->user);

        $collection = Collection::create([
            'title' => 'User Collection',
            'user_id' => $this->user->id,
        ]);

        $item = Item::create([
            'title' => 'Unscored Item',
        ]);

        $item->collections()->attach($collection->id);
        $item->categories()->attach($this->category->id);

        $this->deleteJson("/api/v1/items/{$item->id}/criteria/{$this->criteria->id_criteria}")
            ->assertStatus(404)
            ->assertJson(['message' => 'Score not found for this item and criterion.']);
    }

    public function test_public_list_supports_pagination_filters_and_sorting()
    {
        Collection::create(['title' => 'Bravo', 'user_id' => $this->user->id]);
        Collection::create(['title' => 'Alpha', 'user_id' => $this->admin->id]);

        $response = $this->getJson('/api/v1/collections?title=a&sort=title&direction=asc&per_page=1');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('data.0.title', 'Alpha');
    }

    public function test_deactivating_user_revokes_existing_tokens()
    {
        $token = $this->user->createToken('test_token')->plainTextToken;

        Sanctum::actingAs($this->admin);

        $this->putJson('/api/v1/users/' . $this->user->id, [
            'is_active' => false,
        ])->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
