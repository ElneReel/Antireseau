<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
                'max:' . config('insociable.comment_max_length', 1000),
            ],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => "Le commentaire ne peut pas être vide.",
            'body.max' => "Le commentaire ne peut pas dépasser " . config('insociable.comment_max_length', 1000) . " caractères.",
        ];
    }
}
