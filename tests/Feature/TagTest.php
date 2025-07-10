<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /** @test Создание нового тега */
    public function test_user_can_create_tag()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tags', [
            'name' => 'Test Tag',
            'color' => '#FFFFFF',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
               'data' => ['id', 'name', 'color']
            ]);

        $this->assertDatabaseHas('tags', [
            'name' => 'Test Tag',
            'color' => '#FFFFFF',
        ]);
    }

    /** @test Привязка тега к задаче */
    public function test_tag_can_be_assigned_to_task()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();
        $task = Task::factory()->create(['author_id' => $user->id]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/tasks/{$task->id}", [
            'title' => $task->title,
            'description' => $task->description,
            'tag_ids' => [$tag->id],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('task_tag', [
            'task_id' => $task->id,
            'tag_id' => $tag->id,
        ]);
    }
}
