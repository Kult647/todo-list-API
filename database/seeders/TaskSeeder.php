<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $projects = \App\Models\Project::all();
        $tags = \App\Models\Tag::all();

        Task::factory(30)->create()->each(function ($task) use ($users, $projects, $tags) {
            // Назначаем случайный проект (может быть null)
            if (rand(0, 1)) {
                $task->project()->associate($projects->random());
                $task->save();
            }

            // Добавляем случайных соисполнителей
            $assignees = $users->where('id', '!=', $task->author_id)->random(rand(0, 2));
            if ($assignees->isNotEmpty()) {
                $task->assignees()->attach($assignees->pluck('id')->toArray());
            }

            // Добавляем случайные теги
            if ($tags->isNotEmpty()) {
                $task->tags()->attach(
                    $tags->random(rand(1, 3))->pluck('id')->toArray()
                );
            }
        });
    }
}
