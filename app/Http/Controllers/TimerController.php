<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TimerController extends Controller
{
    public function show(): View
    {
        $minutes = auth()->user()->countdown_minutes ?? 60;
        $totalSeconds = $minutes * 60;

        return redirect()->route('workcountcard', [
            'minutes' => $minutes,
            'totalSeconds' => $totalSeconds,
            'remainingSeconds' => $totalSeconds,
        ]);

    }

}

   