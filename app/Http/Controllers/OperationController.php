<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class OperationController extends Controller
{
    public function show(): View
    {
        return view('operation');
    }
}
