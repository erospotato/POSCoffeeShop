<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Services\AuditService;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index()
    {
        $recipes = Recipe::with(['product.variants', 'variant', 'items.ingredient'])->paginate(15);
        return view('inventory.recipes.index', compact('recipes'));
    }

    public function create(Request $request)
    {
        $products = Product::with('variants')->where('is_available', true)->get();
        $ingredients = Ingredient::where('is_active', true)->get();
        $selectedProductId = $request->input('product_id');

        return view('inventory.recipes.create', compact('products', 'ingredients', 'selectedProductId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit' => ['required', 'string', 'max:20'],
        ]);

        $recipe = Recipe::updateOrCreate(
            [
                'product_id' => $request->input('product_id'),
                'product_variant_id' => $request->input('product_variant_id'),
            ],
            [
                'instructions' => $request->input('instructions'),
            ]
        );

        // Sync items
        $recipe->items()->delete();

        foreach ($request->input('items') as $item) {
            RecipeItem::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $item['ingredient_id'],
                'quantity' => (float) $item['quantity'],
                'unit' => $item['unit'],
            ]);
        }

        AuditService::log('recipe_saved', $recipe);

        return redirect()->route('recipes.index')->with('success', 'Recipe bill of materials saved successfully.');
    }

    public function edit(Recipe $recipe)
    {
        $recipe->load(['product.variants', 'variant', 'items.ingredient']);
        $products = Product::with('variants')->where('is_available', true)->get();
        $ingredients = Ingredient::where('is_active', true)->get();

        return view('inventory.recipes.edit', compact('recipe', 'products', 'ingredients'));
    }

    public function update(Request $request, Recipe $recipe)
    {
        $request->validate([
            'instructions' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit' => ['required', 'string', 'max:20'],
        ]);

        $recipe->update(['instructions' => $request->input('instructions')]);
        $recipe->items()->delete();

        foreach ($request->input('items') as $item) {
            RecipeItem::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $item['ingredient_id'],
                'quantity' => (float) $item['quantity'],
                'unit' => $item['unit'],
            ]);
        }

        AuditService::log('recipe_updated', $recipe);

        return redirect()->route('recipes.index')->with('success', 'Recipe updated successfully.');
    }

    public function destroy(Recipe $recipe)
    {
        $recipe->items()->delete();
        $recipe->delete();

        AuditService::log('recipe_deleted');

        return redirect()->route('recipes.index')->with('success', 'Recipe deleted.');
    }
}
