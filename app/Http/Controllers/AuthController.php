<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('feed');
        }

        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'member',
                'status' => 'active',
                'terms_accepted_at' => Carbon::now('UTC'),
                'is_adult_confirmed' => true,
            ]);

            Profile::create([
                'user_id' => $user->id,
                'handle' => $validated['handle'],
                'handle_lower' => strtolower($validated['handle']),
                'display_name' => $validated['display_name'] ?? null,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('feed')->with('success', 'Bienvenue sur Insociable.');
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('feed');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis ne correspondent à aucun compte actif.'],
            ]);
        }

        $user = Auth::user();

        if ($user->isBanned()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Ce compte a été suspendu par l’équipe de modération pour non-respect des règles du lieu.'],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('feed'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Tu es déconnecté.');
    }
}
