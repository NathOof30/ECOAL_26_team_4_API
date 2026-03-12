<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Collection;
use App\Models\Category;
use App\Models\Item;
use App\Models\Criteria;
use App\Models\ItemCriteria;

class ApiOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed needed static data
        $this->cat1 = Category::create(['title' => 'Test Cat 1']);
        $this->crit1 = Criteria::create(['id_criteria' => 1, 'name' => 'Criteria 1']);

        // Create Users
        $this->user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create Collections
        $this->collection1 = Collection::create([
            'title' => 'Col 1',
            'user_id' => $this->user1->id,
        ]);

        $this->collection2 = Collection::create([
            'title' => 'Col 2',
            'user_id' => $this->user2->id,
        ]);

        // Create items
        $this->item1 = Item::create([
            'title' => 'Item 1',
        ]);
        $this->item1->collections()->attach($this->collection1->id);
        $this->item1->categories()->attach($this->cat1->id);

        $this->item2 = Item::create([
            'title' => 'Item 2',
        ]);
        $this->item2->collections()->attach($this->collection2->id);
        $this->item2->categories()->attach($this->cat1->id);

        // Create scores
        ItemCriteria::create([
            'id_item' => $this->item1->id,
            'id_criteria' => $this->crit1->id_criteria,
            'value' => 1,
        ]);
    }

    public function test_user_can_update_own_collection()
    {
        $response = $this->actingAs($this->user1, 'sanctum')->putJson('/api/v1/collections/' . $this->collection1->id, [
            'title' => 'Updated Col 1'
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals('Updated Col 1', $response->json('data.title'));
    }

    public function test_user_cannot_update_other_collection()
    {
        $response = $this->actingAs($this->user2, 'sanctum')->putJson('/api/v1/collections/' . $this->collection1->id, [
            'title' => 'Hacked Col 1'
        ]);
        
        $response->assertStatus(403);
    }

    public function test_user_can_update_own_item()
    {
        $response = $this->actingAs($this->user1, 'sanctum')->putJson('/api/v1/items/' . $this->item1->id, [
            'title' => 'Updated Item 1'
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals('Updated Item 1', $response->json('data.title'));
    }

    public function test_user_cannot_update_other_item()
    {
        $response = $this->actingAs($this->user2, 'sanctum')->putJson('/api/v1/items/' . $this->item1->id, [
            'title' => 'Hacked Item 1'
        ]);
        
        $response->assertStatus(403);
    }

    public function test_user_can_score_own_item()
    {
        // Try to update the score mapped to crit1
        $response = $this->actingAs($this->user1, 'sanctum')->putJson('/api/v1/items/' . $this->item1->id . '/criteria/' . $this->crit1->id_criteria, [
            'value' => 2
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.value'));
    }

    public function test_user_cannot_score_other_item()
    {
        $response = $this->actingAs($this->user2, 'sanctum')->putJson('/api/v1/items/' . $this->item1->id . '/criteria/' . $this->crit1->id_criteria, [
            'value' => 2
        ]);
        
        $response->assertStatus(403);
    }

    public function test_api_public_routes_work_without_auth()
    {
        $response = $this->getJson('/api/v1/items');
        $response->assertStatus(200);

        $response = $this->getJson('/api/v1/collections');
        $response->assertStatus(200);

        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(200);

        $response = $this->getJson('/api/v1/criteria');
        $response->assertStatus(200);

        $response = $this->getJson('/api/v1/users');
        $response->assertStatus(200);
    }

    public function test_user_can_create_item_automatically_assigned_to_collection()
    {
        // $this->user1 has $this->collection1
        $response = $this->actingAs($this->user1, 'sanctum')->postJson('/api/v1/items', [
            'title' => 'New Item',
            'category1_id' => $this->cat1->id,
        ]);

        $response->assertStatus(201);
        $this->assertEquals($this->collection1->id, $response->json('data.collection_id'));
    }

    public function test_regular_user_cannot_update_other_user_profile()
    {
        $response = $this->actingAs($this->user1, 'sanctum')->putJson('/api/v1/users/' . $this->user2->id, [
            'name' => 'Nope',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized. You can only update your own profile.']);
    }

    public function test_regular_user_cannot_escalate_own_role_or_active_flag()
    {
        $response = $this->actingAs($this->user1, 'sanctum')->putJson('/api/v1/users/' . $this->user1->id, [
            'user_type' => 'admin',
            'is_active' => false,
            'name' => 'Updated User 1',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated User 1')
            ->assertJsonPath('data.user_type', 'user')
            ->assertJsonPath('data.is_active', true);
    }

    public function test_admin_can_update_other_user_role()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->putJson('/api/v1/users/' . $this->user1->id, [
            'user_type' => 'editor',
            'is_active' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user_type', 'editor')
            ->assertJsonPath('data.is_active', false);
    }

    public function test_user_cannot_move_item_to_another_collection()
    {
        $response = $this->actingAs($this->user1, 'sanctum')->putJson('/api/v1/items/' . $this->item1->id, [
            'collection_id' => $this->collection2->id,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($this->collection1->id, $response->json('data.collection_id'));
    }

    public function test_admin_cannot_delete_own_account_through_endpoint()
    {
        $admin = User::create([
            'name' => 'Admin 2',
            'email' => 'admin2@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson('/api/v1/users/' . $admin->id);

        $response->assertStatus(403)
            ->assertJson(['message' => 'You cannot delete your own account through this endpoint.']);
    }
}
