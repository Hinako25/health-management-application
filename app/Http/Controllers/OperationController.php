<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OperationController extends Controller
{
    public function show(): View
    {
        return view('operation');
    }

    public function start(): RedirectResponse
    {
        return redirect()->route('home')->with('auto_start_work', true);
    }
}
