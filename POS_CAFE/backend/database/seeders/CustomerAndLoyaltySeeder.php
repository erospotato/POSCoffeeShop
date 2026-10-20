<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyTransaction;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CustomerAndLoyaltySeeder extends Seeder
{
    public function run(): void
    {
        $c1 = Customer::create([
            'name' => 'Sophia Martinez',
            'phone' => '012999001',
            'email' => 'sophia.m@example.com',
            'points_balance' => 150,
            'total_spent' => 125.50,
            'is_active' => true,
        ]);

        $c2 = Customer::create([
            'name' => 'David Chen',
            'phone' => '012999002',
            'email' => 'david.c@example.com',
            'points_balance' => 60,
            'total_spent' => 60.00,
            'is_active' => true,
        ]);

        $c3 = Customer::create([
            'name' => 'Emily Watson',
            'phone' => '012999003',
            'email' => 'emily.w@example.com',
            'points_balance' => 320,
            'total_spent' => 320.00,
            'is_active' => true,
        ]);

        // Loyalty transactions history
        LoyaltyTransaction::create([
            'customer_id' => $c1->id,
            'type' => 'earned',
            'points' => 150,
            'balance_after' => 150,
            'notes' => 'Points accumulated from regular visits',
        ]);

        LoyaltyTransaction::create([
            'customer_id' => $c3->id,
            'type' => 'earned',
            'points' => 320,
            'balance_after' => 320,
            'notes' => 'Frequent customer loyalty rewards',
        ]);

        // Rewards catalog
        LoyaltyReward::create([
            'name' => '$2 Off Any Drink or Order',
            'description' => 'Redeem 50 loyalty points for an instant $2 discount.',
            'points_required' => 50,
            'discount_type' => 'fixed_amount',
            'discount_value' => 2.00,
            'is_active' => true,
        ]);

        LoyaltyReward::create([
            'name' => '$5 Café Voucher',
            'description' => 'Redeem 100 points for a $5 discount on your total checkout.',
            'points_required' => 100,
            'discount_type' => 'fixed_amount',
            'discount_value' => 5.00,
            'is_active' => true,
        ]);

        $croissant = Product::where('slug', 'butter-flaky-croissant')->first();
        LoyaltyReward::create([
            'name' => 'Free French Butter Croissant',
            'description' => 'Enjoy a complimentary fresh pastry on us.',
            'points_required' => 80,
            'discount_type' => 'free_product',
            'discount_value' => 2.75,
            'product_id' => $croissant?->id,
            'is_active' => true,
        ]);
    }
}
