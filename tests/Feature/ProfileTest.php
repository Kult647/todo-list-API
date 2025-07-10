<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test Получение профиля пользователя */
    public function test_user_can_view_their_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;
        // Создаем профиль пользователя
        $user->profile()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'bio', 'phone', 'address']
            ]);
    }

    /** @test Обновление профиля пользователя */
    public function test_user_can_update_their_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;
        // Создаем профиль пользователя
        $user->profile()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/profile', [
            'bio' => 'New bio',
            'phone' => '1234567890',
            'address' => 'New address',
        ]);

        $response->assertStatus(200)
            ->assertJson(['data' =>[
                'bio' => 'New bio',
                'phone' => '1234567890',
                'address' => 'New address',
            ]]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'bio' => 'New bio',
        ]);
    }
}
