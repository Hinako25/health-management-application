<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DailyReportController extends Controller
{
    public function show(): View
    {
        $dailyChartData = auth()->user()->daily_chart_data ?? [];
        $totalgoals = new Day;
        $totalgoals->daily_chart_data = $dailyChartData;
        $totalgoals->save();

        return view('dailychart', [
            'totalgoals' => $totalgoals,
        ]);
    }
}