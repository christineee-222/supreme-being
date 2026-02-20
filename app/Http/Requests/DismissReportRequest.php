<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DismissReportRequest extends FormRequest
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
            'resolution_note' => ['required', 'string', 'max:5000'],
        ];
    }
}
