<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDonationCheckoutRequest extends FormRequest
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
            'amount' => ['required', 'integer', 'min:100', 'max:100000'], // $1 - $1000
            'currency' => ['nullable', 'string', 'size:3'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Please choose an amount to donate.',
            'amount.integer' => 'Donation amount must be a whole number of cents.',
            'amount.min' => 'Minimum donation is $1.',
            'amount.max' => 'Maximum donation is $1,000.',
            'currency.size' => 'Currency must be a 3-letter code.',
        ];
    }
}
