<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'target_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $viewer = Auth::user();
        $targetId = (int) $request->input('target_id');

        if ($viewer->id === $targetId) {
            return redirect()->back()->with('error', 'Tu ne peux pas te bloquer toi-même.');
        }

        Block::firstOrCreate([
            'blocker_id' => $viewer->id,
            'blocked_id' => $targetId,
        ]);

        return redirect()->back()->with('info', 'Compte bloqué. Ses publications et interactions n’apparaîtront plus pour toi.');
    }

    public function destroy(int $targetId)
    {
        $viewer = Auth::user();

        Block::where('blocker_id', $viewer->id)
            ->where('blocked_id', $targetId)
            ->delete();

        return redirect()->back()->with('info', 'Compte débloqué.');
    }
}
