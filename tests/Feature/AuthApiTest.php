<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

it('registers a user and returns a token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'alice@example.com')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ]);
});

it('logs in an existing user and returns the authenticated profile', function () {
    $user = User::factory()->create([
        'email' => 'bob@example.com',
    ]);

    $login = $this->postJson('/api/login', [
        'email' => 'bob@example.com',
        'password' => 'password',
    ]);

    $login->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'bob@example.com');

    $token = $login->json('data.token');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('data.email', 'bob@example.com');
});
