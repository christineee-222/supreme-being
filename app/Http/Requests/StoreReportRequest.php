<?php

namespace App\Http\Requests;

use App\Enums\ReportReason;
use App\Http\Controllers\Moderation\ReportController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
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
            'reported_user_id' => ['required', 'uuid', 'exists:users,id'],
            'reportable_type' => ['required', Rule::in(array_keys(ReportController::REPORTABLE_TYPES))],
            'reportable_id' => ['required', 'uuid'],
            'reason' => ['required', Rule::enum(ReportReason::class)],
            'reporter_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
