<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\StockAdjustment;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $adjustments = StockAdjustment::with(['ingredient', 'user'])->latest()->paginate(15);
        $ingredients = Ingredient::where('is_active', true)->orderBy('name')->get();

        return view('inventory.adjustments.index', compact('adjustments', 'ingredients'));
    }

    public function store(StockAdjustmentRequest $request)
    {
        $ingredient = Ingredient::findOrFail($request->input('ingredient_id'));

        try {
            $adjustment = $this->inventoryService->adjustStock(
                $ingredient,
                (float) $request->input('quantity_adjusted'),
                $request->input('type'),
                $request->input('reason'),
                $request->user()
            );

            return back()->with('success', "Stock adjustment of {$adjustment->quantity_adjusted} {$ingredient->unit} recorded.");
        } catch (Exception $e) {
            return back()->withErrors(['adjustment_error' => $e->getMessage()]);
        }
    }

    public function ledger(Request $request)
    {
        $query = InventoryMovement::with(['ingredient', 'user'])->latest();

        if ($request->filled('ingredient_id')) {
            $query->where('ingredient_id', $request->input('ingredient_id'));
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->input('movement_type'));
        }

        $movements = $query->paginate(20);
        $ingredients = Ingredient::orderBy('name')->get();

        return view('inventory.adjustments.ledger', compact('movements', 'ingredients'));
    }
}
