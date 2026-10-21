<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'cafe_name', 'value' => 'Veloura Café', 'group' => 'general', 'description' => 'The brand name of the café'],
            ['key' => 'cafe_address', 'value' => 'No. 88 Heritage Blvd, University District', 'group' => 'general', 'description' => 'Store physical location'],
            ['key' => 'cafe_phone', 'value' => '+855 23 888 999', 'group' => 'general', 'description' => 'Contact telephone number'],
            ['key' => 'cafe_email', 'value' => 'hello@velouracafe.com', 'group' => 'general', 'description' => 'Customer service email'],
            ['key' => 'currency_symbol', 'value' => '$', 'group' => 'pricing', 'description' => 'Currency symbol displayed across POS and receipts'],
            ['key' => 'tax_rate', 'value' => '0.0', 'group' => 'pricing', 'description' => 'Standard VAT / Sales tax percentage (0% default)'],
            ['key' => 'tax_mode', 'value' => 'exclusive', 'group' => 'pricing', 'description' => 'Tax calculation mode: inclusive or exclusive'],
            ['key' => 'order_prefix', 'value' => 'ORD', 'group' => 'pos', 'description' => 'Prefix for order identification numbers'],
            ['key' => 'receipt_footer', 'value' => 'Thank you for choosing Veloura Café! Enjoy your handcrafted brew.', 'group' => 'pos', 'description' => 'Printed text on thermal receipts'],
            ['key' => 'loyalty_points_per_dollar', 'value' => '1.0', 'group' => 'loyalty', 'description' => 'Points awarded per $1 spent on paid orders'],
            ['key' => 'allow_negative_inventory', 'value' => '1', 'group' => 'inventory', 'description' => 'Allow sales without blocking on inventory levels'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
