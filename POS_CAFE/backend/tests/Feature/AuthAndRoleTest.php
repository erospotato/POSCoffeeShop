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
