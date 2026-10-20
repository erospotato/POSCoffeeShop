<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\LoyaltyService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderAndShiftSeeder extends Seeder
{
    public function run(): void
    {
        $cashier = User::whereHas('role', function ($q) {
            $q->where('slug', 'cashier');
        })->first() ?: User::first();

        $customer1 = Customer::first();
        $customer2 = Customer::skip(1)->first();

        // 1. Open an active cashier shift
        $shift = CashierShift::create([
            'user_id' => $cashier->id,
            'opened_at' => now()->startOfDay()->addHours(8),
            'opening_cash' => 100.00,
            'expected_cash' => 100.00,
            'status' => 'open',
        ]);

        $cappuccino = Product::where('slug', 'caffe-cappuccino')->first();
        $latte = Product::where('slug', 'vanilla-bean-latte')->first();
        $croissant = Product::where('slug', 'butter-flaky-croissant')->first();
        $matcha = Product::where('slug', 'ceremonial-matcha-latte')->first();
        $extraShot = ProductAddon::where('name', 'Extra Espresso Shot')->first();

        // Order 1: Completed Cash Order
        $order1 = Order::create([
            'order_number' => 'ORD-' . now()->format('Ymd') . '-0001',
            'cashier_shift_id' => $shift->id,
            'user_id' => $cashier->id,
            'customer_id' => $customer1?->id,
            'order_type' => 'dine_in',
            'table_number' => 'Table 2',
            'status' => OrderStatus::COMPLETED,
            'payment_status' => PaymentStatus::PAID,
            'subtotal' => 7.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.70,
            'total_amount' => 7.70,
            'notes' => 'Extra hot cappuccino',
        ]);

        $item1 = OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $cappuccino->id,
            'item_name' => $cappuccino->name,
            'variant_name' => 'Regular (8 oz)',
            'unit_price' => 3.50,
            'quantity' => 2,
            'subtotal' => 7.00,
            'status' => 'completed',
        ]);

        Payment::create([
            'order_id' => $order1->id,
            'payment_method' => 'cash',
            'amount' => 7.70,
            'status' => 'successful',
            'cash_received' => 10.00,
            'cash_change' => 2.30,
            'transaction_reference' => 'PAY-CSH-0001',
        ]);

        // Order 2: Card Order
        $order2 = Order::create([
            'order_number' => 'ORD-' . now()->format('Ymd') . '-0002',
            'cashier_shift_id' => $shift->id,
            'user_id' => $cashier->id,
            'customer_id' => $customer2?->id,
            'order_type' => 'takeaway',
            'status' => OrderStatus::READY,
            'payment_status' => PaymentStatus::PAID,
            'subtotal' => 7.55,
            'discount_amount' => 0.00,
            'tax_amount' => 0.76,
            'total_amount' => 8.31,
            'notes' => 'Pack croissant separately please',
        ]);

        $item2a = OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $latte->id,
            'item_name' => $latte->name,
            'variant_name' => 'Iced Regular',
            'unit_price' => 4.20,
            'quantity' => 1,
            'subtotal' => 4.20,
            'status' => 'ready',
        ]);

        if ($extraShot) {
            OrderItemAddon::create([
                'order_item_id' => $item2a->id,
                'product_addon_id' => $extraShot->id,
                'addon_name' => $extraShot->name,
                'price' => $extraShot->price,
            ]);
            $item2a->subtotal += $extraShot->price;
            $item2a->unit_price += $extraShot->price;
            $item2a->save();
        }

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $croissant->id,
            'item_name' => $croissant->name,
            'unit_price' => 2.75,
            'quantity' => 1,
            'subtotal' => 2.75,
            'status' => 'ready',
        ]);

        Payment::create([
            'order_id' => $order2->id,
            'payment_method' => 'card',
            'amount' => 8.31,
            'status' => 'successful',
            'transaction_reference' => 'TXN-CRD-88912',
        ]);

        // Order 3: Active Kitchen Order (Preparing state - ready for Barista to test in KDS!)
        $order3 = Order::create([
            'order_number' => 'ORD-' . now()->format('Ymd') . '-0003',
            'cashier_shift_id' => $shift->id,
            'user_id' => $cashier->id,
            'order_type' => 'dine_in',
            'table_number' => 'Table 5',
            'status' => OrderStatus::PREPARING,
            'payment_status' => PaymentStatus::PAID,
            'subtotal' => 4.50,
            'discount_amount' => 0.00,
            'tax_amount' => 0.45,
            'total_amount' => 4.95,
            'notes' => 'Oat milk if possible, less sweet',
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'product_id' => $matcha->id,
            'item_name' => $matcha->name,
            'variant_name' => 'Iced',
            'unit_price' => 4.50,
            'quantity' => 1,
            'subtotal' => 4.50,
            'preparation_notes' => 'Oat milk, light ice',
            'status' => 'preparing',
        ]);

        Payment::create([
            'order_id' => $order3->id,
            'payment_method' => 'qr_code',
            'amount' => 4.95,
            'status' => 'successful',
            'transaction_reference' => 'QR-SIM-991244',
        ]);

        // Update shift expected cash
        $shift->expected_cash = 100.00 + 7.70; // Cash payment added
        $shift->save();
    }
}
