<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
        
        // Re-run ALL necessary stored procedures migrations
        $this->artisan('migrate:refresh --path=database/migrations/2026_03_03_000103_create_sp_create_order.php');
        $this->artisan('migrate:refresh --path=database/migrations/2026_03_03_000104_create_sp_get_orders.php');
    }

    public function test_can_create_order_successfully()
    {
        $product = Product::factory()->create(['stock' => 10, 'price' => 100]);

        $data = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '123456789',
            'customer_address' => '123 Main St',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 2]
            ]
        ];

        $order = $this->orderService->createOrder($data);

        $this->assertNotNull($order);
        $this->assertEquals(201.60, $order->total_amount); // (200 - 10% desc + 12% IVA)
        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_cannot_create_order_with_insufficient_stock()
    {
        $product = Product::factory()->create(['stock' => 1, 'price' => 100]);

        $data = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '123456789',
            'customer_address' => '123 Main St',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 5]
            ]
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Stock insuficiente para el producto ID: ' . $product->id);

        $this->orderService->createOrder($data);
    }
}
