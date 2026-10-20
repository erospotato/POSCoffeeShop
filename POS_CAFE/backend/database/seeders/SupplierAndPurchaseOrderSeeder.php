<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupplierAndPurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::whereHas('role', function ($q) {
            $q->where('slug', 'manager');
        })->first() ?: User::first();

        $sup1 = Supplier::create([
            'name' => 'Highland Specialty Coffee Estate',
            'contact_name' => 'Carlos Mendoza',
            'phone' => '+855 77 123 456',
            'email' => 'orders@highlandestate.com',
            'address' => 'Plantation Sector 4, Mountain Foothills',
            'is_active' => true,
        ]);

        $sup2 = Supplier::create([
            'name' => 'Pure Pasture Dairy Co.',
            'contact_name' => 'Sarah Jenkins',
            'phone' => '+855 88 654 321',
            'email' => 'sales@purepasture.com',
            'address' => 'Green Valley Farm Road 12',
            'is_active' => true,
        ]);

        $sup3 = Supplier::create([
            'name' => 'Artisan Bakery & Packaging Supply',
            'contact_name' => 'Jean-Luc Dubois',
            'phone' => '+855 99 789 012',
            'email' => 'contact@artisanpack.com',
            'address' => 'Industrial Parkway Block B',
            'is_active' => true,
        ]);

        // Purchase order
        $beans = Ingredient::where('sku', 'ING-BEAN-001')->first();
        if ($beans) {
            $po = PurchaseOrder::create([
                'po_number' => 'PO-2026-001',
                'supplier_id' => $sup1->id,
                'created_by' => $manager->id,
                'status' => 'received',
                'total_cost' => 250.00,
                'ordered_at' => now()->subDays(5),
                'expected_delivery_date' => now()->subDays(2),
                'received_at' => now()->subDays(2),
                'notes' => 'Bulk seasonal Arabica beans delivery.',
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'ingredient_id' => $beans->id,
                'quantity_ordered' => 10000.000, // 10kg
                'quantity_received' => 10000.000,
                'unit_price' => 0.0250,
                'subtotal' => 250.00,
            ]);
        }
    }
}
