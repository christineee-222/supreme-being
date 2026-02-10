<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRsvpRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Auth already handled by auth.workos middleware
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:going,interested,not_going',
            ],
        ];
    }

    /**
     * Normalize input before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower(trim((string) $this->input('status'))),
            ]);
        }
    }
}

