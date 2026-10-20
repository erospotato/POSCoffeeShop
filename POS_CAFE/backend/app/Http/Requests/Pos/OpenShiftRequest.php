<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class OpenShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isManager() || $this->user()->isCashier();
    }

    public function rules(): array
    {
        return [
            'opening_cash' => ['required', 'numeric', 'min:0', 'max:50000'],
        ];
    }
}
