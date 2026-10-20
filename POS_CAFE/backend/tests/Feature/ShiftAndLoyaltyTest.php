<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\User;
use App\Services\LoyaltyService;
use App\Services\ShiftService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftAndLoyaltyTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected User $manager;
    protected Customer $customer;
    protected LoyaltyReward $reward;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::where('email', 'cashier@cafe.com')->firstOrFail();
        $this->manager = User::where('email', 'manager@cafe.com')->firstOrFail();

        // Close any existing open shifts for cashier so tests have clean state
        CashierShift::where('user_id', $this->cashier->id)
            ->where('status', 'open')
            ->update(['status' => 'closed', 'closed_at' => now()]);

        $this->customer = Customer::firstOrCreate(
            ['phone' => '+15559998888'],
            [
                'name' => 'Alice Test',
                'email' => 'alice.test@example.com',
                'points_balance' => 100,
                'total_spent' => 100.00,
                'is_active' => true,
            ]
        );

        $this->reward = LoyaltyReward::firstOrCreate(
            ['name' => 'Free Specialty Drink Test'],
            [
                'points_required' => 50,
                'discount_type' => 'fixed_amount',
                'discount_value' => 5.00,
                'is_active' => true,
            ]
        );
    }

    public function test_cashier_can_open_shift_and_prevent_duplicate_open_shifts(): void
    {
        $shiftService = app(ShiftService::class);

        $shift = $shiftService->openShift($this->cashier, 100.00);

        $this->assertEquals('open', $shift->status);
        $this->assertEquals(100.00, (float) $shift->opening_cash);
        $this->assertEquals(100.00, (float) $shift->expected_cash);

        // Attempting to open another shift while one is open must fail
        $this->expectException(Exception::class);
        $shiftService->openShift($this->cashier, 50.00);
    }

    public function test_cash_movements_update_expected_cash(): void
    {
        $shiftService = app(ShiftService::class);

        $shift = $shiftService->openShift($this->cashier, 100.00);

        // Cash-In of $50
        $shiftService->recordCashMovement($shift, 'cash_in', 50.00, 'Additional change fund', $this->cashier);
        $shift->refresh();
        $this->assertEquals(150.00, (float) $shift->expected_cash);

        // Cash-Out of $20
        $shiftService->recordCashMovement($shift, 'cash_out', 20.00, 'Petty cash purchase', $this->cashier);
        $shift->refresh();
        $this->assertEquals(130.00, (float) $shift->expected_cash);
    }

    public function test_cashier_can_close_shift_and_record_discrepancy(): void
    {
        $shiftService = app(ShiftService::class);

        $shift = $shiftService->openShift($this->cashier, 100.00);
        $shiftService->recordCashMovement($shift, 'cash_in', 50.00, 'Drawer float', $this->cashier);

        // Expected is 150.00. Cashier counts 145.00 (Short by $5.00)
        $closedShift = $shiftService->closeShift($shift, 145.00, 'Drawer short by $5');

        $this->assertEquals('closed', $closedShift->status);
        $this->assertEquals(145.00, (float) $closedShift->actual_cash);
        $this->assertEquals(-5.00, (float) $closedShift->discrepancy);
        $this->assertNotNull($closedShift->closed_at);

        // Manager reviews shift
        $reviewedShift = $shiftService->reviewShift($closedShift, $this->manager, 'Approved minor discrepancy');
        $this->assertEquals($this->manager->id, $reviewedShift->manager_reviewed_by);
        $this->assertNotNull($reviewedShift->reviewed_at);
    }

    public function test_customer_earns_loyalty_points_on_completed_order(): void
    {
        $loyaltyService = app(LoyaltyService::class);

        $initialBalance = $this->customer->points_balance;

        $order = Order::create([
            'order_number' => 'ORD-LOY-001',
            'user_id' => $this->cashier->id,
            'customer_id' => $this->customer->id,
            'order_type' => 'dine_in',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 25.00,
            'total_amount' => 25.00,
        ]);

        $txn = $loyaltyService->awardPointsForOrder($order);

        $this->assertNotNull($txn);
        $this->assertEquals(25, $txn->points); // 1 pt per $1
        $this->customer->refresh();
        $this->assertEquals($initialBalance + 25, $this->customer->points_balance);

        // Verify idempotency: awarding again does not double points
        $secondTxn = $loyaltyService->awardPointsForOrder($order);
        $this->assertEquals($txn->id, $secondTxn->id);
        $this->customer->refresh();
        $this->assertEquals($initialBalance + 25, $this->customer->points_balance);
    }

    public function test_customer_can_redeem_loyalty_points_for_reward(): void
    {
        $loyaltyService = app(LoyaltyService::class);

        $this->customer->points_balance = 80;
        $this->customer->save();

        // Redeem 50 points
        $txn = $loyaltyService->redeemReward($this->customer, $this->reward);

        $this->assertEquals(-50, $txn->points);
        $this->customer->refresh();
        $this->assertEquals(30, $this->customer->points_balance);

        // Attempting to redeem again with insufficient points (needs 50, has 30) must throw
        $this->expectException(Exception::class);
        $loyaltyService->redeemReward($this->customer, $this->reward);
    }

    public function test_order_refund_reverses_awarded_loyalty_points(): void
    {
        $loyaltyService = app(LoyaltyService::class);

        $order = Order::create([
            'order_number' => 'ORD-LOY-002',
            'user_id' => $this->cashier->id,
            'customer_id' => $this->customer->id,
            'order_type' => 'dine_in',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 20.00,
            'total_amount' => 20.00,
        ]);

        $loyaltyService->awardPointsForOrder($order);
        $balanceAfterAward = $this->customer->fresh()->points_balance;

        // Refund full $20.00
        $reverseTxn = $loyaltyService->reversePointsForRefund($order, 20.00);

        $this->assertNotNull($reverseTxn);
        $this->assertEquals(-20, $reverseTxn->points);
        $this->assertEquals($balanceAfterAward - 20, $this->customer->fresh()->points_balance);
    }
}
