@php
    $tasks = $tasks ?? config('stretchtasks.items');
    $taskTotalSeconds = collect($tasks)->sum('countdown_seconds');
    $completedTasks = $completedTasks ?? auth()->user()->completed_tasks ?? 0;
@endphp

<div
    id="task-countdown-minute-root"
    data-tasks='@json($tasks)'
    data-total-seconds="{{ $taskTotalSeconds }}"
    data-completed-tasks="{{ $completedTasks }}"
    class="border-gray-300 border-2 rounded-md w-full flex flex-col min-h-0 bg-white p-5 dark:bg-black"
>

    <div class="flex items-center justify-between w-full pb-3 shrink-0">
        <h2 class="text-base font-semibold text-[#1a1d23] dark:text-white">ストレッチ　タスク</h2>
        <button
            type="button"
            id="start-task-countdown-minute"
            class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-green-500 opacity-75 hover:bg-green-600 opacity-75 transition-colors cursor-pointer text-white"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M8 5v14l11-7z"/>
            </svg>
            Play
        </button>
    </div>

    <span id="task-countdown-display" class="text-2xl font-bold tabular-nums shrink-0 px-5 pb-3 dark:text-white">00:00</span>


    <div class="flex flex-col divide-y divide-[#f0f1f3] w-full flex-1 min-h-0 overflow-y-auto">
        @foreach ($tasks as $task)
            <button
                type="button"
                class="task-item group flex items-center gap-3 w-full px-5 py-2 text-2xl text-left hover:bg-[#f8f9fa] transition-colors disabled:opacity-50 "
                data-task-id="{{ $task['id'] }}"
                data-countdown-seconds="{{ $task['countdown_seconds'] }}"
            >
                <span class="task-name flex-1 text-lg transition-colors group-[.is-checked]:line-through group-[.is-checked]:text-[#8b919a] dark:text-gray-300">{{ $task['name'] }}</span>
                <span class="block bg-red-200 opacity-75 text-red-600 text-lg tabular-nums px-2 py-1 rounded-xl">{{ $task['part'] }}</span>
                <span class="block bg-orange-200 opacity-75 text-orange-600 text-lg tabular-nums px-2 py-1 rounded-xl">{{ $task['effect'] }}</span>
                <span class="block bg-green-200 opacity-75 text-green-600 text-lg tabular-nums px-2 py-1 rounded-xl transition-colors">{{ $task['duration'] }}</span>
            </button>
        @endforeach
    </div>
</div>
