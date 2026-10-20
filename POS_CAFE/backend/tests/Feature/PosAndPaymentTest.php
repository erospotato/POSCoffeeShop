<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\PosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected User $manager;
    protected CashierShift $shift;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::where('email', 'cashier@cafe.com')->firstOrFail();
        $this->manager = User::where('email', 'manager@cafe.com')->firstOrFail();

        // Ensure active shift exists for cashier
        $this->shift = CashierShift::firstOrCreate(
            ['user_id' => $this->cashier->id, 'status' => 'open'],
            [
                'opening_balance' => 100.00,
                'expected_cash' => 100.00,
                'opened_at' => now(),
            ]
        );

        $this->product = Product::with(['variants', 'addons'])->where('is_available', true)->firstOrFail();
    }

    public function test_pos_service_calculates_server_side_pricing_accurately(): void
    {
        $posService = app(PosService::class);

        $variant = $this->product->variants->first();
        $addon = $this->product->addons->first();

        $items = [
            [
                'product_id' => $this->product->id,
                'variant_id' => $variant?->id,
                'addon_ids' => $addon ? [$addon->id] : [],
                'quantity' => 2,
            ]
        ];

        $calculation = $posService->calculatePricing($items);

        $expectedUnitPrice = (float) $this->product->base_price
            + ($variant ? (float) $variant->price_modifier : 0.0)
            + ($addon ? (float) $addon->price : 0.0);

        $expectedSubtotal = round($expectedUnitPrice * 2, 2);

        $this->assertEquals($expectedSubtotal, $calculation['subtotal']);
        $this->assertGreaterThan(0, $calculation['total_amount']);
    }

    public function test_cashier_can_checkout_order_with_cash(): void
    {
        $posService = app(PosService::class);
        $pricing = $posService->calculatePricing([
            ['product_id' => $this->product->id, 'quantity' => 1]
        ]);

        $payload = [
            'order_type' => 'dine_in',
            'table_number' => 'T-04',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ]
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => $pricing['total_amount'],
                    'cash_received' => $pricing['total_amount'] + 10.00,
                ]
            ]
        ];

        $response = $this->actingAs($this->cashier)->postJson(route('pos.checkout'), $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'order',
            'is_fully_paid',
            'receipt_url',
        ]);

        $orderId = $response->json('order.id');
        $order = Order::findOrFail($orderId);
        $this->assertEquals('paid', $order->payment_status->value);
        $this->assertEquals('preparing', $order->status->value); // Sent to kitchen
    }

    public function test_split_payment_succeeds_when_full_amount_is_covered(): void
    {
        $posService = app(PosService::class);
        $calc = $posService->calculatePricing([
            ['product_id' => $this->product->id, 'quantity' => 2]
        ]);

        $half = round($calc['total_amount'] / 2, 2);
        $remainder = round($calc['total_amount'] - $half, 2);

        $payload = [
            'order_type' => 'takeaway',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2]
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => $half],
                ['method' => 'card', 'amount' => $remainder, 'reference' => 'AUTH-89211'],
            ]
        ];

        $response = $this->actingAs($this->cashier)->postJson(route('pos.checkout'), $payload);
        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        $order = Order::findOrFail($response->json('order.id'));
        $this->assertCount(2, $order->payments);
    }

    public function test_manager_can_refund_order_partially_or_fully(): void
    {
        // 1. Create a paid order
        $posService = app(PosService::class);
        $pricing = $posService->calculatePricing([
            ['product_id' => $this->product->id, 'quantity' => 1]
        ]);

        $payload = [
            'order_type' => 'dine_in',
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'payments' => [['method' => 'cash', 'amount' => $pricing['total_amount']]]
        ];

        $checkout = $this->actingAs($this->cashier)->postJson(route('pos.checkout'), $payload);
        $orderId = $checkout->json('order.id');
        $order = Order::findOrFail($orderId);

        // 2. Manager processes refund
        $refundAmount = (float) $order->total_amount;
        $response = $this->actingAs($this->manager)->post(route('pos.orders.refund', $order->id), [
            'amount' => $refundAmount,
            'reason' => 'Customer changed mind before preparation',
            'restock_inventory' => true,
        ]);

        $response->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals('refunded', $order->payment_status->value);
    }

    public function test_refund_cannot_exceed_total_paid_order_amount(): void
    {
        $posService = app(PosService::class);
        $pricing = $posService->calculatePricing([
            ['product_id' => $this->product->id, 'quantity' => 1]
        ]);

        $payload = [
            'order_type' => 'dine_in',
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'payments' => [['method' => 'cash', 'amount' => $pricing['total_amount']]]
        ];

        $checkout = $this->actingAs($this->cashier)->postJson(route('pos.checkout'), $payload);
        $order = Order::findOrFail($checkout->json('order.id'));

        $excessiveAmount = (float) $order->total_amount + 50.00;

        $response = $this->actingAs($this->manager)->post(route('pos.orders.refund', $order->id), [
            'amount' => $excessiveAmount,
            'reason' => 'Fraudulent refund test',
        ]);

        $response->assertSessionHasErrors(['refund_error']);
    }
}
