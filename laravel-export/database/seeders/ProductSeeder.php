<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Elektronik', 'Giyim', 'Ev & Yaşam', 'Spor', 'Kitap'];

        for ($i = 1; $i <= 50; $i++) {
            Product::create([
                'name' => $categories[array_rand($categories)] . ' Ürün ' . $i,
                'description' => 'Bu ürün #' . $i . ' için açıklama metni.',
                'price' => round(rand(1000, 100000) / 100, 2),
                'stock' => rand(0, 500),
                'sku' => 'SKU-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'is_active' => rand(0, 10) > 2,
            ]);
        }
    }
}
