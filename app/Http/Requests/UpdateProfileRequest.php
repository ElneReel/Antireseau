<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'display_name' => ['nullable', 'string', 'max:60'],
            'bio' => ['nullable', 'string', 'max:' . config('insociable.bio_max_length', 240)],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:3072'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.max' => "Le nom d'affichage ne peut pas dépasser 60 caractères.",
            'bio.max' => "La bio ne peut pas dépasser " . config('insociable.bio_max_length', 240) . " caractères.",
            'avatar.image' => "L'avatar doit être une image valide.",
            'avatar.mimes' => "L'avatar doit être au format JPEG, PNG ou WebP.",
            'avatar.max' => "L'avatar ne peut pas dépasser 3 Mo.",
        ];
    }
}
