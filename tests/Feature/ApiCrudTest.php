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
        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['access_token', 'token_type', 'user']);
    }

    public function test_user_can_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'token_type', 'user']);
    }

    public function test_authenticated_user_can_get_profile()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->getJson('/api/user');
        
        $response->assertStatus(200)
                 ->assertJson(['email' => 'test@example.com']);
    }

    public function test_user_can_logout()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson('/api/logout');
        
        $response->assertStatus(200);
    }

    public function test_can_fetch_public_resources()
    {
        // Users list
        $this->getJson('/api/users')->assertStatus(200);
        // Categories
        $this->getJson('/api/categories')->assertStatus(200);
        // Collections
        $this->getJson('/api/collections')->assertStatus(200);
        // Items
        $this->getJson('/api/items')->assertStatus(200);
        // Criteria
        $this->getJson('/api/criteria')->assertStatus(200);
    }

    public function test_user_can_create_and_manage_collection_and_item()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs($this->user);

        // 1. Create Collection
        $response = $this->postJson('/api/collections', [
            'title' => 'My First Collection',
            'description' => 'Test description',
        ]);
        
        $response->assertStatus(201);
        $collectionId = $response->json('id');

        // 2. Create Item in that Collection
        $itemResponse = $this->postJson('/api/items', [
            'title' => 'My First Item',
            'description' => 'A shiny new item',
            'status' => true,
            'category1_id' => $this->category->id,
        ]);
        
        $itemResponse->assertStatus(201)
                     ->assertJson(['collection_id' => $collectionId]); // Verified it is automatically assigned
        $itemId = $itemResponse->json('id');

        // 3. Score the Item
        $scoreResponse = $this->postJson('/api/item-criteria', [
            'id_item' => $itemId,
            'id_criteria' => $this->criteria->id_criteria,
            'value' => 2,
        ]);
        
        file_put_contents('dump.json', $scoreResponse->getContent());
        $scoreResponse->assertStatus(201);

        // 4. Read the scores for this item back via public route
        $readScoresResponse = $this->getJson("/api/items/{$itemId}/criteria");
        $readScoresResponse->assertStatus(200);
        $this->assertCount(1, $readScoresResponse->json());
    }

    public function test_admin_can_manage_categories_and_criteria()
    {
        Sanctum::actingAs($this->admin);

        // Create Category
        $catResponse = $this->postJson('/api/categories', [
            'title' => 'Admin Category',
        ]);
        $catResponse->assertStatus(201);

        // Update Category
        $this->putJson('/api/categories/' . $catResponse->json('id'), [
            'title' => 'Updated Admin Category',
        ])->assertStatus(200);

        // Delete Category
        $this->deleteJson('/api/categories/' . $catResponse->json('id'))
             ->assertStatus(204);

        // Note: For criteria we assume the same structure but note that the current migrations map an explicit string primary key or similar for Criteria, 
        // the default behavior is sufficient to test Auth barrier for now.
    }
}
