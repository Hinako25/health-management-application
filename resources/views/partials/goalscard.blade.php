@php
    $dailyTasks = $dailyTasks ?? auth()->user()->daily_tasks ?? 0;
    $completedTasks = $completedTasks ?? auth()->user()->completed_tasks ?? 0;
@endphp

<div class="border-gray-300 border-2 rounded-md w-full flex flex-col min-h-0 bg-white p-5 dark:bg-black">

    <div class="pb-3 shrink-0">
        <h2 class="text-base font-semibold text-[#1a1d23] dark:text-white">今日の成果</h2>
    </div>

    <flux:checkbox.group
        id="goals-card-root"
        data-daily-tasks="{{ $dailyTasks }}"
        data-completed-tasks="{{ $completedTasks }}"
        class="shrink-0 flex flex-col items-start gap-3 w-full dark:text-white"
        wire:model="goals-card-root"
        wire:click="toggleGoalCard"
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
    </flux:checkbox.group>
</div>
