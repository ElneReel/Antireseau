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
                'required_without_all:image,media_file',
                'nullable',
                'string',
                'max:' . config('insociable.post_max_length', 2000),
            ],
            'image' => [
                'nullable',
                'file',
                'mimes:jpeg,png,webp,jpg,mp4,webm,mp3,wav,ogg',
                'max:' . config('insociable.media.max_file_size_kb', 51200),
            ],
            'media_file' => [
                'nullable',
                'file',
                'mimes:jpeg,png,webp,jpg,mp4,webm,mp3,wav,ogg',
                'max:' . config('insociable.media.max_file_size_kb', 51200),
            ],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'comments_allowed' => ['nullable', 'boolean'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required_without_all' => "La publication doit comporter du texte si aucun média n'est joint.",
            'body.max' => "La publication ne peut pas dépasser " . config('insociable.post_max_length', 2000) . " caractères.",
            'image.mimes' => "Formats acceptés : Photos (JPEG, PNG, WebP), Vidéos (MP4, WebM) ou Audio (MP3, WAV, OGG).",
            'image.max' => "Le fichier dépasse la taille maximale autorisée (50 Mo).",
            'media_file.mimes' => "Formats acceptés : Photos (JPEG, PNG, WebP), Vidéos (MP4, WebM) ou Audio (MP3, WAV, OGG).",
            'media_file.max' => "Le fichier dépasse la taille maximale autorisée (50 Mo).",
        ];
    }
}
