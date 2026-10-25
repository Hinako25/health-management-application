<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'total_goals' => ['required', 'integer', Rule::in(array_keys(config('goals.options')))],
            'countdown_minutes' => ['required', 'integer', Rule::in(array_keys(config('workcountdown.options')))],
            'daily_tasks' => ['required', 'integer', Rule::in(array_keys(config('dailytaskcontdown.options')))],
            'goal_reward' => ['required', 'string', 'max:50'],
        ]);

        auth()->user()->update($validated);

        return redirect()->route('operation');

    }

    public function createGoalCardUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'daily_tasks' => ['required', 'integer'],

        ]);

        return redirect()->route('goalcard');
    }
}
