<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        Project::factory(5)->create()->each(function ($project) use ($users) {
            // Добавляем случайных пользователей в проект
            $project->users()->attach(
                $users->random(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}
