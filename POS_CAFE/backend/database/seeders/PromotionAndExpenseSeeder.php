<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Seeder;

class PromotionAndExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::whereHas('role', function ($q) {
            $q->where('slug', 'manager');
        })->first() ?: User::first();

        // Promotions
        Promotion::create([
            'name' => 'Afternoon Coffee Special (15% Off)',
            'code' => 'COFFEE15',
            'type' => 'percentage',
            'value' => 15.00,
            'min_spend' => 5.00,
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(60),
            'is_active' => true,
            'usage_limit' => 200,
            'used_count' => 14,
        ]);

        Promotion::create([
            'name' => 'Weekend Brunch $3 Voucher',
            'code' => 'WEEKEND3',
            'type' => 'fixed_amount',
            'value' => 3.00,
            'min_spend' => 10.00,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(30),
            'is_active' => true,
            'usage_limit' => 100,
            'used_count' => 8,
        ]);

        // Expense Categories
        $catRent = ExpenseCategory::create(['name' => 'Rent & Facility Lease', 'slug' => 'rent', 'description' => 'Commercial space lease']);
        $catUtil = ExpenseCategory::create(['name' => 'Utilities (Power, Water, Net)', 'slug' => 'utilities', 'description' => 'Monthly electricity and high-speed fiber']);
        $catMaint = ExpenseCategory::create(['name' => 'Equipment & Maintenance', 'slug' => 'maintenance', 'description' => 'Espresso grinder maintenance and water filtration']);
        $catSupplies = ExpenseCategory::create(['name' => 'Cleaning & Consumables', 'slug' => 'supplies', 'description' => 'Sanitizing solutions, towels, biodegradable bags']);

        // Sample approved expenses
        Expense::create([
            'expense_category_id' => $catMaint->id,
            'user_id' => $manager->id,
            'amount' => 120.00,
            'expense_date' => now()->subDays(4),
            'payment_method' => 'bank_transfer',
            'description' => 'Quarterly commercial espresso machine descaling and gasket replacement',
            'is_approved' => true,
            'approved_by' => $manager->id,
        ]);

        Expense::create([
            'expense_category_id' => $catSupplies->id,
            'user_id' => $manager->id,
            'amount' => 45.50,
            'expense_date' => now()->subDays(2),
            'payment_method' => 'cash',
            'description' => 'Eco-friendly cleaning supplies and paper towels',
            'is_approved' => true,
            'approved_by' => $manager->id,
        ]);
    }
}
