<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Criteria;
use App\Models\Item;
use App\Models\ItemCriteria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiSecurityAndUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_cannot_escalate_role(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Role Escalation Attempt',
            'email' => 'escalation@example.com',
            'password' => 'password123',
            'user_type' => 'admin',
        ]);

        $response->assertStatus(201);
        $this->assertSame('user', $response->json('user.user_type'));
    }

    public function test_non_admin_cannot_create_user_through_admin_endpoint(): void
    {
        $user = User::factory()->create([
            'user_type' => 'user',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/users', [
            'name' => 'Illegal User',
            'email' => 'illegal@example.com',
            'password' => 'password123',
            'user_type' => 'user',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_create_category(): void
    {
        $user = User::factory()->create([
            'user_type' => 'user',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/categories', [
            'title' => 'Forbidden Category',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_update_role_fields_on_self(): void
    {
        $user = User::factory()->create([
            'user_type' => 'user',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/users/' . $user->id, [
            'user_type' => 'admin',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/user/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.png', 120, 120),
        ]);

        $response->assertStatus(200);
        $avatarUrl = $response->json('avatar_url');

        $this->assertNotNull($avatarUrl);
        $this->assertStringContainsString('/storage/avatars/', $avatarUrl);
        $this->assertTrue(str_starts_with($avatarUrl, 'http'));
    }

    public function test_user_can_upload_image_only_for_owned_item(): void
    {
        Storage::fake('public');

        $category = Category::create(['title' => 'Spark Wheel']);

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $collection = Collection::create([
            'title' => 'Owner Collection',
            'user_id' => $owner->id,
        ]);

        $item = Item::create([
            'title' => 'Owned Lighter',
            'collection_id' => $collection->id,
            'category1_id' => $category->id,
        ]);

        $forbiddenResponse = $this->actingAs($otherUser, 'sanctum')->postJson('/api/items/' . $item->id . '/image', [
            'image' => UploadedFile::fake()->image('forbidden.png', 640, 640),
        ]);
        $forbiddenResponse->assertStatus(403);

        $successResponse = $this->actingAs($owner, 'sanctum')->postJson('/api/items/' . $item->id . '/image', [
            'image' => UploadedFile::fake()->image('owned.png', 640, 640),
        ]);

        $successResponse->assertStatus(200);
        $imageUrl = $successResponse->json('image_url');
        $this->assertStringContainsString('/storage/items/', $imageUrl);
        $this->assertTrue(str_starts_with($imageUrl, 'http'));
    }

    public function test_item_requires_all_criteria_before_publication(): void
    {
        $category = Category::create(['title' => 'Friction']);
        $criterionA = Criteria::create(['name' => 'Durability']);
        $criterionB = Criteria::create(['name' => 'Price']);

        $user = User::factory()->create();
        $collection = Collection::create([
            'title' => 'My Lighters',
            'user_id' => $user->id,
        ]);

        $item = Item::create([
            'title' => 'Incomplete Item',
            'collection_id' => $collection->id,
            'category1_id' => $category->id,
            'status' => false,
        ]);

        $publishEarlyResponse = $this->actingAs($user, 'sanctum')->putJson('/api/items/' . $item->id, [
            'status' => true,
        ]);
        $publishEarlyResponse->assertStatus(422);

        ItemCriteria::create([
            'id_item' => $item->id,
            'id_criteria' => $criterionA->id_criteria,
            'value' => 2,
        ]);
        ItemCriteria::create([
            'id_item' => $item->id,
            'id_criteria' => $criterionB->id_criteria,
            'value' => 1,
        ]);

        $publishOkResponse = $this->actingAs($user, 'sanctum')->putJson('/api/items/' . $item->id, [
            'status' => true,
        ]);
        $publishOkResponse->assertStatus(200)
            ->assertJson(['status' => true]);
    }
}
