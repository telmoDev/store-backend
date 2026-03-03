<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService();
        
        // Re-run stored procedures migration as RefreshDatabase might drop them
        $this->artisan('migrate:refresh --path=database/migrations/2026_03_03_000101_create_sp_get_products.php');
        $this->artisan('migrate:refresh --path=database/migrations/2026_03_03_000102_create_sp_get_products_count.php');
    }

    public function test_can_list_products_and_search_by_name()
    {
        // Create sample products
        Product::factory()->create(['name' => 'Laptop Gamer', 'sku' => 'LAP-001', 'stock' => 10, 'price' => 1200]);
        Product::factory()->create(['name' => 'Mouse Wireless', 'sku' => 'MSE-001', 'stock' => 50, 'price' => 25]);
        Product::factory()->create(['name' => 'Teclado Mecanico', 'sku' => 'KBD-001', 'stock' => 20, 'price' => 80]);

        // Search for 'Laptop'
        $results = $this->productService->listProducts(['search' => 'Laptop']);
        
        $this->assertCount(1, $results);
        $this->assertEquals('Laptop Gamer', $results[0]->name);
    }

    public function test_can_search_by_sku()
    {
        Product::factory()->create(['name' => 'Laptop Gamer', 'sku' => 'LAP-001', 'stock' => 10, 'price' => 1200]);
        Product::factory()->create(['name' => 'Mouse Wireless', 'sku' => 'MSE-001', 'stock' => 50, 'price' => 25]);

        // Search for SKU 'MSE'
        $results = $this->productService->listProducts(['search' => 'MSE']);
        
        $this->assertCount(1, $results);
        $this->assertEquals('Mouse Wireless', $results[0]->name);
    }
}
