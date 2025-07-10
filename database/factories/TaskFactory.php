<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition()
    {
        return [
            'author_id' => \App\Models\User::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'completed' => $this->faker->boolean,
            'due_date' => $this->faker->dateTimeBetween('now', '+1 year'),
        ];
    }
}
