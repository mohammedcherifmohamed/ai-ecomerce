<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class CreateInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inquiry' => 'required|string|min:7|max:255',
            'category' => 'nullable|string|max:100',
        ];
    }
}
