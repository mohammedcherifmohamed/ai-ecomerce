<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class CustomerSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required_without:email|integer|exists:customers,id',
            'email' => 'required_without:customer_id|email|exists:users,email',
        ];
    }
}
