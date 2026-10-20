<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\Recipe;
use App\Models\StockAdjustment;
use App\Models\StockReceipt;
use App\Models\SystemSetting;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Deduct stock for all items in an order based on recipes.
     * Guaranteed idempotent using idempotency_key.
     */
    public function deductForOrder(Order $order, User $user): void
    {
        $idempotencyKeyPrefix = "order_deduct_{$order->id}_";

        // Check if already deducted
        $alreadyDeducted = InventoryMovement::where('movement_type', 'order_deduction')
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->exists();

        if ($alreadyDeducted) {
            return; // Idempotent guard
        }

        $allowNegative = (bool) SystemSetting::get('allow_negative_inventory', false);

        foreach ($order->items as $orderItem) {
            // Find recipe for specific variant or default product recipe
            $recipe = Recipe::with('items.ingredient')
                ->where('product_id', $orderItem->product_id)
                ->where('product_variant_id', $orderItem->product_variant_id)
                ->first();

            if (!$recipe) {
                $recipe = Recipe::with('items.ingredient')
                    ->where('product_id', $orderItem->product_id)
                    ->whereNull('product_variant_id')
                    ->first();
            }

            if (!$recipe) {
                continue; // No recipe configured for this item
            }

            foreach ($recipe->items as $recipeItem) {
                // Row lock on ingredient to prevent concurrent race condition
                $ingredient = Ingredient::where('id', $recipeItem->ingredient_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $neededQuantity = (float) $recipeItem->quantity * (int) $orderItem->quantity;
                $convertedNeeded = $this->convertUnit($neededQuantity, $recipeItem->unit, $ingredient->unit);

                $previousStock = (float) $ingredient->current_stock;
                $newStock = round($previousStock - $convertedNeeded, 3);

                if ($newStock < 0 && !$allowNegative) {
                    throw new Exception("Insufficient stock for ingredient '{$ingredient->name}'. Available: {$previousStock} {$ingredient->unit}, Required: {$convertedNeeded} {$ingredient->unit}.");
                }

                $ingredient->current_stock = $newStock;
                $ingredient->save();

                $uniqueKey = $idempotencyKeyPrefix . "item_{$orderItem->id}_ing_{$ingredient->id}";

                InventoryMovement::create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => $user->id,
                    'movement_type' => 'order_deduction',
                    'quantity' => -$convertedNeeded,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'unit_cost' => $ingredient->cost_per_unit,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'reason' => "Order #{$order->order_number} ({$orderItem->item_name} x{$orderItem->quantity})",
                    'idempotency_key' => $uniqueKey,
                ]);
            }
        }
    }

    /**
     * Adjust stock manually (correction, spoilage, wastage).
     */
    public function adjustStock(Ingredient $ingredient, float $quantityAdjusted, string $type, string $reason, User $user): StockAdjustment
    {
        return DB::transaction(function () use ($ingredient, $quantityAdjusted, $type, $reason, $user) {
            $ingredient = Ingredient::where('id', $ingredient->id)->lockForUpdate()->firstOrFail();

            $previousStock = (float) $ingredient->current_stock;
            $newStock = round($previousStock + $quantityAdjusted, 3);

            if ($newStock < 0) {
                throw new Exception("Adjusted stock cannot result in negative quantity ({$newStock} {$ingredient->unit}).");
            }

            $ingredient->current_stock = $newStock;
            $ingredient->save();

            $costImpact = round(abs($quantityAdjusted) * (float) $ingredient->cost_per_unit, 2);

            $adjustment = StockAdjustment::create([
                'ingredient_id' => $ingredient->id,
                'user_id' => $user->id,
                'type' => $type,
                'quantity_adjusted' => $quantityAdjusted,
                'cost_impact' => $costImpact,
                'reason' => $reason,
            ]);

            $movementType = match ($type) {
                'spoilage_waste', 'damage' => 'waste',
                default => ($quantityAdjusted >= 0 ? 'adjustment_increase' : 'adjustment_decrease'),
            };

            InventoryMovement::create([
                'ingredient_id' => $ingredient->id,
                'user_id' => $user->id,
                'movement_type' => $movementType,
                'quantity' => $quantityAdjusted,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'unit_cost' => $ingredient->cost_per_unit,
                'reference_type' => 'stock_adjustment',
                'reference_id' => $adjustment->id,
                'reason' => "Adjustment: {$reason} ({$type})",
            ]);

            AuditService::log('inventory_adjusted', $adjustment, null, [
                'ingredient' => $ingredient->name,
                'quantity' => $quantityAdjusted,
                'type' => $type,
                'cost_impact' => $costImpact,
            ]);

            return $adjustment;
        });
    }

    /**
     * Receive stock from a Purchase Order and increment inventory.
     */
    public function receivePurchaseOrder(PurchaseOrder $po, array $receivedQuantities, User $user, ?string $notes = null): StockReceipt
    {
        return DB::transaction(function () use ($po, $receivedQuantities, $user, $notes) {
            $po = PurchaseOrder::where('id', $po->id)->lockForUpdate()->firstOrFail();

            if ($po->isReceived()) {
                throw new Exception("Purchase Order #{$po->po_number} has already been marked as fully received.");
            }

            $receipt = StockReceipt::create([
                'purchase_order_id' => $po->id,
                'received_by' => $user->id,
                'received_at' => now(),
                'notes' => $notes,
            ]);

            $allCompleted = true;

            foreach ($po->items as $poItem) {
                $qtyReceivedThisTime = (float) ($receivedQuantities[$poItem->id] ?? 0);

                if ($qtyReceivedThisTime <= 0) {
                    if ((float) $poItem->quantity_received < (float) $poItem->quantity_ordered) {
                        $allCompleted = false;
                    }
                    continue;
                }

                $ingredient = Ingredient::where('id', $poItem->ingredient_id)->lockForUpdate()->firstOrFail();

                $previousStock = (float) $ingredient->current_stock;
                $newStock = round($previousStock + $qtyReceivedThisTime, 3);

                $ingredient->current_stock = $newStock;

                // Update weighted average or latest cost per unit
                if ((float) $poItem->unit_price > 0) {
                    $ingredient->cost_per_unit = $poItem->unit_price;
                }
                $ingredient->save();

                $poItem->quantity_received = round((float) $poItem->quantity_received + $qtyReceivedThisTime, 3);
                $poItem->save();

                if ((float) $poItem->quantity_received < (float) $poItem->quantity_ordered) {
                    $allCompleted = false;
                }

                InventoryMovement::create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => $user->id,
                    'movement_type' => 'purchase_receipt',
                    'quantity' => $qtyReceivedThisTime,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'unit_cost' => $poItem->unit_price,
                    'reference_type' => 'purchase_order',
                    'reference_id' => $po->id,
                    'reason' => "PO #{$po->po_number} receipt",
                ]);
            }

            $po->status = $allCompleted ? 'received' : 'ordered';
            if ($allCompleted) {
                $po->received_at = now();
            }
            $po->save();

            AuditService::log('po_received', $receipt, null, [
                'po_number' => $po->po_number,
                'received_by' => $user->name,
            ]);

            return $receipt;
        });
    }

    /**
     * Restore stock upon an authorized refund.
     */
    public function restockForRefund(Order $order, User $user, string $reason): void
    {
        foreach ($order->items as $orderItem) {
            $recipe = Recipe::with('items.ingredient')
                ->where('product_id', $orderItem->product_id)
                ->where('product_variant_id', $orderItem->product_variant_id)
                ->first() ?: Recipe::with('items.ingredient')
                ->where('product_id', $orderItem->product_id)
                ->whereNull('product_variant_id')
                ->first();

            if (!$recipe) {
                continue;
            }

            foreach ($recipe->items as $recipeItem) {
                $ingredient = Ingredient::where('id', $recipeItem->ingredient_id)->lockForUpdate()->first();
                if (!$ingredient) continue;

                $returnQty = (float) $recipeItem->quantity * (int) $orderItem->quantity;
                $converted = $this->convertUnit($returnQty, $recipeItem->unit, $ingredient->unit);

                $previousStock = (float) $ingredient->current_stock;
                $newStock = round($previousStock + $converted, 3);

                $ingredient->current_stock = $newStock;
                $ingredient->save();

                InventoryMovement::create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => $user->id,
                    'movement_type' => 'refund_restock',
                    'quantity' => $converted,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'unit_cost' => $ingredient->cost_per_unit,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'reason' => "Refund restock for Order #{$order->order_number}: {$reason}",
                ]);
            }
        }
    }

    /**
     * Unit conversion helper (e.g. Gallon, Bottle, Bag, Tin, Box, pcs, kg, g, L, ml).
     */
    public function convertUnit(float $amount, string $fromUnit, string $toUnit): float
    {
        $from = strtolower(trim($fromUnit));
        $to = strtolower(trim($toUnit));

        if ($from === $to) {
            return $amount;
        }

        // Weight conversions (base: grams)
        $weights = [
            'g' => 1.0,
            'gram' => 1.0,
            'grams' => 1.0,
            'kg' => 1000.0,
            'kilogram' => 1000.0,
            'bag' => 1000.0, // standard 1kg coffee beans bag
            'bags' => 1000.0,
            'tin' => 250.0,  // 250g tea/matcha tin
            'tins' => 250.0,
        ];
        if (isset($weights[$from]) && isset($weights[$to])) {
            $baseGrams = $amount * $weights[$from];
            return $baseGrams / $weights[$to];
        }

        // Volume conversions (base: milliliters)
        $volumes = [
            'ml' => 1.0,
            'milliliter' => 1.0,
            'milliliters' => 1.0,
            'l' => 1000.0,
            'liter' => 1000.0,
            'liters' => 1000.0,
            'gallon' => 3785.41, // 1 US gallon = 3785.41 ml
            'gallons' => 3785.41,
            'bottle' => 750.0,   // standard 750ml syrup bottle
            'bottles' => 750.0,
            'carton' => 1000.0,  // 1 carton = 1000 ml
            'cartons' => 1000.0,
        ];
        if (isset($volumes[$from]) && isset($volumes[$to])) {
            $baseMl = $amount * $volumes[$from];
            return $baseMl / $volumes[$to];
        }

        // Discrete items / pastry boxes
        $pieces = [
            'pcs' => 1.0,
            'piece' => 1.0,
            'pieces' => 1.0,
            'unit' => 1.0,
            'units' => 1.0,
            'box' => 20.0,
            'boxes' => 20.0,
        ];
        if (isset($pieces[$from]) && isset($pieces[$to])) {
            $basePcs = $amount * $pieces[$from];
            return $basePcs / $pieces[$to];
        }

        return $amount;
    }
}
