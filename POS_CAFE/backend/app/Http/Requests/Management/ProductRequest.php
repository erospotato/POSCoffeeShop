<?php

namespace App\Http\Requests\Management;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isManager();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('variants') && is_array($this->variants)) {
            $filteredVariants = array_values(array_filter($this->variants, function ($v) {
                return !empty(trim($v['name'] ?? ''));
            }));
            $this->merge(['variants' => count($filteredVariants) > 0 ? $filteredVariants : null]);
        }

        if ($this->has('addons') && is_array($this->addons)) {
            $filteredAddons = array_values(array_filter($this->addons, function ($a) {
                return !empty(trim($a['name'] ?? ''));
            }));
            $this->merge(['addons' => count($filteredAddons) > 0 ? $filteredAddons : null]);
        }
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'image_path' => ['nullable', 'string', 'max:500'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:80'],
            'variants.*.price_modifier' => ['required_with:variants', 'numeric'],
            'variants.*.sku' => ['nullable', 'string', 'max:60'],
            'addons' => ['nullable', 'array'],
            'addons.*.name' => ['required_with:addons', 'string', 'max:100'],
            'addons.*.price' => ['required_with:addons', 'numeric', 'min:0'],
        ];
    }
}
