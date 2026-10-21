<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;
    protected User $barista;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('email', 'admin@cafe.com')->firstOrFail();
        $this->manager = User::where('email', 'manager@cafe.com')->firstOrFail();
        $this->cashier = User::where('email', 'cashier@cafe.com')->firstOrFail();
        $this->barista = User::where('email', 'barista@cafe.com')->firstOrFail();
    }

    public function test_admin_can_access_administrative_routes(): void
    {
        $response = $this->actingAs($this->admin)->get(route('staff.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->get(route('settings.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->get(route('audit-logs.index'));
        $response->assertStatus(200);
    }

    public function test_cashier_cannot_access_administrative_routes(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('staff.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->cashier)->get(route('settings.index'));
        $response->assertStatus(403);
    }

    public function test_barista_cannot_access_pos_or_financial_reports(): void
    {
        $response = $this->actingAs($this->barista)->get(route('pos.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->barista)->get(route('reports.sales'));
        $response->assertStatus(403);
    }

    public function test_barista_can_access_kitchen_display_system(): void
    {
        $response = $this->actingAs($this->barista)->get(route('kds.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->barista)->getJson(route('kds.poll'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['orders']);
    }

    public function test_barista_can_access_inventory_recipes_and_adjustments(): void
    {
        $response = $this->actingAs($this->barista)->get(route('ingredients.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->barista)->get(route('recipes.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->barista)->get(route('adjustments.index'));
        $response->assertStatus(200);

        $ingredient = \App\Models\Ingredient::first();
        if ($ingredient) {
            $response = $this->actingAs($this->barista)->post(route('adjustments.store'), [
                'ingredient_id' => $ingredient->id,
                'type' => 'spoilage_waste',
                'quantity_adjusted' => -1,
                'reason' => 'Accidental spillage at coffee machine',
            ]);
            $response->assertRedirect(route('adjustments.index'));
            $response->assertSessionHas('success');
        }
    }

    public function test_cashier_name_is_cashier_1_and_can_choose_shift_station_and_period(): void
    {
        $this->assertEquals('Cashier 1', $this->cashier->name);

        \App\Models\CashierShift::where('user_id', $this->cashier->id)->delete();

        $response = $this->actingAs($this->cashier)->post(route('shifts.open'), [
            'opening_cash' => 120.00,
            'cashier_station' => 'Cashier 2',
            'shift_period' => 'Morning',
        ]);
        $response->assertRedirect(route('pos.index'));

        $shift = $this->cashier->activeShift();
        $this->assertNotNull($shift);
        $this->assertEquals('Cashier 2', $shift->cashier_station);
        $this->assertEquals('Morning', $shift->shift_period);
        $this->assertEquals(120.00, (float) $shift->opening_cash);
    }

    public function test_cashier_cannot_create_loyalty_reward(): void
    {
        $response = $this->actingAs($this->cashier)->post(route('loyalty.rewards.store'), [
            'name' => 'Free Pastry',
            'points_required' => 50,
            'discount_type' => 'fixed_amount',
            'discount_value' => 5.00,
        ]);
        $response->assertStatus(403);
    }

    public function test_purchase_order_can_be_created_with_product_and_amount_only(): void
    {
        $supplier = \App\Models\Supplier::first();
        $ingredient = \App\Models\Ingredient::first();

        $response = $this->actingAs($this->admin)->post(route('purchase-orders.store'), [
            'supplier_id' => $supplier->id,
            'ordered_at' => now()->toDateString(),
            'items' => [
                [
                    'ingredient_id' => $ingredient->id,
                    'quantity_ordered' => 20,
                ],
            ],
        ]);

        $response->assertSessionHas('success');
        $po = \App\Models\PurchaseOrder::latest()->first();
        $this->assertNotNull($po);
        $this->assertEquals(1, $po->items->count());
        $this->assertEquals(20, (float) $po->items->first()->quantity_ordered);
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $cashierRole = Role::where('slug', 'cashier')->firstOrFail();

        $response = $this->actingAs($this->admin)->put(route('staff.update', $this->admin->id), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role_id' => $cashierRole->id,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors(['role_id']);
        $this->assertEquals('Administrator', $this->admin->fresh()->role->name);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $response = $this->actingAs($this->admin)->put(route('staff.update', $this->admin->id), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role_id' => $this->admin->role_id,
            'is_active' => false,
        ]);

        $response->assertSessionHasErrors(['is_active']);
        $this->assertTrue($this->admin->fresh()->is_active);
    }
}
