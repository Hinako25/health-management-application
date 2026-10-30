@php
    $dailyTasks = $dailyTasks ?? auth()->user()->daily_tasks ?? 0;
    $completedTasks = $completedTasks ?? auth()->user()->completed_tasks ?? 0;
    // 達成率（%）。目標回数が 0 のときは 0、100 を上限にする（dailyreport.js の calcAchievementRate と同じ計算）
    $achievementRate = $dailyTasks > 0 ? min(100, intdiv((int) $completedTasks * 100, (int) $dailyTasks)) : 0;
@endphp

<div class="border-gray-300 border-2 rounded-md w-full flex flex-col min-h-0 bg-white p-5">

    <div class="pb-3 shrink-0">
        <h2 class="text-base font-semibold text-[#1a1d23]">今日の成果</h2>
    </div>

    <flux:checkbox.group
        id="goals-card-root"
        data-daily-tasks="{{ $dailyTasks }}"
        data-completed-tasks="{{ $completedTasks }}"
        class="shrink-0 flex flex-col items-start gap-3 w-full"
    >
        <flux:field variant="inline" class="w-full pl-5 py-2">
            <flux:checkbox value="daily_tasks_completed" />
            <flux:label class="text-lg tabular-nums">
                今日のストレッチ回数:<span id="goals-completed-display">{{ $completedTasks }}/{{ $dailyTasks }}</span>
            </flux:label>
        </flux:field>

        <flux:field variant="inline" class="w-full pl-5 py-2">
            <flux:checkbox value="total_time_worked" />
            <flux:label class="text-lg tabular-nums">
                総作業時間:<span id="goals-total-time-display">0分</span>
            </flux:label>
        </flux:field>

        <div class="w-full pl-5 py-2">
            <p class="text-sm tabular-nums">
                達成率:<span id="goals-achievement-rate-display">{{ $achievementRate }}%</span>
            </p>
            <div class="mt-2 h-2 w-full rounded-full bg-gray-200" role="progressbar" aria-label="達成率" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $achievementRate }}" id="goals-achievement-rate-track">
                <div id="goals-achievement-rate-bar" class="h-2 rounded-full bg-green-500" style="width: {{ $achievementRate }}%"></div>
            </div>
        </div>

        <div class="w-full pl-5 py-2">
            <p class="text-sm tabular-nums">
                連続達成:<span id="goals-streak-display">{{ auth()->user()->currentStreakDays() }}</span>日
            </p>
        </div>
    </flux:checkbox.group>

    {{-- 目標回数を達成してポイントをもらったときに dailyreport.js が表示する --}}
    <flux:card id="goals-reward-card" class="hidden w-full mt-3">
        <flux:heading>ポイントがたまりました！</flux:heading>
        <flux:text id="goals-reward-message" class="mt-2 mb-4"></flux:text>
        <flux:button id="goals-reward-close" variant="outline" size="sm">閉じる</flux:button>
    </flux:card>
</div>
