<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppealRequest;
use App\Models\Appeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppealController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        if (!$user->isWritingSuspended()) {
            return redirect()->route('feed')->with('info', "Ton compte n'est pas suspendu.");
        }

        $existingPending = Appeal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        return view('appeals.create', [
            'user' => $user,
            'existingPending' => $existingPending,
        ]);
    }

    public function store(StoreAppealRequest $request)
    {
        $user = Auth::user();

        if (!$user->isWritingSuspended()) {
            return redirect()->route('feed')->with('info', "Ton compte n'est pas suspendu.");
        }

        // Vérifier s'il a déjà une contestation en attente
        $existing = Appeal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return redirect()->route('appeals.create')
                ->with('error', 'Une contestation est déjà en cours d’examen pour ton compte.');
        }

        Appeal::create([
            'user_id' => $user->id,
            'message' => $request->input('message'),
            'status' => 'pending',
        ]);

        return redirect()->route('appeals.create')
            ->with('success', 'Ta contestation a été envoyée. Elle sera examinée par la modération.');
    }
}
