<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop',
                'description' => 'Portable computing device for work and entertainment.',
                'price' => 999.99,
                'stock' => 20,
            ],
            [
                'name' => 'Mouse',
                'description' => 'Wireless optical mouse with ergonomic design.',
                'price' => 29.99,
                'stock' => 50,
            ],
            [
                'name' => 'Teclado',
                'description' => 'Mechanical keyboard with customizable keys.',
                'price' => 79.99,
                'stock' => 35,
            ],
            [
                'name' => 'Monitor',
                'description' => '27-inch display with vibrant colors and crisp visuals.',
                'price' => 249.99,
                'stock' => 15,
            ],
            [
                'name' => 'Audífonos',
                'description' => 'Over-ear headphones with noise cancellation.',
                'price' => 129.99,
                'stock' => 25,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['name' => $product['name']],
                $product,
            );
        }
    }
}
