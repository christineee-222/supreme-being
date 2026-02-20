<?php

namespace App\Http\Requests;

use App\Enums\PerformanceReviewOutcome;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecidePerformanceReviewRequest extends FormRequest
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
            'admin_outcome' => ['required', Rule::enum(PerformanceReviewOutcome::class)],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
