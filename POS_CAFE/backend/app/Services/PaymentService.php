<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected LoyaltyService $loyaltyService
    ) {}

    /**
     * Process a single or partial payment for an order.
     * Payments array format:
     * [
     *   [
     *      'method' => 'cash',
     *      'amount' => 10.00,
     *      'cash_received' => 15.00,
     *      'reference' => '...'
     *   ], ...
     * ]
     */
    public function processPayments(Order $order, array $paymentsList, User $processedBy): array
    {
        return DB::transaction(function () use ($order, $paymentsList, $processedBy) {
            // Lock order for update to prevent concurrent race condition
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($order->isPaid()) {
                throw new Exception("Order #{$order->order_number} is already fully paid.");
            }

            if ($order->isCancelled()) {
                throw new Exception("Cannot process payment for a cancelled order.");
            }

            $alreadyPaid = (float) $order->successfulPayments()->sum('amount');
            $remainingDue = max(0.0, round((float) $order->total_amount - $alreadyPaid, 2));

            $totalPaymentAttempted = 0.0;
            $createdPayments = [];

            foreach ($paymentsList as $paymentData) {
                $method = $paymentData['method'];
                $amount = round((float) $paymentData['amount'], 2);

                if ($amount <= 0) {
                    throw new Exception("Payment amount must be greater than zero.");
                }

                $cashReceived = null;
                $cashChange = null;

                if ($method === 'cash') {
                    $cashReceived = round((float) ($paymentData['cash_received'] ?? $amount), 2);
                    if ($cashReceived < $amount) {
                        throw new Exception("Cash received ($" . number_format($cashReceived, 2) . ") is less than required amount ($" . number_format($amount, 2) . ").");
                    }
                    $cashChange = round($cashReceived - $amount, 2);
                }

                $reference = !empty($paymentData['reference']) ? $paymentData['reference'] : ('PAY-' . strtoupper(Str::random(10)));
                while (Payment::where('transaction_reference', $reference)->exists()) {
                    $reference .= '-' . strtoupper(Str::random(6));
                }

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $method,
                    'amount' => $amount,
                    'status' => 'successful',
                    'cash_received' => $cashReceived,
                    'cash_change' => $cashChange,
                    'transaction_reference' => $reference,
                    'payment_details' => [
                        'processed_by_user_id' => $processedBy->id,
                        'processed_at' => now()->toIso8601String(),
                        'gateway' => 'simulation_standard',
                    ],
                ]);

                $totalPaymentAttempted += $amount;
                $createdPayments[] = $payment;
            }

            $newTotalPaid = round($alreadyPaid + $totalPaymentAttempted, 2);

            if ($newTotalPaid >= (float) $order->total_amount) {
                $order->payment_status = PaymentStatus::PAID;
                $order->status = OrderStatus::PREPARING; // Automatically moves to kitchen queue
                $order->save();

                // Trigger atomic inventory deduction exactly once
                $this->inventoryService->deductForOrder($order, $processedBy);

                // Trigger customer loyalty points earning if applicable
                if ($order->customer_id) {
                    $this->loyaltyService->awardPointsForOrder($order);
                }
            } else {
                $order->payment_status = PaymentStatus::PARTIALLY_PAID;
                $order->save();
            }

            AuditService::log('payment_processed', $order, null, [
                'order_id' => $order->id,
                'payments_count' => count($createdPayments),
                'total_paid' => $newTotalPaid,
                'status' => $order->payment_status->value,
            ]);

            return [
                'order' => $order->fresh(['payments', 'items']),
                'payments' => $createdPayments,
                'remaining_due' => max(0.0, round((float) $order->total_amount - $newTotalPaid, 2)),
                'is_fully_paid' => $order->payment_status === PaymentStatus::PAID,
            ];
        });
    }

    /**
     * Process an authorized refund.
     */
    public function processRefund(Order $order, float $amount, string $reason, User $manager, bool $restock = false): Refund
    {
        return DB::transaction(function () use ($order, $amount, $reason, $manager, $restock) {
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            $refundable = $order->remainingRefundable();
            $amount = round($amount, 2);

            if ($amount <= 0) {
                throw new Exception("Refund amount must be greater than zero.");
            }

            if ($amount > $refundable) {
                throw new Exception("Refund amount ($" . number_format($amount, 2) . ") exceeds refundable balance ($" . number_format($refundable, 2) . ").");
            }

            // Find payment to link
            $primaryPayment = $order->successfulPayments()->latest()->first();

            $refund = Refund::create([
                'order_id' => $order->id,
                'payment_id' => $primaryPayment?->id,
                'user_id' => $manager->id,
                'amount' => $amount,
                'reason' => $reason,
                'restock_inventory' => $restock,
            ]);

            // Update order status if fully refunded
            $newTotalRefunded = $order->totalRefunded() + $amount;
            if ($newTotalRefunded >= (float) $order->total_amount) {
                $order->payment_status = PaymentStatus::REFUNDED;
                $order->status = OrderStatus::CANCELLED;
                $order->cancelled_at = now();
                $order->cancellation_reason = "Refunded: " . $reason;
                $order->cancelled_by = $manager->id;
                $order->save();
            }

            // Reverse loyalty points proportionately if customer order
            if ($order->customer_id) {
                $this->loyaltyService->reversePointsForRefund($order, $amount);
            }

            // Restock inventory if requested and policy allows
            if ($restock) {
                $this->inventoryService->restockForRefund($order, $manager, $reason);
            }

            AuditService::log('order_refunded', $refund, null, [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'amount' => $amount,
                'authorized_by' => $manager->name,
                'reason' => $reason,
            ]);

            return $refund;
        });
    }
}
