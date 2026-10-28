<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => "Le mot de passe actuel est requis.",
            'current_password.current_password' => "Le mot de passe actuel est incorrect.",
            'password.required' => "Le nouveau mot de passe est requis.",
            'password.confirmed' => "La confirmation du mot de passe ne correspond pas.",
        ];
    }
}
