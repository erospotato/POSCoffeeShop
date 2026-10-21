<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\IngredientRequest;
use App\Models\Ingredient;
use App\Services\AuditService;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::withCount('recipeItems');

        if ($request->filled('low_stock')) {
            $query->whereColumn('current_stock', '<=', 'min_stock_alert');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $ingredients = $query->orderBy('name')->paginate(15);
        $totalStockValue = Ingredient::where('is_active', true)->selectRaw('SUM(current_stock * cost_per_unit) as val')->value('val') ?? 0;
        $lowStockCount = Ingredient::where('is_active', true)->whereColumn('current_stock', '<=', 'min_stock_alert')->count();

        return view('inventory.ingredients.index', compact('ingredients', 'totalStockValue', 'lowStockCount'));
    }

    public function create()
    {
        return view('inventory.ingredients.create');
    }

    public function store(IngredientRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['current_stock'] = (float) ($data['current_stock'] ?? 0);
        $data['cost_per_unit'] = (float) ($data['cost_per_unit'] ?? 0);
        $data['min_stock_alert'] = (float) ($data['min_stock_alert'] ?? 0);

        $ingredient = Ingredient::create($data);
        AuditService::log('ingredient_created', $ingredient);

        return redirect()->route('ingredients.index')->with('success', "Ingredient '{$ingredient->name}' created.");
    }

    public function edit(Ingredient $ingredient)
    {
        return view('inventory.ingredients.edit', compact('ingredient'));
    }

    public function update(IngredientRequest $request, Ingredient $ingredient)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', false);
        $data['cost_per_unit'] = isset($data['cost_per_unit']) && $data['cost_per_unit'] !== '' ? (float) $data['cost_per_unit'] : (float) $ingredient->cost_per_unit;
        $data['min_stock_alert'] = (float) ($data['min_stock_alert'] ?? 0);

        $ingredient->update($data);
        AuditService::log('ingredient_updated', $ingredient);

        return redirect()->route('ingredients.index')->with('success', "Ingredient '{$ingredient->name}' updated.");
    }

    public function destroy(Ingredient $ingredient)
    {
        if ($ingredient->recipeItems()->exists()) {
            return back()->withErrors(['ingredient_error' => 'Cannot delete ingredient that is used in recipes. Remove it from recipes first.']);
        }

        $name = $ingredient->name;
        $ingredient->delete();
        AuditService::log('ingredient_deleted', null, ['name' => $name]);

        return redirect()->route('ingredients.index')->with('success', "Ingredient '{$name}' deleted.");
    }
}
