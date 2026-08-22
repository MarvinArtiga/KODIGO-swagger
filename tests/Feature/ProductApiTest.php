<?php

use App\Models\Product;

it('lists public products and allows authenticated creation', function () {
    Product::factory()->count(2)->create();

    $this->getJson('/api/products')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'name', 'price', 'stock'],
            ],
        ]);

    $user = \App\Models\User::factory()->create();
    $token = auth('api')->login($user);

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/products', [
            'name' => 'Gaming Chair',
            'description' => 'Comfortable chair',
            'price' => 149.99,
            'stock' => 10,
        ])
        ->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Gaming Chair');
});
