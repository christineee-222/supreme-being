<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModeratorApplicationRequest extends FormRequest
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
            'motivation' => ['required', 'string', 'max:10000'],
            'scenario_response_1' => ['required', 'string', 'max:10000'],
            'scenario_response_2' => ['required', 'string', 'max:10000'],
            'conflicts_of_interest' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
