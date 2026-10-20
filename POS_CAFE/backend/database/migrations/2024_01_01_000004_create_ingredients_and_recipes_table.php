<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('sku', 60)->unique();
            $table->string('unit', 20)->default('g'); // g, ml, pcs, kg, l
            $table->decimal('cost_per_unit', 10, 4)->default(0.0000);
            $table->decimal('current_stock', 12, 3)->default(0.000);
            $table->decimal('min_stock_alert', 12, 3)->default(10.000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'product_variant_id']);
        });

        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('quantity', 10, 3)->default(1.000);
            $table->string('unit', 20)->default('g');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_items');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('ingredients');
    }
};
