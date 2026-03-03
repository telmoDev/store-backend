<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear productos específicos
        $products = [
            [
                'name' => 'Laptop Gamer Pro',
                'sku' => 'LP-001',
                'price' => 1299.99,
                'stock' => 15,
            ],
            [
                'name' => 'Smartphone Ultra 5G',
                'sku' => 'SP-002',
                'price' => 899.50,
                'stock' => 25,
            ],
            [
                'name' => 'Audífonos Bluetooth Noise Cancelling',
                'sku' => 'AU-003',
                'price' => 199.99,
                'stock' => 50,
            ],
            [
                'name' => 'Monitor 4K 27"',
                'sku' => 'MN-004',
                'price' => 349.00,
                'stock' => 10,
            ],
            [
                'name' => 'Teclado Mecánico RGB',
                'sku' => 'TC-005',
                'price' => 89.99,
                'stock' => 30,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Crear 15 productos adicionales aleatorios
        Product::factory(15)->create();
    }
}
