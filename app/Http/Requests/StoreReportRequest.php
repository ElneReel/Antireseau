<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'target_type' => ['required', 'string', 'in:post,comment,user'],
            'target_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:100'],
            'details' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => "Le motif du signalement est obligatoire.",
            'details.max' => "Les précisions ne peuvent pas dépasser 1 000 caractères.",
        ];
    }
}
