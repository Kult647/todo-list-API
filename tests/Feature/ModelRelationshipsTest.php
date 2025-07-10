<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Profile;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /** @test Связь пользователя и профиля (один-к-одному) */
    public function test_user_has_one_profile()
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([
            'bio' => 'Test bio',
            'phone' => '1234567890',
            'address' => 'Test address',
        ]);

        $this->assertInstanceOf(Profile::class, $user->profile);
        $this->assertEquals($user->id, $profile->user_id);
    }

    /** @test Связь пользователя и задач (один-ко-многим) */
    public function test_user_has_many_tasks()
    {
        $user = User::factory()->create();
        $task1 = Task::factory()->create(['author_id' => $user->id]);
        $task2 = Task::factory()->create(['author_id' => $user->id]);

        $this->assertCount(2, $user->tasks);
        $this->assertInstanceOf(Task::class, $user->tasks->first());
    }

    /** @test Связь пользователя и проектов (многие-ко-многим) */
    public function test_user_belongs_to_many_projects()
    {
        $user = User::factory()->create();
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        $user->projects()->attach([$project1->id, $project2->id]);

        $this->assertCount(2, $user->projects);
        $this->assertInstanceOf(Project::class, $user->projects->first());
    }

    /** @test Связь пользователя и общих задач (многие-ко-многим) */
    public function test_user_has_many_shared_tasks()
    {
        $author = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['author_id' => $author->id]);

        $task->assignees()->attach($assignee->id);

        $this->assertCount(1, $assignee->sharedTasks);
        $this->assertInstanceOf(Task::class, $assignee->sharedTasks->first());
    }

    /** @test Связь задачи и проекта (многие-к-одному) */
    public function test_task_belongs_to_project()
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->assertInstanceOf(Project::class, $task->project);
        $this->assertEquals($project->id, $task->project->id);
    }

    /** @test Связь задачи и тегов (многие-ко-многим) */
    public function test_task_has_many_tags()
    {
        $task = Task::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $task->tags()->attach([$tag1->id, $tag2->id]);

        $this->assertCount(2, $task->tags);
        $this->assertInstanceOf(Tag::class, $task->tags->first());
    }

    /** @test Связь проекта и задач (один-ко-многим) */
    public function test_project_has_many_tasks()
    {
        $project = Project::factory()->create();
        $task1 = Task::factory()->create(['project_id' => $project->id]);
        $task2 = Task::factory()->create(['project_id' => $project->id]);

        $this->assertCount(2, $project->tasks);
        $this->assertInstanceOf(Task::class, $project->tasks->first());
    }

    /** @test Связь тега и задач (многие-ко-многим) */
    public function test_tag_has_many_tasks()
    {
        $tag = Tag::factory()->create();
        $task1 = Task::factory()->create();
        $task2 = Task::factory()->create();

        $tag->tasks()->attach([$task1->id, $task2->id]);

        $this->assertCount(2, $tag->tasks);
        $this->assertInstanceOf(Task::class, $tag->tasks->first());
    }
}
