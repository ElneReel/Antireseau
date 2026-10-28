<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'body' => [
                'required_without:image',
                'nullable',
                'string',
                'max:' . config('insociable.post_max_length', 2000),
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,webp,jpg',
                'max:' . config('insociable.media.max_file_size_kb', 3072),
            ],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'comments_allowed' => ['nullable', 'boolean'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required_without' => "La publication doit comporter du texte si aucune image n'est jointe.",
            'body.max' => "La publication ne peut pas dépasser " . config('insociable.post_max_length', 2000) . " caractères.",
            'image.image' => "Le fichier doit être une image valide.",
            'image.mimes' => "Seuls les formats JPEG, PNG et WebP sont autorisés.",
            'image.max' => "L'image ne peut pas dépasser 3 Mo.",
        ];
    }
}
