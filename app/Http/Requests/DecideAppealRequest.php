<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecideAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approved,denied'],
            'admin_decision_note' => ['required', 'string', 'max:5000'],
        ];
    }
}
