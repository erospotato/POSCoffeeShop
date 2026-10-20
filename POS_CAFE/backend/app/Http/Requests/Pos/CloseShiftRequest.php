<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class CloseShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'actual_cash' => ['required', 'numeric', 'min:0', 'max:100000'],
            'closing_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
