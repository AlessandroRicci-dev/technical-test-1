<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;


class AuthenticationTest extends TestCase
{

    public function test_can_auth_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'password'),
        ]);

        $response = $this->postJson('/api/auth', [
            'email'    => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    public function test_cannot_auth_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth', [
            'email' => fake()->email(),
            'password' => 'wrongPassword',
        ]);

        $response->assertStatus(401);
        $response->assertJsonStructure(['error', 'message']);
    }

    public function test_cannot_auth_with_malformed_credentials(): void
    {
        $response = $this->postJson('/api/auth', [
            'email' => 'thisIsNotAnEmail',
            'password' => 'wrongPassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }
}
