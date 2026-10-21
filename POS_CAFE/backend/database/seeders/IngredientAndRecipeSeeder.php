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
            'unit' => 'Bag',
            'cost_per_unit' => 14.50,
            'current_stock' => 15.000,
            'min_stock_alert' => 2.000,
            'is_active' => true,
        ]);

        $milk = Ingredient::create([
            'name' => 'Fresh Whole Dairy Milk',
            'sku' => 'ING-MILK-001',
            'unit' => 'Gallon',
            'cost_per_unit' => 3.80,
            'current_stock' => 10.000,
            'min_stock_alert' => 2.000,
            'is_active' => true,
        ]);

        $oatMilk = Ingredient::create([
            'name' => 'Barista Oat Milk',
            'sku' => 'ING-OAT-001',
            'unit' => 'Carton',
            'cost_per_unit' => 4.20,
            'current_stock' => 8.000,
            'min_stock_alert' => 2.000,
            'is_active' => true,
        ]);

        $vanilla = Ingredient::create([
            'name' => 'Natural Vanilla Syrup',
            'sku' => 'ING-SYR-VAN',
            'unit' => 'Bottle',
            'cost_per_unit' => 9.50,
            'current_stock' => 6.000,
            'min_stock_alert' => 1.000,
            'is_active' => true,
        ]);

        $caramel = Ingredient::create([
            'name' => 'Gourmet Caramel Sauce',
            'sku' => 'ING-SAU-CAR',
            'unit' => 'Bottle',
            'cost_per_unit' => 10.50,
            'current_stock' => 5.000,
            'min_stock_alert' => 1.000,
            'is_active' => true,
        ]);

        $matchaPowder = Ingredient::create([
            'name' => 'Ceremonial Matcha Powder',
            'sku' => 'ING-TEA-MAT',
            'unit' => 'Tin',
            'cost_per_unit' => 18.00,
            'current_stock' => 8.000,
            'min_stock_alert' => 2.000,
            'is_active' => true,
        ]);

        $croissants = Ingredient::create([
            'name' => 'Ready-to-bake Croissant Dough',
            'sku' => 'ING-BAK-CRO',
            'unit' => 'Box',
            'cost_per_unit' => 15.00,
            'current_stock' => 25.000,
            'min_stock_alert' => 5.000,
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
            $recipe = Recipe::create(['product_id' => $cappuccino->id, 'instructions' => 'Extract double espresso shot, steam fresh milk with velvety microfoam.']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $beans->id, 'quantity' => 0.02, 'unit' => 'Bag']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $milk->id, 'quantity' => 0.05, 'unit' => 'Gallon']);
        }

        // Vanilla Latte
        $latte = Product::where('slug', 'vanilla-bean-latte')->first();
        if ($latte) {
            $recipe = Recipe::create(['product_id' => $latte->id, 'instructions' => 'Pump vanilla syrup into cup, add double espresso shot, top with steamed milk.']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $beans->id, 'quantity' => 0.02, 'unit' => 'Bag']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $milk->id, 'quantity' => 0.06, 'unit' => 'Gallon']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $vanilla->id, 'quantity' => 0.02, 'unit' => 'Bottle']);
        }

        // Americano
        $americano = Product::where('slug', 'classic-americano')->first();
        if ($americano) {
            $recipe = Recipe::create(['product_id' => $americano->id, 'instructions' => 'Pull double espresso shot over hot water or ice.']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $beans->id, 'quantity' => 0.02, 'unit' => 'Bag']);
        }

        // Matcha Latte
        $matcha = Product::where('slug', 'ceremonial-matcha-latte')->first();
        if ($matcha) {
            $recipe = Recipe::create(['product_id' => $matcha->id, 'instructions' => 'Whisk matcha powder with water, combine with steamed milk.']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $matchaPowder->id, 'quantity' => 0.02, 'unit' => 'Tin']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $milk->id, 'quantity' => 0.05, 'unit' => 'Gallon']);
        }

        // Croissant
        $croissant = Product::where('slug', 'butter-flaky-croissant')->first();
        if ($croissant) {
            $recipe = Recipe::create(['product_id' => $croissant->id, 'instructions' => 'Bake at 190°C for 18 minutes until puffed and golden.']);
            RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $croissants->id, 'quantity' => 0.05, 'unit' => 'Box']);
        }
    }
}
