<?php

namespace App\Http\Requests;

use App\Enums\ReportResolution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveReportRequest extends FormRequest
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
            'resolution' => ['required', Rule::enum(ReportResolution::class)],
            'resolution_note' => ['required', 'string', 'max:5000'],
            'rule_reference' => ['nullable', 'string', 'max:20'],
            'moderator_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
