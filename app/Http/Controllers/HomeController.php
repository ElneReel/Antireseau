<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // Si l'utilisateur est déjà connecté, il accède directement au fil commun
        if (Auth::check()) {
            return redirect()->route('feed');
        }

        return view('pages.home');
    }

    public function rules()
    {
        return view('pages.rules');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function contact()
    {
        return view('pages.contact');
    }
}
