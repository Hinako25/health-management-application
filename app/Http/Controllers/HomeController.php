<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();
        $today = now()->toDateString();

        if ($user->last_reset_date !== $today) {
            $user->update([
                'completed_tasks' => 0,
                'last_reset_date' => $today,
            ]);
        }

        $minutes = $user->countdown_minutes ?? 60;
        $totalSeconds = $minutes * 60;

        return view('home', [
            'minutes' => $minutes,
            'totalSeconds' => $totalSeconds,
            'remainingSeconds' => $totalSeconds,
            'completedTasks' => $user->completed_tasks ?? 0,
            'tasks' => config('stretchtasks.items'),
        ]);
    }
}
