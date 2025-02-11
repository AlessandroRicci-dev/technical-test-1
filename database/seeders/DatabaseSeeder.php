<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Order;
use App\Models\Product;
use Meilisearch\Client;
use App\Models\OrderItem;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

    private object $products;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'Test Admin',
            'email' => "admin@test.com",
            'password' => Hash::make('password'),
            'role' => 'ADMIN'
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => "user@test.com",
            'password' => Hash::make('password'),
            'role' => 'USER'
        ]);

        $products = Product::factory(50)->create();

        User::factory(10)->create()->each(function ($user) use ($products) {
            Order::factory(5)->create([
                "user_id" => $user->id
            ])->each(function ($order) use ($products) {
                OrderItem::factory(3)->create([
                    'order_id' => $order->id,
                    'product_id' => $products->random()->id,
                ]);
            });
        });

        ///update configuration for meilisearch

        $client = new Client(config('scout.meilisearch.host'));
        $index = $client->index('orders');
        $index->updateFilterableAttributes(['user_id', 'name', 'description', 'created_at']);
        $index->updateSortableAttributes(['user_id', 'name', 'description', 'created_at']);
    }
}
