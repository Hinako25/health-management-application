<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DailyReportController extends Controller
{
    // dailyreport.js から送られた作業時間（秒）を、日付ごとに users.daily_chart_data に足していく
    public function dailyChartDataUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // 1回の作業の記録に加え、localStorage にある過去の履歴をまとめて送れるようにする（目標期間 210 日 × work/stretch）
            'entries' => ['required', 'array', 'max:500'],
            'entries.*.date' => ['required', 'date_format:Y-m-d'],
            'entries.*.type' => ['required', Rule::in(['work', 'stretch'])],
            'entries.*.seconds' => ['required', 'integer', 'min:1', 'max:86400'],
        ], [
            'entries.required' => '作業時間のデータがありません。',
            'entries.array' => '作業時間のデータの形式が正しくありません。',
            'entries.max' => '一度に保存できる作業時間のデータが多すぎます。',
            'entries.*.date.required' => '日付がありません。',
            'entries.*.date.date_format' => '日付の形式が正しくありません。',
            'entries.*.type.required' => '作業の種類がありません。',
            'entries.*.type.in' => '作業の種類が正しくありません。',
            'entries.*.seconds.required' => '作業時間がありません。',
            'entries.*.seconds.integer' => '作業時間は整数で送ってください。',
            'entries.*.seconds.min' => '作業時間は1秒以上で送ってください。',
            'entries.*.seconds.max' => '作業時間は1日（86400秒）以下で送ってください。',
        ]);

        $user = auth()->user();
        $dailyChartData = $user->daily_chart_data ?? [];

        foreach ($validated['entries'] as $entry) {
            $day = $dailyChartData[$entry['date']] ?? [];
            $day[$entry['type']] = ($day[$entry['type']] ?? 0) + $entry['seconds'];
            $dailyChartData[$entry['date']] = [
                'work' => $day['work'] ?? 0,
                'stretch' => $day['stretch'] ?? 0,
            ];
        }

        $user->update([
            'daily_chart_data' => $dailyChartData,
        ]);

        return response()->json([
            'daily_chart_data' => $user->daily_chart_data,
        ]);
    }
}
