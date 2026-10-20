<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryAndProductSeeder extends Seeder
{
    public function run(): void
    {
        $catCoffee = Category::create([
            'name' => 'Hot & Iced Coffee',
            'slug' => 'coffee',
            'description' => 'Single-origin espresso drinks and specialty brews.',
            'icon' => 'coffee',
            'sort_order' => 1,
        ]);

        $catTea = Category::create([
            'name' => 'Artisan Teas & Refreshers',
            'slug' => 'tea-refreshers',
            'description' => 'Herbal infusions, matcha, and fruit teas.',
            'icon' => 'leaf',
            'sort_order' => 2,
        ]);

        $catBakery = Category::create([
            'name' => 'Pastries & Bakery',
            'slug' => 'bakery',
            'description' => 'Freshly baked croissants, muffins, and cakes.',
            'icon' => 'cake',
            'sort_order' => 3,
        ]);

        // Products in Coffee
        $cappuccino = Product::create([
            'category_id' => $catCoffee->id,
            'name' => 'Caffè Cappuccino',
            'slug' => 'caffe-cappuccino',
            'sku' => 'COF-CAP-001',
            'description' => 'Rich espresso with equal parts steamed milk and velvety foam.',
            'base_price' => 3.50,
            'is_available' => true,
        ]);

        ProductVariant::create(['product_id' => $cappuccino->id, 'name' => 'Regular (8 oz)', 'price_modifier' => 0.00, 'sku' => 'COF-CAP-REG']);
        ProductVariant::create(['product_id' => $cappuccino->id, 'name' => 'Large (12 oz)', 'price_modifier' => 0.75, 'sku' => 'COF-CAP-LRG']);

        $latte = Product::create([
            'category_id' => $catCoffee->id,
            'name' => 'Vanilla Bean Latte',
            'slug' => 'vanilla-bean-latte',
            'sku' => 'COF-LAT-002',
            'description' => 'Espresso with silky steamed milk and Madagascar vanilla.',
            'base_price' => 3.80,
            'is_available' => true,
        ]);

        ProductVariant::create(['product_id' => $latte->id, 'name' => 'Hot Regular', 'price_modifier' => 0.00, 'sku' => 'COF-LAT-HOT']);
        ProductVariant::create(['product_id' => $latte->id, 'name' => 'Iced Regular', 'price_modifier' => 0.40, 'sku' => 'COF-LAT-ICE']);
        ProductVariant::create(['product_id' => $latte->id, 'name' => 'Iced Large', 'price_modifier' => 1.00, 'sku' => 'COF-LAT-ILRG']);

        $americano = Product::create([
            'category_id' => $catCoffee->id,
            'name' => 'Classic Americano',
            'slug' => 'classic-americano',
            'sku' => 'COF-AME-003',
            'description' => 'Double espresso shot poured over hot purified water.',
            'base_price' => 2.50,
            'is_available' => true,
        ]);

        ProductVariant::create(['product_id' => $americano->id, 'name' => 'Hot', 'price_modifier' => 0.00, 'sku' => 'COF-AME-HOT']);
        ProductVariant::create(['product_id' => $americano->id, 'name' => 'Iced', 'price_modifier' => 0.30, 'sku' => 'COF-AME-ICE']);

        $caramelMacchiato = Product::create([
            'category_id' => $catCoffee->id,
            'name' => 'Caramel Cloud Macchiato',
            'slug' => 'caramel-cloud-macchiato',
            'sku' => 'COF-MAC-004',
            'description' => 'Freshly steamed milk with vanilla, marked with espresso and caramel drizzle.',
            'base_price' => 4.20,
            'is_available' => true,
        ]);

        ProductVariant::create(['product_id' => $caramelMacchiato->id, 'name' => 'Regular', 'price_modifier' => 0.00, 'sku' => 'COF-MAC-REG']);
        ProductVariant::create(['product_id' => $caramelMacchiato->id, 'name' => 'Large', 'price_modifier' => 0.80, 'sku' => 'COF-MAC-LRG']);

        // Products in Tea
        $matcha = Product::create([
            'category_id' => $catTea->id,
            'name' => 'Ceremonial Matcha Latte',
            'slug' => 'ceremonial-matcha-latte',
            'sku' => 'TEA-MAT-001',
            'description' => 'Uji ceremonial grade green tea whisked with whole or oat milk.',
            'base_price' => 4.00,
            'is_available' => true,
        ]);

        ProductVariant::create(['product_id' => $matcha->id, 'name' => 'Hot', 'price_modifier' => 0.00, 'sku' => 'TEA-MAT-HOT']);
        ProductVariant::create(['product_id' => $matcha->id, 'name' => 'Iced', 'price_modifier' => 0.50, 'sku' => 'TEA-MAT-ICE']);

        // Products in Bakery
        $croissant = Product::create([
            'category_id' => $catBakery->id,
            'name' => 'Butter Flaky Croissant',
            'slug' => 'butter-flaky-croissant',
            'sku' => 'BAK-CRO-001',
            'description' => 'Traditional French butter croissant baked golden crisp every morning.',
            'base_price' => 2.75,
            'is_available' => true,
        ]);

        $muffin = Product::create([
            'category_id' => $catBakery->id,
            'name' => 'Wild Blueberry Muffin',
            'slug' => 'wild-blueberry-muffin',
            'sku' => 'BAK-MUF-002',
            'description' => 'Moist muffin packed with organic wild blueberries and streusel crumble.',
            'base_price' => 2.90,
            'is_available' => true,
        ]);

        // Global Addons
        ProductAddon::create(['product_id' => null, 'name' => 'Extra Espresso Shot', 'price' => 0.80, 'is_active' => true]);
        ProductAddon::create(['product_id' => null, 'name' => 'Oat Milk Substitution', 'price' => 0.60, 'is_active' => true]);
        ProductAddon::create(['product_id' => null, 'name' => 'Vanilla Bean Syrup', 'price' => 0.50, 'is_active' => true]);
        ProductAddon::create(['product_id' => null, 'name' => 'Caramel Drizzle', 'price' => 0.50, 'is_active' => true]);
        ProductAddon::create(['product_id' => null, 'name' => 'Whipped Cream', 'price' => 0.40, 'is_active' => true]);
    }
}
