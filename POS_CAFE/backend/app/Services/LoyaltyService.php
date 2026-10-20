<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\SystemSetting;
use Exception;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Award points for a completed order. Idempotent per order.
     */
    public function awardPointsForOrder(Order $order): ?LoyaltyTransaction
    {
        if (!$order->customer_id) {
            return null;
        }

        // Check idempotency: avoid awarding twice for same order
        $existing = LoyaltyTransaction::where('order_id', $order->id)
            ->where('type', 'earned')
            ->first();

        if ($existing) {
            return $existing;
        }

        $pointsPerDollar = (float) SystemSetting::get('loyalty_points_per_dollar', 1.0);
        $pointsToAward = (int) floor((float) $order->total_amount * $pointsPerDollar);

        if ($pointsToAward <= 0) {
            return null;
        }

        return DB::transaction(function () use ($order, $pointsToAward) {
            $customer = Customer::where('id', $order->customer_id)->lockForUpdate()->firstOrFail();

            $customer->points_balance += $pointsToAward;
            $customer->total_spent = round((float) $customer->total_spent + (float) $order->total_amount, 2);
            $customer->save();

            return LoyaltyTransaction::create([
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'type' => 'earned',
                'points' => $pointsToAward,
                'balance_after' => $customer->points_balance,
                'notes' => "Earned {$pointsToAward} pts from Order #{$order->order_number}",
            ]);
        });
    }

    /**
     * Redeem points for a reward discount.
     */
    public function redeemReward(Customer $customer, LoyaltyReward $reward, ?Order $order = null): LoyaltyTransaction
    {
        return DB::transaction(function () use ($customer, $reward, $order) {
            $customer = Customer::where('id', $customer->id)->lockForUpdate()->firstOrFail();

            if ($customer->points_balance < $reward->points_required) {
                throw new Exception("Customer only has {$customer->points_balance} points, but {$reward->points_required} are required for '{$reward->name}'.");
            }

            $customer->points_balance -= $reward->points_required;
            $customer->save();

            return LoyaltyTransaction::create([
                'customer_id' => $customer->id,
                'order_id' => $order?->id,
                'type' => 'redeemed',
                'points' => -$reward->points_required,
                'balance_after' => $customer->points_balance,
                'notes' => "Redeemed '{$reward->name}' ({$reward->points_required} pts)",
            ]);
        });
    }

    /**
     * Reverse points upon order refund.
     */
    public function reversePointsForRefund(Order $order, float $refundAmount): ?LoyaltyTransaction
    {
        if (!$order->customer_id) {
            return null;
        }

        $pointsPerDollar = (float) SystemSetting::get('loyalty_points_per_dollar', 1.0);
        $pointsToDeduct = (int) ceil($refundAmount * $pointsPerDollar);

        if ($pointsToDeduct <= 0) {
            return null;
        }

        return DB::transaction(function () use ($order, $pointsToDeduct, $refundAmount) {
            $customer = Customer::where('id', $order->customer_id)->lockForUpdate()->firstOrFail();

            $customer->points_balance = max(0, $customer->points_balance - $pointsToDeduct);
            $customer->total_spent = max(0.0, round((float) $customer->total_spent - $refundAmount, 2));
            $customer->save();

            return LoyaltyTransaction::create([
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'type' => 'refund_reversed',
                'points' => -$pointsToDeduct,
                'balance_after' => $customer->points_balance,
                'notes' => "Reversed {$pointsToDeduct} pts for refunded Order #{$order->order_number}",
            ]);
        });
    }
}
