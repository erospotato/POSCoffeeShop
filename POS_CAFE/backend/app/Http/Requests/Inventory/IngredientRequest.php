<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isManager();
    }

    public function rules(): array
    {
        $ingId = $this->route('ingredient')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['required', 'string', 'max:60', Rule::unique('ingredients', 'sku')->ignore($ingId)],
            'unit' => ['required', 'string', 'max:20'],
            'cost_per_unit' => ['nullable', 'numeric', 'min:0'],
            'current_stock' => ['nullable', 'numeric', 'min:0'],
            'min_stock_alert' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
