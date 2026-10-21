<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\InventoryService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $cashier;
    protected Ingredient $coffeeBeans;
    protected Ingredient $milk;
    protected Product $cappuccino;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::where('email', 'manager@cafe.com')->firstOrFail();
        $this->cashier = User::where('email', 'cashier@cafe.com')->firstOrFail();

        $this->coffeeBeans = Ingredient::where('sku', 'ING-BEAN-001')->firstOrFail();
        $this->milk = Ingredient::where('sku', 'ING-MILK-001')->firstOrFail();
        $this->cappuccino = Product::where('slug', 'caffe-cappuccino')->firstOrFail();
    }

    public function test_order_deducts_inventory_with_unit_conversion(): void
    {
        $inventoryService = app(InventoryService::class);

        $initialBeansStock = (float) $this->coffeeBeans->current_stock;
        $initialMilkStock = (float) $this->milk->current_stock;

        // Create order with 2 cappuccinos (2 * 18g = 36g beans, 2 * 180ml = 360ml milk)
        $order = Order::create([
            'order_number' => 'ORD-INV-001',
            'user_id' => $this->cashier->id,
            'order_type' => 'dine_in',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 9.50,
            'total_amount' => 9.50,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->cappuccino->id,
            'item_name' => $this->cappuccino->name,
            'quantity' => 2,
            'unit_price' => 4.75,
            'subtotal' => 9.50,
        ]);

        $order->load('items');

        $inventoryService->deductForOrder($order, $this->cashier);

        $this->coffeeBeans->refresh();
        $this->milk->refresh();

        $this->assertEquals(round($initialBeansStock - 0.04, 3), (float) $this->coffeeBeans->current_stock);
        $this->assertEquals(round($initialMilkStock - 0.10, 3), (float) $this->milk->current_stock);

        // Verify ledger movement entry exists
        $movement = InventoryMovement::where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('ingredient_id', $this->coffeeBeans->id)
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(-0.04, (float) $movement->quantity);
    }

    public function test_inventory_deduction_is_idempotent(): void
    {
        $inventoryService = app(InventoryService::class);

        $order = Order::create([
            'order_number' => 'ORD-INV-002',
            'user_id' => $this->cashier->id,
            'order_type' => 'takeaway',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 4.75,
            'total_amount' => 4.75,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->cappuccino->id,
            'item_name' => $this->cappuccino->name,
            'quantity' => 1,
            'unit_price' => 4.75,
            'subtotal' => 4.75,
        ]);

        $order->load('items');

        // First deduction
        $inventoryService->deductForOrder($order, $this->cashier);
        $stockAfterFirst = (float) $this->coffeeBeans->fresh()->current_stock;

        // Second deduction (simulating duplicated event)
        $inventoryService->deductForOrder($order, $this->cashier);
        $stockAfterSecond = (float) $this->coffeeBeans->fresh()->current_stock;

        $this->assertEquals($stockAfterFirst, $stockAfterSecond);
    }

    public function test_negative_inventory_is_blocked_when_override_is_disabled(): void
    {
        SystemSetting::set('allow_negative_inventory', '0');
        $inventoryService = app(InventoryService::class);

        // Set stock below what order needs
        $this->coffeeBeans->current_stock = 0.01; // Needs 0.04 Bag for 2 cappuccinos
        $this->coffeeBeans->save();

        $order = Order::create([
            'order_number' => 'ORD-INV-003',
            'user_id' => $this->cashier->id,
            'order_type' => 'dine_in',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 9.50,
            'total_amount' => 9.50,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->cappuccino->id,
            'item_name' => $this->cappuccino->name,
            'quantity' => 2,
            'unit_price' => 4.75,
            'subtotal' => 9.50,
        ]);

        $order->load('items');

        $this->expectException(Exception::class);
        $inventoryService->deductForOrder($order, $this->cashier);
    }

    public function test_manager_can_adjust_stock_for_spoilage_or_waste(): void
    {
        $inventoryService = app(InventoryService::class);

        $initialStock = (float) $this->milk->current_stock;

        $adjustment = $inventoryService->adjustStock(
            $this->milk,
            -2.0, // wasted 2 Gallons
            'spoilage_waste',
            'Expired milk gallons discarded',
            $this->manager
        );

        $this->milk->refresh();
        $this->assertEquals($initialStock - 2.0, (float) $this->milk->current_stock);
        $this->assertEquals(7.60, (float) $adjustment->cost_impact); // 2 gallons * $3.80/gal = $7.60
    }
}
