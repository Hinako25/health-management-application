<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;

class WorkController extends Controller
{
    public function work(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'countdown_minutes' => ['required', 'integer', Rule::in(array_keys(config('workcountdown.options')))],
        ]);

        auth()->user()->update([
            'countdown_minutes' => $validated['countdown_minutes'],
        ]);
        return redirect()->route('home');
    }
    public function updateSoundEnabled(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sound_enabled' => ['required', 'boolean'],
        ]);
        $user = auth()->user();
        $user->update([
            'sound_enabled' => $validated['sound_enabled'],
        ]);
        return response()->json([
            'sound_enabled' => (int) $user->sound_enabled,
        ]);
    }
}

