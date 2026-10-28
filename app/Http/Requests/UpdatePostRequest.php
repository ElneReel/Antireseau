<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'body' => [
                'required',
                'string',
                'max:' . config('insociable.post_max_length', 2000),
            ],
            'comments_allowed' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => "Le texte de la publication ne peut pas être vide.",
            'body.max' => "La publication ne peut pas dépasser " . config('insociable.post_max_length', 2000) . " caractères.",
        ];
    }
}
