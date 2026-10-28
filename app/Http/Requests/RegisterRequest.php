<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:filter', 'max:255', 'unique:users,email'],
            'handle' => [
                'required',
                'string',
                'min:3',
                'max:24',
                'regex:/^[a-zA-Z0-9_]+$/',
                function ($attribute, $value, $fail) {
                    $lower = strtolower($value);
                    if (\App\Models\Profile::where('handle_lower', $lower)->exists()) {
                        $fail("Ce pseudonyme (@{$value}) est déjà utilisé.");
                    }
                },
            ],
            'display_name' => ['nullable', 'string', 'max:60'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'terms_accepted' => ['accepted'],
            'is_adult' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "L'adresse email n'est pas valide.",
            'email.unique' => "Cette adresse email est déjà utilisée.",
            'handle.required' => "Le pseudonyme est obligatoire.",
            'handle.min' => "Le pseudonyme doit comporter au moins 3 caractères.",
            'handle.max' => "Le pseudonyme ne peut pas dépasser 24 caractères.",
            'handle.regex' => "Le pseudonyme ne peut contenir que des lettres sans accent, des chiffres et le tiret bas (_).",
            'password.required' => "Le mot de passe est obligatoire.",
            'password.confirmed' => "La confirmation du mot de passe ne correspond pas.",
            'terms_accepted.accepted' => "Tu dois accepter les règles du lieu.",
            'is_adult.accepted' => "Tu dois confirmer avoir au moins 18 ans pour participer à cette version.",
        ];
    }
}
