<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isManager() || $this->user()->isCashier();
    }

    public function rules(): array
    {
        return [
            'order_type' => ['required', 'in:dine_in,takeaway,delivery'],
            'table_number' => ['nullable', 'string', 'max:20'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'loyalty_reward_id' => ['nullable', 'exists:loyalty_rewards,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'items.*.addon_ids' => ['nullable', 'array'],
            'items.*.addon_ids.*' => ['exists:product_addons,id'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.method' => ['required', 'in:cash,card,qr_code,loyalty_points'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.cash_received' => ['nullable', 'numeric', 'min:0'],
            'payments.*.reference' => ['nullable', 'string', 'max:100'],
        ];
    }
}
