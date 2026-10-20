<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\SystemSetting;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosService
{
    /**
     * Calculate order totals on server.
     * Items format:
     * [
     *   [
     *      'product_id' => 1,
     *      'variant_id' => 2, // nullable
     *      'addon_ids' => [1, 3], // array of IDs
     *      'quantity' => 2,
     *      'notes' => 'Less sugar'
     *   ]
     * ]
     */
    public function calculatePricing(array $items, ?int $promotionId = null, ?int $customerId = null): array
    {
        $calculatedItems = [];
        $subtotal = 0.0;

        foreach ($items as $itemData) {
            $productId = $itemData['product_id'];
            $quantity = max(1, (int) ($itemData['quantity'] ?? 1));

            $product = Product::with(['category'])->findOrFail($productId);

            if (!$product->is_available) {
                throw new Exception("Product '{$product->name}' is currently unavailable.");
            }

            $unitPrice = (float) $product->base_price;
            $variant = null;

            if (!empty($itemData['variant_id'])) {
                $variant = ProductVariant::where('product_id', $product->id)
                    ->where('id', $itemData['variant_id'])
                    ->where('is_active', true)
                    ->firstOrFail();

                // price modifier can be delta or variant price
                $unitPrice += (float) $variant->price_modifier;
            }

            $addonsTotal = 0.0;
            $itemAddons = [];

            if (!empty($itemData['addon_ids']) && is_array($itemData['addon_ids'])) {
                $addons = ProductAddon::whereIn('id', $itemData['addon_ids'])
                    ->where('is_active', true)
                    ->get();

                foreach ($addons as $addon) {
                    $addonsTotal += (float) $addon->price;
                    $itemAddons[] = [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'price' => (float) $addon->price,
                    ];
                }
            }

            $totalPerItem = $unitPrice + $addonsTotal;
            $lineSubtotal = round($totalPerItem * $quantity, 2);
            $subtotal += $lineSubtotal;

            $calculatedItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'variant_id' => $variant?->id,
                'variant_name' => $variant?->name,
                'unit_price' => round($totalPerItem, 2),
                'quantity' => $quantity,
                'subtotal' => $lineSubtotal,
                'notes' => $itemData['notes'] ?? null,
                'addons' => $itemAddons,
            ];
        }

        $subtotal = round($subtotal, 2);
        $discountAmount = 0.0;
        $appliedPromotion = null;

        if ($promotionId) {
            $promotion = Promotion::find($promotionId);
            if ($promotion && $promotion->isValidNow()) {
                if ($subtotal >= (float) $promotion->min_spend) {
                    if ($promotion->type === 'percentage') {
                        $discountAmount = round(($subtotal * (float) $promotion->value) / 100, 2);
                    } else {
                        $discountAmount = min($subtotal, round((float) $promotion->value, 2));
                    }
                    $appliedPromotion = $promotion;
                }
            }
        }

        $taxRate = (float) SystemSetting::get('tax_rate', 10.0); // e.g., 10%
        $taxMode = SystemSetting::get('tax_mode', 'exclusive'); // inclusive or exclusive

        $afterDiscount = max(0.0, $subtotal - $discountAmount);

        if ($taxMode === 'inclusive') {
            // Tax is included inside the price
            $taxAmount = round($afterDiscount - ($afterDiscount / (1 + ($taxRate / 100))), 2);
            $totalAmount = $afterDiscount;
        } else {
            // Tax is added on top
            $taxAmount = round(($afterDiscount * $taxRate) / 100, 2);
            $totalAmount = round($afterDiscount + $taxAmount, 2);
        }

        return [
            'items' => $calculatedItems,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'tax_mode' => $taxMode,
            'total_amount' => $totalAmount,
            'applied_promotion' => $appliedPromotion,
        ];
    }

    /**
     * Create order from validated data inside a transaction.
     */
    public function createOrder(User $cashier, array $data): Order
    {
        return DB::transaction(function () use ($cashier, $data) {
            $pricing = $this->calculatePricing(
                $data['items'],
                $data['promotion_id'] ?? null,
                $data['customer_id'] ?? null
            );

            $orderNumber = $this->generateOrderNumber();
            $shift = $cashier->activeShift();

            $order = Order::create([
                'order_number' => $orderNumber,
                'cashier_shift_id' => $shift?->id,
                'user_id' => $cashier->id,
                'customer_id' => $data['customer_id'] ?? null,
                'order_type' => $data['order_type'] ?? 'dine_in',
                'table_number' => $data['table_number'] ?? null,
                'status' => OrderStatus::CONFIRMED,
                'payment_status' => PaymentStatus::UNPAID,
                'subtotal' => $pricing['subtotal'],
                'discount_amount' => $pricing['discount_amount'],
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total_amount'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($pricing['items'] as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'item_name' => $item['product_name'],
                    'variant_name' => $item['variant_name'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                    'preparation_notes' => $item['notes'],
                    'status' => 'pending',
                ]);

                foreach ($item['addons'] as $addon) {
                    OrderItemAddon::create([
                        'order_item_id' => $orderItem->id,
                        'product_addon_id' => $addon['id'],
                        'addon_name' => $addon['name'],
                        'price' => $addon['price'],
                    ]);
                }
            }

            if (!empty($pricing['applied_promotion'])) {
                $pricing['applied_promotion']->increment('used_count');
            }

            AuditService::log('order_created', $order, null, [
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
            ]);

            return $order->load(['items.addons', 'customer', 'cashier']);
        });
    }

    public function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = SystemSetting::get('order_prefix', 'ORD');

        // Lock latest order for today to prevent collision
        $latestOrder = Order::whereDate('created_at', now()->toDateString())
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $sequence = 1;
        if ($latestOrder && preg_match('/-(\d+)$/', $latestOrder->order_number, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}
