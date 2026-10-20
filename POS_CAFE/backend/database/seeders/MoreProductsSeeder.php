<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class MoreProductsSeeder extends Seeder
{
    public function run(): void
    {
        $catCoffee = Category::where('slug', 'coffee')->first() ?: Category::first();
        $catTea = Category::where('slug', 'tea-refreshers')->first() ?: Category::first();
        $catBakery = Category::where('slug', 'bakery')->first() ?: Category::first();

        // New category: Blended Frappes & Smoothies
        $catBlended = Category::firstOrCreate(
            ['slug' => 'blended-frappes'],
            [
                'name' => 'Blended Frappes & Smoothies',
                'description' => 'Ice-blended frappes, fruit smoothies, and specialty crushed ice drinks.',
                'icon' => 'sparkles',
                'sort_order' => 4,
                'is_active' => true,
            ]
        );

        $productsData = [
            // Coffees
            [
                'category_id' => $catCoffee->id,
                'name' => 'Iced Spanish Latte',
                'slug' => 'iced-spanish-latte',
                'sku' => 'COF-SPN-005',
                'description' => 'Double espresso shot with creamy condensed milk over iced whole milk.',
                'base_price' => 4.50,
                'image_path' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => [
                    ['name' => 'Regular (12 oz)', 'price_modifier' => 0.00, 'sku' => 'COF-SPN-REG'],
                    ['name' => 'Large (16 oz)', 'price_modifier' => 0.80, 'sku' => 'COF-SPN-LRG'],
                ]
            ],
            [
                'category_id' => $catCoffee->id,
                'name' => 'Cold Brew Signature',
                'slug' => 'cold-brew-signature',
                'sku' => 'COF-CLD-006',
                'description' => 'Single-origin beans slow-steeped for 20 hours for an ultra-smooth chocolate finish.',
                'base_price' => 3.90,
                'image_path' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => [
                    ['name' => 'Regular', 'price_modifier' => 0.00, 'sku' => 'COF-CLD-REG'],
                    ['name' => 'Large', 'price_modifier' => 0.70, 'sku' => 'COF-CLD-LRG'],
                ]
            ],
            [
                'category_id' => $catCoffee->id,
                'name' => 'Dark Chocolate Mocha',
                'slug' => 'dark-chocolate-mocha',
                'sku' => 'COF-MOC-007',
                'description' => 'Espresso combined with Belgian dark chocolate ganache and steamed silky milk.',
                'base_price' => 4.25,
                'image_path' => 'https://images.unsplash.com/photo-1578314675249-a6910f80cc4e?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => [
                    ['name' => 'Hot Regular', 'price_modifier' => 0.00, 'sku' => 'COF-MOC-HOT'],
                    ['name' => 'Iced Regular', 'price_modifier' => 0.40, 'sku' => 'COF-MOC-ICE'],
                ]
            ],
            [
                'category_id' => $catCoffee->id,
                'name' => 'Velvet Flat White',
                'slug' => 'velvet-flat-white',
                'sku' => 'COF-FLT-008',
                'description' => 'Double ristretto espresso blend with dense micro-foamed textured milk.',
                'base_price' => 3.75,
                'image_path' => 'https://images.unsplash.com/photo-1577968897966-3d4325b36b61?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => [
                    ['name' => 'Standard (6 oz)', 'price_modifier' => 0.00, 'sku' => 'COF-FLT-STD'],
                ]
            ],
            [
                'category_id' => $catCoffee->id,
                'name' => 'Doppio Espresso',
                'slug' => 'doppio-espresso',
                'sku' => 'COF-ESP-009',
                'description' => 'Two ounces of pure single-origin espresso extraction with rich hazelnut crema.',
                'base_price' => 2.40,
                'image_path' => 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => []
            ],

            // Teas & Refreshers
            [
                'category_id' => $catTea->id,
                'name' => 'Iced Peach White Tea',
                'slug' => 'iced-peach-white-tea',
                'sku' => 'TEA-PCH-002',
                'description' => 'Delicate white tea infused with white peach purée, served crisp over ice.',
                'base_price' => 3.70,
                'image_path' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => [
                    ['name' => 'Regular', 'price_modifier' => 0.00, 'sku' => 'TEA-PCH-REG'],
                    ['name' => 'Large', 'price_modifier' => 0.60, 'sku' => 'TEA-PCH-LRG'],
                ]
            ],
            [
                'category_id' => $catTea->id,
                'name' => 'Wild Hibiscus Refresher',
                'slug' => 'wild-hibiscus-refresher',
                'sku' => 'TEA-HIB-003',
                'description' => 'Organic ruby hibiscus blossoms, fresh berries, mint sprig, and lemon.',
                'base_price' => 3.85,
                'image_path' => 'https://images.unsplash.com/photo-1597481499750-3e6b22637e12?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => [
                    ['name' => 'Iced Still', 'price_modifier' => 0.00, 'sku' => 'TEA-HIB-STL'],
                    ['name' => 'Sparkling Soda', 'price_modifier' => 0.50, 'sku' => 'TEA-HIB-SPK'],
                ]
            ],
            [
                'category_id' => $catTea->id,
                'name' => 'Earl Grey Milk Tea',
                'slug' => 'earl-grey-milk-tea',
                'sku' => 'TEA-EGL-004',
                'description' => 'Fragrant bergamot black tea brewed slowly with brown sugar and creamy milk.',
                'base_price' => 3.90,
                'image_path' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => [
                    ['name' => 'Hot', 'price_modifier' => 0.00, 'sku' => 'TEA-EGL-HOT'],
                    ['name' => 'Iced', 'price_modifier' => 0.40, 'sku' => 'TEA-EGL-ICE'],
                ]
            ],
            [
                'category_id' => $catTea->id,
                'name' => 'Fresh Lemon Honey Ice Tea',
                'slug' => 'fresh-lemon-honey-ice-tea',
                'sku' => 'TEA-LMN-005',
                'description' => 'Cold shaken Ceylon black tea with squeezed fresh lemon juice and organic wildflower honey.',
                'base_price' => 3.30,
                'image_path' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => []
            ],

            // Pastries & Food
            [
                'category_id' => $catBakery->id,
                'name' => 'Pain au Chocolat',
                'slug' => 'pain-au-chocolat',
                'sku' => 'BAK-PAC-003',
                'description' => 'Multi-layered French puff pastry enclosing two batons of semi-sweet dark chocolate.',
                'base_price' => 3.20,
                'image_path' => 'https://images.unsplash.com/photo-1530610476181-d83430b64dcd?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => []
            ],
            [
                'category_id' => $catBakery->id,
                'name' => 'Warm Cinnamon Roll',
                'slug' => 'warm-cinnamon-roll',
                'sku' => 'BAK-CIN-004',
                'description' => 'Freshly baked brioche swirl roll with Korintje cinnamon and cream cheese glaze.',
                'base_price' => 3.50,
                'image_path' => 'https://images.unsplash.com/photo-1509365465985-25d11c17e812?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => []
            ],
            [
                'category_id' => $catBakery->id,
                'name' => 'Classic NY Cheesecake',
                'slug' => 'classic-ny-cheesecake',
                'sku' => 'BAK-CHK-005',
                'description' => 'Velvety New York style cheesecake on a honey graham cracker crumb crust.',
                'base_price' => 4.60,
                'image_path' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => []
            ],
            [
                'category_id' => $catBakery->id,
                'name' => 'Fudge Walnut Brownie',
                'slug' => 'fudge-walnut-brownie',
                'sku' => 'BAK-BRN-006',
                'description' => 'Ultra-fudgy dark cocoa brownie with roasted California walnuts and sea salt.',
                'base_price' => 3.10,
                'image_path' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => []
            ],
            [
                'category_id' => $catBakery->id,
                'name' => 'Almond Glazed Danish',
                'slug' => 'almond-glazed-danish',
                'sku' => 'BAK-ALM-007',
                'description' => 'Crisp golden puff pastry centered with almond marzipan frangipane cream.',
                'base_price' => 3.40,
                'image_path' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => []
            ],
            [
                'category_id' => $catBakery->id,
                'name' => 'Sourdough Avocado Toast',
                'slug' => 'sourdough-avocado-toast',
                'sku' => 'BAK-AVO-008',
                'description' => 'Thick-cut country sourdough, crushed Hass avocado, lemon, sea salt flakes, and chili crunch.',
                'base_price' => 4.95,
                'image_path' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => []
            ],

            // Blended Frappes & Smoothies
            [
                'category_id' => $catBlended->id,
                'name' => 'Caramel Crunch Frappé',
                'slug' => 'caramel-crunch-frappe',
                'sku' => 'FRP-CRM-001',
                'description' => 'Blended espresso, buttery caramel, milk, and ice, crowned with whipped cream and toffee bits.',
                'base_price' => 4.75,
                'image_path' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => [
                    ['name' => 'Regular', 'price_modifier' => 0.00, 'sku' => 'FRP-CRM-REG'],
                    ['name' => 'Large', 'price_modifier' => 0.85, 'sku' => 'FRP-CRM-LRG'],
                ]
            ],
            [
                'category_id' => $catBlended->id,
                'name' => 'Matcha Frost Frappé',
                'slug' => 'matcha-frost-frappe',
                'sku' => 'FRP-MAT-002',
                'description' => 'Japanese ceremonial grade matcha whisked with creamy vanilla milk, crushed ice and whipped cream.',
                'base_price' => 4.85,
                'image_path' => 'https://images.unsplash.com/photo-1515823662972-da6a2e4d3002?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => [
                    ['name' => 'Regular', 'price_modifier' => 0.00, 'sku' => 'FRP-MAT-REG'],
                    ['name' => 'Large', 'price_modifier' => 0.85, 'sku' => 'FRP-MAT-LRG'],
                ]
            ],
            [
                'category_id' => $catBlended->id,
                'name' => 'Strawberry Vanilla Smoothie',
                'slug' => 'strawberry-vanilla-smoothie',
                'sku' => 'FRP-STR-003',
                'description' => 'Real sweet strawberries, Greek yogurt, pure honey, and Madagascar vanilla blended frosty smooth.',
                'base_price' => 4.40,
                'image_path' => 'https://images.unsplash.com/photo-1553530666-ba11a7da3888?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'variants' => []
            ],
        ];

        foreach ($productsData as $p) {
            $variants = $p['variants'];
            unset($p['variants']);

            $product = Product::firstOrCreate(['sku' => $p['sku']], $p);
            $product->update($p);

            foreach ($variants as $v) {
                ProductVariant::firstOrCreate(
                    ['product_id' => $product->id, 'name' => $v['name']],
                    $v
                );
            }
        }
    }
}
