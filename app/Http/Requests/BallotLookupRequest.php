<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class BallotLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address.required' => 'Please enter an address.',
            'address.min' => 'Please enter a more complete address (street, city, state).',
        ];
    }
}
