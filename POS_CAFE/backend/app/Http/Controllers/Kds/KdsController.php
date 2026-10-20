<?php

namespace App\Http\Controllers\Kds;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KdsController extends Controller
{
    public function index()
    {
        $orders = $this->getActiveKitchenOrders();
        return view('kds.index', compact('orders'));
    }

    /**
     * Polling endpoint for KDS live updates.
     */
    public function pollOrders(): JsonResponse
    {
        $orders = $this->getActiveKitchenOrders();

        $formatted = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_type' => ucfirst(str_replace('_', ' ', $order->order_type)),
                'table_number' => $order->table_number,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'status_badge' => $order->status->badgeClass(),
                'elapsed_minutes' => (int) $order->created_at->diffInMinutes(now()),
                'created_at_time' => $order->created_at->format('H:i'),
                'notes' => $order->notes,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->item_name,
                        'variant' => $item->variant_name,
                        'quantity' => $item->quantity,
                        'notes' => $item->preparation_notes,
                        'status' => $item->status,
                        'addons' => $item->addons->map(fn($a) => $a->addon_name)->toArray(),
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'orders' => $formatted,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Update order kitchen status: preparing, ready, completed.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:preparing,ready,completed'],
        ]);

        $newStatus = OrderStatus::from($request->input('status'));
        $oldStatus = $order->status;

        $order->status = $newStatus;
        $order->save();

        // Update item statuses to match
        $order->items()->update(['status' => $request->input('status')]);

        AuditService::log('kds_status_updated', $order, ['status' => $oldStatus->value], ['status' => $newStatus->value]);

        return response()->json([
            'success' => true,
            'message' => "Order #{$order->order_number} marked as {$newStatus->label()}.",
            'status' => $newStatus->value,
        ]);
    }

    protected function getActiveKitchenOrders()
    {
        return Order::with(['items.addons', 'customer'])
            ->whereIn('status', [OrderStatus::CONFIRMED, OrderStatus::PREPARING, OrderStatus::READY])
            ->whereIn('payment_status', ['paid', 'partially_paid'])
            ->orderByRaw("CASE status WHEN 'confirmed' THEN 1 WHEN 'preparing' THEN 2 WHEN 'ready' THEN 3 ELSE 4 END")
            ->orderBy('created_at')
            ->get();
    }
}
