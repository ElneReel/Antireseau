<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:10', 'max:1500'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => "Explique brièvement la situation pour ta contestation.",
            'message.min' => "Ton message doit comporter au moins 10 caractères.",
            'message.max' => "Ton message ne peut pas dépasser 1 500 caractères.",
        ];
    }
}
