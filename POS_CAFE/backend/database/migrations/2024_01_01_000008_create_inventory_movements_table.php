<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('movement_type', 40); // order_deduction, purchase_receipt, adjustment_increase, adjustment_decrease, waste, refund_restock
            $table->decimal('quantity', 12, 3); // signed: negative for deductions, positive for receipts
            $table->decimal('previous_stock', 12, 3);
            $table->decimal('new_stock', 12, 3);
            $table->decimal('unit_cost', 10, 4)->default(0.0000);
            $table->string('reference_type', 40)->nullable(); // order, purchase_order, stock_adjustment, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reason', 255)->nullable();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->timestamps();

            $table->index(['ingredient_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('type', 40); // count_correction, spoilage_waste, damage, internal_use
            $table->decimal('quantity_adjusted', 12, 3); // signed
            $table->decimal('cost_impact', 10, 2)->default(0.00);
            $table->text('reason');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('inventory_movements');
    }
};
