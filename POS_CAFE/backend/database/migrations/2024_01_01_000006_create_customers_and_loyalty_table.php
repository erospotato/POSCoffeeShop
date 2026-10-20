<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('phone', 30)->unique();
            $table->string('email', 150)->nullable();
            $table->integer('points_balance')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->integer('points_required')->default(100);
            $table->string('discount_type', 30)->default('fixed_amount'); // fixed_amount, percentage, free_product
            $table->decimal('discount_value', 10, 2)->default(0.00);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('type', 30); // earned, redeemed, refund_reversed, manual_adjustment
            $table->integer('points'); // positive for earned, negative for redeemed/reversed
            $table->integer('balance_after');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('customers');
    }
};
