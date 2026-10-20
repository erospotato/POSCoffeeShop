<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\PurchaseOrderRequest;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\AuditService;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $purchaseOrders = $query->paginate(15);
        $suppliers = Supplier::where('is_active', true)->get();

        return view('suppliers.purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $ingredients = Ingredient::where('is_active', true)->get();

        return view('suppliers.purchase-orders.create', compact('suppliers', 'ingredients'));
    }

    public function store(PurchaseOrderRequest $request)
    {
        $data = $request->validated();
        $poNumber = 'PO-' . now()->format('Y') . '-' . sprintf('%04d', PurchaseOrder::count() + 1);

        $totalCost = 0.0;
        foreach ($data['items'] as $item) {
            $totalCost += (float) $item['quantity_ordered'] * (float) $item['unit_price'];
        }

        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $data['supplier_id'],
            'created_by' => $request->user()->id,
            'status' => 'ordered',
            'total_cost' => round($totalCost, 2),
            'ordered_at' => $data['ordered_at'],
            'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $subtotal = round((float) $item['quantity_ordered'] * (float) $item['unit_price'], 2);
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'ingredient_id' => $item['ingredient_id'],
                'quantity_ordered' => (float) $item['quantity_ordered'],
                'quantity_received' => 0,
                'unit_price' => (float) $item['unit_price'],
                'subtotal' => $subtotal,
            ]);
        }

        AuditService::log('po_created', $po, null, ['po_number' => $po->po_number, 'total_cost' => $po->total_cost]);

        return redirect()->route('purchase-orders.show', $po->id)->with('success', "Purchase Order {$po->po_number} created.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'creator', 'items.ingredient', 'receipts.receivedByUser']);
        return view('suppliers.purchase-orders.show', compact('purchaseOrder'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'quantities' => ['required', 'array'],
            'quantities.*' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->inventoryService->receivePurchaseOrder(
                $purchaseOrder,
                $request->input('quantities'),
                $request->user(),
                $request->input('notes')
            );

            return back()->with('success', "Stock delivery received and inventory ledger updated.");
        } catch (Exception $e) {
            return back()->withErrors(['receive_error' => $e->getMessage()]);
        }
    }
}
