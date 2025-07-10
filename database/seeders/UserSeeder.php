<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Создаем администратора
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $admin->profile()->create([
            'bio' => 'System administrator',
            'phone' => '+1234567890',
            'address' => 'Admin address',
        ]);

        // Создаем обычных пользователей
        User::factory(10)->create()->each(function ($user) {
            $user->profile()->create();
        });
    }
}
