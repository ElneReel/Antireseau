<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => "Tu dois saisir ton mot de passe pour confirmer la suppression définitive.",
            'password.current_password' => "Le mot de passe saisi est incorrect.",
        ];
    }
}
