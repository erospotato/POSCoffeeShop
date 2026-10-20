<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isManager();
    }

    public function rules(): array
    {
        return [
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'quantity_adjusted' => ['required', 'numeric', 'not_in:0'],
            'type' => ['required', 'in:count_correction,spoilage_waste,damage,internal_use'],
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }
}
