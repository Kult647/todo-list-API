<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test Создание новой задачи */
    public function test_user_can_create_task()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $tag = Tag::factory()->create();
        $assignee = User::factory()->create();

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'project_id' => $project->id,
            'assignee_ids' => [$assignee->id],
            'tag_ids' => [$tag->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'completed',
                    'due_date',
                    'author' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                    'project' => ['id', 'name', 'description', 'created_at', 'updated_at'],
                    'assignees' => [['id', 'name', 'email', 'created_at', 'updated_at']],
                    'tags' => [['id', 'name', 'color', 'created_at', 'updated_at']],
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'author_id' => $user->id,
        ]);

        $taskId = $response->json('data.id');
        $this->assertDatabaseHas('task_user', [
            'task_id' => $taskId,
            'user_id' => $assignee->id,
        ]);
        $this->assertDatabaseHas('task_tag', [
            'task_id' => $taskId,
            'tag_id' => $tag->id,
        ]);
    }

    /** @test Получение списка задач с фильтрацией по проекту */
    public function test_user_can_filter_tasks_by_project()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $tasksInProject = Task::factory(3)->create([
            'author_id' => $user->id,
            'project_id' => $project->id,
        ]);
        $tasksNotInProject = Task::factory(2)->create(['author_id' => $user->id]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/tasks?project_id={$project->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response['data']);
    }

    /** @test Получение списка задач с фильтрацией по тегам */
    public function test_user_can_filter_tasks_by_tags()
    {
        $user = User::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $taskWithTag1 = Task::factory()->create(['author_id' => $user->id]);
        $taskWithTag1->tags()->attach($tag1);

        $taskWithBothTags = Task::factory()->create(['author_id' => $user->id]);
        $taskWithBothTags->tags()->attach([$tag1->id, $tag2->id]);

        $taskWithoutTags = Task::factory()->create(['author_id' => $user->id]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/tasks?tag_ids={$tag1->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response['data']);
    }

    /** @test Поиск задач по названию и описанию */
    public function test_user_can_search_tasks()
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'author_id' => $user->id,
            'title' => 'Unique title for search',
            'description' => 'Test description',
        ]);
        Task::factory()->create([
            'author_id' => $user->id,
            'title' => 'Another task',
            'description' => 'Unique description for search',
        ]);
        Task::factory()->create(['author_id' => $user->id]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tasks?search=Unique');

        $response->assertStatus(200);
        $this->assertCount(2, $response['data']);
    }

    /** @test Мягкое удаление и восстановление задачи */
    public function test_task_soft_delete_and_restore()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['author_id' => $user->id]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Удаление задачи
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);

        // Восстановление задачи
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/tasks/{$task->id}/restore");

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
    }

    /** @test Проверка доступа к задачам, где пользователь является соисполнителем */
    public function test_user_can_view_shared_tasks()
    {
        $author = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['author_id' => $author->id]);
        $task->assignees()->attach($assignee->id);

        $token = $assignee->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tasks');

        $response->assertStatus(200);
        $this->assertCount(1, $response['data']);
        $this->assertEquals($task->id, $response['data'][0]['id']);
    }
}
