<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем тестового пользователя один раз для всех тестов
        $this->user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        // Создаем токен для пользователя
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test Проверка регистрации нового пользователя */
    public function test_register_route()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token'
            ]);
    }

    /** @test Проверка входа пользователя */
    public function test_login_route()
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token'
            ]);
    }

    /** @test Проверка получения данных текущего пользователя */
    public function test_user_route()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email'],
            ]);
    }

    /** @test Проверка выхода пользователя */
    public function test_logout_route()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully'
            ]);
    }

    /** @test Проверка работы с профилем пользователя */
    public function test_profile_routes()
    {
        // Создаем профиль для пользователя
        $this->user->profile()->create([
            'bio' => 'Test bio',
            'phone' => '1234567890',
            'address' => 'Test address'
        ]);

        // Получение профиля
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/profile');


        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' =>  ['id', 'user_id', 'bio', 'phone', 'address']
            ]);

    // Обновление профиля
        $updateData = [
            'bio' => 'Updated bio',
            'phone' => '9876543210',
            'address' => 'Updated address',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/profile', $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'bio', 'phone', 'address']
            ])
            ->assertJson([
                'data' => $updateData
            ]);
        ///////dd($response->json());
        // Дополнительная проверка, что данные действительно обновились в БД
        $this->assertDatabaseHas('profiles', array_merge(
            ['user_id' => $this->user->id],
            $updateData
        ));
    }

    /** @test Проверка CRUD операций для задач */
    public function test_task_routes()
    {
        $project = Project::factory()->create();
        $tag = Tag::factory()->create();
        $assignee = User::factory()->create();

        // Создание задачи
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
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

        $taskData = $response->json('data');
        $taskId = $taskData['id'];

        // Проверка создания задачи в базе
        $this->assertDatabaseHas('tasks', [
            'id' => $taskId,
            'title' => 'Test Task'
        ]);

        // Получение списка задач
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'title',
                    'description',
                    'completed',
                    'due_date',
                    'author',
                    'project',
                    'assignees',
                    'tags',
                    'created_at',
                    'updated_at'
                ]]
            ]);

        // Получение конкретной задачи
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/tasks/{$taskId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'completed',
                    'due_date',
                    'author',
                    'project',
                    'assignees',
                    'tags',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $taskId,
                    'title' => 'Test Task'
                ]
            ]);
    }

    /** @test Проверка CRUD операций для проектов */
    public function test_project_routes()
    {
        $newUser = User::factory()->create();

        // Создание проекта
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/projects', [
            'name' => 'Test Project',
            'description' => 'Test Description',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' =>  ['id', 'name', 'description']
            ]);

        $projectId = $response->json('data.id');

        // Проверка создания проекта в базе
        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'name' => 'Test Project'
        ]);
    }

    /** @test Проверка CRUD операций для тегов */
    public function test_tag_routes()
    {
        // Создание тега
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tags', [
            'name' => 'Test Tag',
            'color' => '#FFFFFF',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'color']
            ]);

        $tagId = $response->json('data.id');

        // Проверка создания тега в базе
        $this->assertDatabaseHas('tags', [
            'id' => $tagId,
            'name' => 'Test Tag'
        ]);
    }
}
