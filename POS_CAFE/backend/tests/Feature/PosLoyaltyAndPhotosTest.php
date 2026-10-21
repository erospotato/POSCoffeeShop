<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosLoyaltyAndPhotosTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected User $manager;
    protected CashierShift $shift;
    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::where('email', 'cashier@cafe.com')->firstOrFail();
        $this->manager = User::where('email', 'manager@cafe.com')->firstOrFail();

        $this->shift = CashierShift::firstOrCreate(
            ['user_id' => $this->cashier->id, 'status' => 'open'],
            [
                'opening_balance' => 100.00,
                'expected_cash' => 100.00,
                'opened_at' => now(),
            ]
        );

        $this->customer = Customer::create([
            'name' => 'Alice Member',
            'phone' => '012888999',
            'email' => 'alice@test.com',
            'points_balance' => 120,
            'total_spent' => 100.00,
            'is_active' => true,
        ]);

        $this->product = Product::where('is_available', true)->firstOrFail();
    }

    public function test_photo_library_api_returns_authentic_photos(): void
    {
        $response = $this->actingAs($this->cashier)->getJson(route('photos.search', ['q' => 'coffee']));
        $response->assertOk();
        $response->assertJsonStructure(['photos' => [['title', 'category', 'url']]]);
        $this->assertNotEmpty($response->json('photos'));
    }

    public function test_pos_pricing_calculation_removes_tax_and_applies_loyalty_reward(): void
    {
        $reward = LoyaltyReward::create([
            'name' => '$2 Instant Discount',
            'points_required' => 50,
            'discount_type' => 'fixed_amount',
            'discount_value' => 2.00,
            'is_active' => true,
        ]);

        $items = [
            [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]
        ];

        $response = $this->actingAs($this->cashier)->postJson(route('pos.calculate'), [
            'items' => $items,
            'customer_id' => $this->customer->id,
            'loyalty_reward_id' => $reward->id,
        ]);

        $response->assertOk();
        $data = $response->json('data');

        // Verify Tax is zero
        $this->assertEquals(0.0, $data['tax_amount']);
        $this->assertEquals(0.0, $data['tax_rate']);

        // Verify Loyalty discount applied
        $this->assertEquals(2.0, $data['loyalty_discount']);
        $expectedTotal = round($data['subtotal'] - 2.00, 2);
        $this->assertEquals($expectedTotal, $data['total_amount']);
    }

    public function test_checkout_redeems_customer_points_and_returns_receipt_data(): void
    {
        $reward = LoyaltyReward::create([
            'name' => '$2 Member Discount',
            'points_required' => 50,
            'discount_type' => 'fixed_amount',
            'discount_value' => 2.00,
            'is_active' => true,
        ]);

        $initialBalance = $this->customer->points_balance;

        $items = [
            [
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]
        ];

        $checkoutResponse = $this->actingAs($this->cashier)->postJson(route('pos.checkout'), [
            'order_type' => 'dine_in',
            'table_number' => 'Table 9',
            'customer_id' => $this->customer->id,
            'loyalty_reward_id' => $reward->id,
            'items' => $items,
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => (float) $this->product->base_price - 2.00,
                    'cash_received' => 10.00,
                ]
            ]
        ]);

        $checkoutResponse->assertOk();
        $this->assertTrue($checkoutResponse->json('success'));
        $this->assertNotNull($checkoutResponse->json('receipt_data'));
        $this->assertEquals(0.0, $checkoutResponse->json('receipt_data.tax_amount'));

        // Customer points should have deducted 50, and then earned points for the new order
        $this->customer->refresh();
        $this->assertLessThan($initialBalance, $this->customer->points_balance);

        // Verify that viewing customer's loyalty history page renders successfully without errors
        $historyResponse = $this->actingAs($this->cashier)->get(route('customers.show', $this->customer->id));
        $historyResponse->assertOk();
        $historyResponse->assertSee('Loyalty Points Activity');
        $historyResponse->assertSee($this->customer->name);
    }

    public function test_pos_terminal_renders_with_clean_catalog_functions(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('pos.index'));
        $response->assertOk();
        $response->assertSee('selectProductById');
        $response->assertSee('quickAddProductById');
        $response->assertSee('cartItemsList');
        $response->assertSee('emptyCartMessage');
    }

    public function test_quick_customer_enrollment_from_pos(): void
    {
        $response = $this->actingAs($this->cashier)->postJson(route('pos.customers.quick_create'), [
            'name' => 'James Quick',
            'phone' => '099123456',
            'email' => 'james.q@example.com',
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        $this->assertDatabaseHas('customers', [
            'phone' => '099123456',
            'name' => 'James Quick',
        ]);
    }
}
