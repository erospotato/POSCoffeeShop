<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'user_id',
        'movement_type',
        'quantity',
        'previous_stock',
        'new_stock',
        'unit_cost',
        'reference_type',
        'reference_id',
        'reason',
        'idempotency_key',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'previous_stock' => 'decimal:3',
        'new_stock' => 'decimal:3',
        'unit_cost' => 'decimal:4',
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
