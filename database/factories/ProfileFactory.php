<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    public function definition()
    {
        return [
            'bio' => $this->faker->paragraph,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
        ];
    }
}
