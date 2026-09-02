<div
    id="daily-chart-root"
    data-user-id="{{ auth()->id() }}"
    data-total-goals="{{ auth()->user()->total_goals ?? 0 }}"
    data-daily-tasks="{{ auth()->user()->daily_tasks ?? 0 }}"
    data-created-at="{{ auth()->user()->created_at->timestamp }}"
    data-chart-data='@json(auth()->user()->daily_chart_data ?? [])'
    class="border-gray-300 border-2 rounded-md w-full flex flex-col min-h-0 bg-white overflow-hidden"
>
    <div class="p-4 pb-2 shrink-0">
        <h2 class="text-base font-semibold text-[#1a1d23]">作業時間</h2>
        <canvas id="daily-chart-canvas" class="mt-2 block h-24 w-full max-h-1/3"></canvas>
    </div>
    @if(auth()->user()->daily_chart_data)
     @error('daily_chart_data')
        <p class="text-red-500 text-sm border border-red-300 bg-red-50 rounded-md p-3" role="alert">{{ $error.messages }}</p>
     @enderror
   @endif
</div>
