<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreateGoalsController extends Controller
{
    public function create(): View
    {
        return view('creategoals');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'total_goals' => ['required', 'integer'],
            'countdown_minutes' => ['required', 'integer'],
            'daily_tasks' => ['required', 'integer'],
            'goal_reward' => ['required', 'string', 'max:50'],
        ]);

        auth()->user()->update($validated);

        return redirect()->route('home')->with('auto_start_work', true);

    }
    public function createGoalCardUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'daily_tasks' => ['required', 'integer'],

        ]);
        return redirect()->route('goalcard');
    }
}