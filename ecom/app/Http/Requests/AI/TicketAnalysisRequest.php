<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class TicketAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'category' => 'nullable|string|max:100',
        ];
    }
}
