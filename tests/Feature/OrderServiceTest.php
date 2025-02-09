<?php

namespace Tests\Feature;

use App\Models\Order;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEmpty;

class OrderServiceTest extends TestCase
{


    public function test_admin_views_all_orders()
    {
        $user = User::factory()->create([
            'role' => 'ADMIN'
        ]);

        $response = $this->getJson('/api/order', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $user->createToken('TestToken')->plainTextToken,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total'
        ]);
    }

    public function test_user_views_only_their_orders()
    {
        $user = User::factory()->create([
            'role' => 'USER'
        ]);

        $response = $this->getJson('/api/order', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $user->createToken('TestToken')->plainTextToken,
        ]);

        $response->assertStatus(200);

        collect($response->json('data'))->each(function ($order) use ($user) {
            $this->assertEquals($user->id, $order['user_id']);
        });
    }

    public function test_user_can_create_an_order()
    {
        $user = User::factory()->create([
            'role' => 'USER'
        ]);

        $product = Product::factory()->create([
            'stock_quantity' => 1,
            'price' => 10.00
        ]);

        $requestData = [
            'user_id' => $user->id,
            "name" => "Order Test",
            "description" => "Testing creation of orders",
            "status" => "DRAFT",

            "products" => [
                ["id" => $product->id, "qty" => 1]
            ]
        ];

        $response = $this->postJson('/api/order', $requestData, [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $user->createToken('TestToken')->plainTextToken,
        ]);

        $response->assertStatus(200);

        $id = $response->json('id');
        $newOrder = Order::where("id", $id)->first();

        assertNotEmpty($newOrder);
        assertEquals("Testing creation of orders", $newOrder->description);
    }


    public function test_user_can_update_an_order()
    {

        $user = User::factory()->create([
            'role' => 'USER'
        ]);

        $product = Product::factory()->create([
            'stock_quantity' => 1,
            'price' => 10.00
        ]);

        $requestData = [
            'user_id' => $user->id,
            "name" => "Order Test",
            "description" => "Testing updating of orders",
            "status" => "DRAFT",
            "products" => [
                ["id" => $product->id, "qty" => 1]
            ]
        ];

        $response = $this->patchJson('/api/order/1', $requestData, [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $user->createToken('TestToken')->plainTextToken,
        ]);

        $response->assertStatus(200);

        $updatedOrder = Order::where("id", '1')->first();

        assertNotEmpty($updatedOrder);
        assertEquals("Testing updating of orders", $updatedOrder->description);
    }


    public function test_user_can_delete_an_order()
    {

        $user = User::factory()->create([
            'role' => 'USER'
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'description' => 'Order to be deleted'
        ]);

        $response = $this->deleteJson('/api/order/' . $order->id, [], [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $user->createToken('TestToken')->plainTextToken,
        ]);


        $response->assertStatus(200);
        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }


    public function test_handles_concurrent_orders_with_different_users()
    {

        $product = Product::factory()->create([
            'stock_quantity' => 1,
            'price' => 10.00
        ]);

        $users = User::factory()->count(5)->create();

        $requestData = [
            "name" => "Order Test",
            "description" => "Testing concurrent orders",
            "status" => "DRAFT",
            "products" => [
                ["id" => $product->id, "qty" => 1]
            ]
        ];

        $responses = Http::pool(function ($pool) use ($users, $requestData) {
            $requests = [];
            foreach ($users as $user) {
                $requests['user_' . $user->id] = $pool->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $user->createToken('TestToken')->plainTextToken,
                ])->post(config("app.url") . '/api/order', array_merge($requestData, ['user_id' => $user->id]));
            }
            return $requests;
        });


        $responseStatuses = array_map(function ($response) {
            return  $response->status();
        }, $responses);

        $countOK = count(array_filter($responseStatuses, fn($status) => $status === 200));
        $countNotOk = count(array_filter($responseStatuses, fn($status) => $status != 200));

        $this->assertSame(1, $countOK, "There must be exactly one 200 code.");
        $this->assertSame(4, $countNotOk, "There must be exactly 4 different status code than 200");
    }
}
