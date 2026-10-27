<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function completedTasks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'completed_tasks' => ['required', 'integer', 'min:0'],
        ]);

        $user = auth()->user();
        $user->update([
            'completed_tasks' => $validated['completed_tasks'],
        ]);

        return response()->json([
            'completed_tasks' => $user->completed_tasks ?? 0,
        ]);
    }
}
