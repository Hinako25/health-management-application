<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function completedTasks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'completed_tasks' => ['required', 'integer', 'min:0'],
        ]);

        $user = auth()->user();
        $rewardedPoints = 0;

        // 2 つのタブから同時に送られても、ポイントが二重に足されないように行をロックする
        DB::transaction(function () use ($user, $validated, &$rewardedPoints) {
            $locked = User::query()->lockForUpdate()->findOrFail($user->id);
            $locked->update([
                'completed_tasks' => $validated['completed_tasks'],
            ]);

            $dailyTasks = $locked->daily_tasks ?? 0;
            $today = today();

            // 今日はじめて目標回数に達したときだけ、連続日数を数えて毎日のポイントと連続ボーナスを足す
            if ($dailyTasks > 0 && $locked->completed_tasks >= $dailyTasks && ! $locked->last_streak_date?->isSameDay($today)) {
                $streakDays = $locked->last_streak_date?->isSameDay($today->copy()->subDay()) ? $locked->streak_days + 1 : 1;
                $rewardedPoints = (int) config('streakpoints.daily', 0) + (config('streakpoints.rewards')[$streakDays] ?? 0);

                $locked->update([
                    'streak_days' => $streakDays,
                    'last_streak_date' => $today,
                    'points' => $locked->points + $rewardedPoints,
                ]);
            }

            $user->setRawAttributes($locked->getAttributes(), true);
        });

        return response()->json([
            'completed_tasks' => $user->completed_tasks ?? 0,
            'streak_days' => $user->currentStreakDays(),
            'points' => $user->points,
            'rewarded_points' => $rewardedPoints,
        ]);
    }
}
