<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class IngredientAndRecipeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        $beans = Ingredient::create([
            'name' => 'Single Origin Arabica Beans',
            'sku' => 'ING-BEAN-001',
            'unit' => 'g',
            'cost_per_unit' => 0.0250,
            'current_stock' => 15000.000, // 15 kg
            'min_stock_alert' => 2000.000,
            'is_active' => true,
        ]);

        $milk = Ingredient::create([
            'name' => 'Fresh Whole Dairy Milk',
            'sku' => 'ING-MILK-001',
            'unit' => 'ml',
            'cost_per_unit' => 0.0020,
            'current_stock' => 25000.000, // 25 liters
            'min_stock_alert' => 5000.000,
            'is_active' => true,
        ]);

        $oatMilk = Ingredient::create([
            'name' => 'Barista Oat Milk',
            'sku' => 'ING-OAT-001',
            'unit' => 'ml',
            'cost_per_unit' => 0.0040,
            'current_stock' => 12000.000,
            'min_stock_alert' => 2000.000,
            'is_active' => true,
        ]);

        $vanilla = Ingredient::create([
            'name' => 'Natural Vanilla Syrup',
            'sku' => 'ING-SYR-VAN',
            'unit' => 'ml',
            'cost_per_unit' => 0.0150,
            'current_stock' => 5000.000,
            'min_stock_alert' => 1000.000,
            'is_active' => true,
        ]);

        $caramel = Ingredient::create([
            'name' => 'Gourmet Caramel Sauce',
            'sku' => 'ING-SAU-CAR',
            'unit' => 'ml',
            'cost_per_unit' => 0.0180,
            'current_stock' => 4000.000,
            'min_stock_alert' => 800.000,
            'is_active' => true,
        ]);

        $matchaPowder = Ingredient::create([
            'name' => 'Ceremonial Matcha Powder',
            'sku' => 'ING-TEA-MAT',
            'unit' => 'g',
            'cost_per_unit' => 0.0800,
            'current_stock' => 2000.000,
            'min_stock_alert' => 400.000,
            'is_active' => true,
        ]);

        $croissants = Ingredient::create([
            'name' => 'Ready-to-bake Croissant Dough',
            'sku' => 'ING-BAK-CRO',
            'unit' => 'pcs',
            'cost_per_unit' => 0.8500,
            'current_stock' => 50.000,
            'min_stock_alert' => 15.000,
            'is_active' => true,
        ]);

        // Record opening stock movements in ledger
        $initialIngredients = [$beans, $milk, $oatMilk, $vanilla, $caramel, $matchaPowder, $croissants];
        foreach ($initialIngredients as $ing) {
            InventoryMovement::create([
                'ingredient_id' => $ing->id,
                'user_id' => $admin?->id,
                'movement_type' => 'adjustment_increase',
                'quantity' => $ing->current_stock,
                'previous_stock' => 0,
                'new_stock' => $ing->current_stock,
                'unit_cost' => $ing->cost_per_unit,
                'reference_type' => 'manual',
                'reason' => 'Opening inventory balance setup',
            ]);
        }

        // Recipes
        // Cappuccino
        $cappuccino = Product::where('slug', 'caffe-cappuccino')->first();
        if ($cappuccino) {
            $recipe = Recipe::create(['product_id' => $cappuccino->id, 'instructions' => 'Extract 36g double shot (18g dry beans), steam 180ml whole milk with microfoam.']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $beans->id, 'quantity' => 18.000, 'unit' => 'g']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $milk->id, 'quantity' => 180.000, 'unit' => 'ml']);
        }

        // Vanilla Latte
        $latte = Product::where('slug', 'vanilla-bean-latte')->first();
        if ($latte) {
            $recipe = Recipe::create(['product_id' => $latte->id, 'instructions' => 'Pump 15ml vanilla syrup into cup, add double espresso shot, top with steamed milk.']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $beans->id, 'quantity' => 18.000, 'unit' => 'g']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $milk->id, 'quantity' => 220.000, 'unit' => 'ml']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $vanilla->id, 'quantity' => 15.000, 'unit' => 'ml']);
        }

        // Americano
        $americano = Product::where('slug', 'classic-americano')->first();
        if ($americano) {
            $recipe = Recipe::create(['product_id' => $americano->id, 'instructions' => 'Pull double espresso shot over hot water or ice.']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $beans->id, 'quantity' => 18.000, 'unit' => 'g']);
        }

        // Matcha Latte
        $matcha = Product::where('slug', 'ceremonial-matcha-latte')->first();
        if ($matcha) {
            $recipe = Recipe::create(['product_id' => $matcha->id, 'instructions' => 'Whisk 6g matcha with 40ml 80°C water, combine with 200ml steamed milk.']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $matchaPowder->id, 'quantity' => 6.000, 'unit' => 'g']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $milk->id, 'quantity' => 200.000, 'unit' => 'ml']);
        }

        // Croissant
        $croissant = Product::where('slug', 'butter-flaky-croissant')->first();
        if ($croissant) {
            $recipe = Recipe::create(['product_id' => $croissant->id, 'instructions' => 'Bake at 190°C for 18 minutes until puffed and golden.']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $croissants->id, 'quantity' => 1.000, 'unit' => 'pcs']);
        }
    }
}
