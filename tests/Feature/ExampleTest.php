<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class ExampleTest extends TestCase
{
    public function test_register_and_login()
    {
        // Failed register
        $this->json('POST', 'api/register')
            ->assertStatus(400)
            ->assertJson([
                'message' => "Validation Failed",
            ]);

        $data = [
            'name' => 'test user',
            'email' => 'testuser@test.com',
            'password' => 'admin123',
            'password_confirmation' => 'admin123',
            'role' => 'writer',
        ];

        // Success register
        $response = $this->json('POST', 'api/register', $data)
            ->assertStatus(201)
            ->assertJsonStructure(['user', 'access_token', 'token_type']);

        // Failed login
        $this->json('POST', 'api/login')
            ->assertStatus(400)
            ->assertJson([
                'message' => "Validation Failed",
            ]);

        // Success login
        $data = ['email' => 'testuser@test.com', 'password' => 'admin123'];
        $res = $this->json('POST', 'api/login', $data);
        $res->assertStatus(200);
    }

    public function test_post_and_commment_CRUD()
    {
        $writer = ['create-post', 'update-post', 'delete-post'];
        $member = ['create-comment', 'update-comment', 'delete-comment'];
        $user = User::factory()->create();
        Sanctum::actingAs($user, $writer);

        $data = [
            'title' => 'Title',
            'thumbnail' => 'title.png',
            'content' => 'Some kind of content',
            'isPublished' => true,
        ];

        // Create Post
        $this->post('/api/posts', $data)
            ->assertStatus(201)
            ->assertJson([
                'post' => $data,
            ]);

        $data = [
            'title' => 'Title updated',
            'thumbnail' => 'title.png',
            'content' => 'Some kind of content',
            'isPublished' => true,
        ];

        // Update Post
        $this->patch('/api/posts/1', $data)
            ->assertStatus(200)
            ->assertJson([
                'post' => $data,
            ]);

        // Signin using Member
        Sanctum::actingAs($user, $member);

        // Create Comment
        $this->post('/api/posts/1/comments', ['comment' => 'My Comment'])
            ->assertStatus(201)
            ->assertJson(['comment' => ['comment' => 'My Comment']]);

        // Update Comment
        $this->patch('/api/posts/1/comments/1', ['comment' => 'My new Comment'])
            ->assertStatus(200)
            ->assertJson(['comment' => ['comment' => 'My new Comment']]);

        // Delete Comment
        $this->delete('/api/posts/1/comments/1')->assertStatus(204);
        $this->get('/api/posts/1/comment/1')->assertStatus(404);

        // Delete Post
        Sanctum::actingAs($user, $writer);
        $this->delete('/api/posts/1')->assertStatus(204);

        // Check if post deleted
        $this->get('/api/posts/1')->assertStatus(404);
    }

    public function test_role_permission()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $writer = ['create-post', 'update-post', 'delete-post'];
        $member = ['create-comment', 'update-comment', 'delete-comment'];

        $data = [
            'title' => 'Title',
            'thumbnail' => 'title.png',
            'content' => 'Some kind of content',
            'isPublished' => true,
        ];

        // Create some data
        Sanctum::actingAs($user, $writer);
        $this->post('/api/posts', $data)->assertStatus(201);

        Sanctum::actingAs($user, $member);
        $this->post('/api/posts/1/comments', ['comment' => 'My Comment'])
            ->assertStatus(201)
            ->assertJson(['comment' => ['comment' => 'My Comment']]);


        // Failed to comment as Writer
        Sanctum::actingAs($user, $writer);
        $this->post('/api/posts/1/comments', ['comment' => 'edit comment'])->assertStatus(403);

        // Other user's post
        Sanctum::actingAs($user1, $writer);
        $this->patch('/api/posts/1', $data)->assertStatus(403);

        // Failed to create post as Member
        Sanctum::actingAs($user, $member);
        $this->post('/api/posts', $data)->assertStatus(403);

        // Other user's comment
        Sanctum::actingAs($user1, $member);
        $this->patch('/api/posts/1/comments/1', ['comment' => 'Other comments'])->assertStatus(403);
    }
}
