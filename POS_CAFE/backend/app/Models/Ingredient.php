<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'unit',
        'cost_per_unit',
        'current_stock',
        'min_stock_alert',
        'is_active',
    ];

    protected $casts = [
        'cost_per_unit' => 'decimal:4',
        'current_stock' => 'decimal:3',
        'min_stock_alert' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function recipeItems(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function isLowStock(): bool
    {
        return (float) $this->current_stock <= (float) $this->min_stock_alert;
    }

    public function stockValue(): float
    {
        return (float) $this->current_stock * (float) $this->cost_per_unit;
    }
}
