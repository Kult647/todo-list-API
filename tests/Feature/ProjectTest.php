<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test Создание нового проекта */
    public function test_user_can_create_project()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/projects', [
            'name' => 'Test Project',
            'description' => 'Test Description',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'description', 'users', 'tasks']
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
        ]);

        $projectId = $response->json('data.id');
        $this->assertDatabaseHas('project_user', [
            'project_id' => $projectId,
            'user_id' => $user->id,
        ]);
    }

    /** @test Добавление пользователя в проект */
    public function test_user_can_add_user_to_project()
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($owner->id);

        $newUser = User::factory()->create();

        $token = $owner->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/projects/{$project->id}/add-user", [
            'user_id' => $newUser->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $newUser->id,
        ]);
    }

    /** @test Удаление пользователя из проекта */
    public function test_user_can_remove_user_from_project()
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($owner->id);

        $userToRemove = User::factory()->create();
        $project->users()->attach($userToRemove->id);

        $token = $owner->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/projects/{$project->id}/remove-user", [
            'user_id' => $userToRemove->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $userToRemove->id,
        ]);
    }

    /** @test Проверка, что участники проекта видят задачи проекта */
    public function test_project_members_can_view_project_tasks()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $project = Project::factory()->create();

        // Добавляем пользователей в проект
        $project->users()->attach([$user1->id, $user2->id]);

        // Создаем задачи от имени разных пользователей
        $task1 = Task::factory()->create([
            'author_id' => $user1->id,
            'project_id' => $project->id,
        ]);

        $task2 = Task::factory()->create([
            'author_id' => $user2->id,
            'project_id' => $project->id,
        ]);

        // Аутентифицируем первого пользователя
        $token = $user1->createToken('auth_token')->plainTextToken;

        // Запрашиваем задачи проекта
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/tasks?project_id={$project->id}");

        $response->assertStatus(200);

        // Проверяем, что получены обе задачи проекта
        $this->assertCount(2, $response->json('data'));

        // Дополнительная проверка, что в ответе есть обе задачи
        $taskIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($task1->id, $taskIds);
        $this->assertContains($task2->id, $taskIds);
    }
}
