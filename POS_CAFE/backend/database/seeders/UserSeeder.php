<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $managerRole = Role::where('slug', 'manager')->firstOrFail();
        $cashierRole = Role::where('slug', 'cashier')->firstOrFail();
        $baristaRole = Role::where('slug', 'barista')->firstOrFail();

        $defaultPassword = Hash::make('password');

        User::firstOrCreate(['email' => 'admin@cafe.com'], [
            'name' => 'Alice Administrator',
            'role_id' => $adminRole->id,
            'phone' => '+855 12 345 678',
            'password' => $defaultPassword,
            'is_active' => true,
        ]);

        User::firstOrCreate(['email' => 'manager@cafe.com'], [
            'name' => 'Michael Manager',
            'role_id' => $managerRole->id,
            'phone' => '+855 12 345 679',
            'password' => $defaultPassword,
            'is_active' => true,
        ]);

        User::firstOrCreate(['email' => 'cashier@cafe.com'], [
            'name' => 'Cashier 1',
            'role_id' => $cashierRole->id,
            'phone' => '+855 12 345 680',
            'password' => $defaultPassword,
            'is_active' => true,
        ]);

        User::firstOrCreate(['email' => 'barista@cafe.com'], [
            'name' => 'Ben Barista',
            'role_id' => $baristaRole->id,
            'phone' => '+855 12 345 681',
            'password' => $defaultPassword,
            'is_active' => true,
        ]);
    }
}
