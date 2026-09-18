@php
    $minutes = auth()->user()->countdown_minutes ?? 60;
    $totalSeconds = $minutes * 60;
    $remainingSeconds = $totalSeconds;
@endphp
<div
    id="timer-root"
    data-sound-enabled="{{ auth()->user()->sound_enabled ? '1' : '0' }}"
    data-total-seconds="{{ $totalSeconds }}"
    data-remaining-seconds="{{ $remainingSeconds }}"
    data-auto-start="{{ session('auto_start_work') ? '1' : '0' }}"
    class="border-gray-300 border-2 rounded-md w-full flex flex-col min-h-0 bg-white"
>
    <div class="p-5 pb-3 shrink-0 flex items-center justify-between">
     <h2 class="text-base font-semibold text-[#1a1d23]">仕事(勉強)時間</h2>
     <div class="flex items-center gap-1">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.143 17.082a24.248 24.248 0 0 0 3.844.148m-3.844-.148a23.856 23.856 0 0 1-5.455-1.31 8.964 8.964 0 0 0 2.3-5.542m3.155 6.852a3 3 0 0 0 5.667 1.97m1.965-2.277L21 21m-4.225-4.225a23.81 23.81 0 0 0 3.536-1.003A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6.53 6.53m10.245 10.245L6.53 6.53M3 3l3.53 3.53" />
      </svg>
      <flux:switch
         id="timer-sound-toggle-button"
         :checked="(bool) auth()->user()->sound_enabled"
         align="left"
      />
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
      </svg>
     </div>
    </div>
    <div class="flex flex-col items-center gap-3 px-5 pb-5">
        <div
            id="timer-display"
            class="w-50 h-50 shrink-0 rounded-full flex items-center justify-center"
            style="background: conic-gradient(#cffce1 0deg var(--cut-angle, 0deg), #e5e7eb var(--cut-angle, 0deg) 360deg);"
        >
            <span id="timer-text" class="w-40 h-40 text-2xl font-bold bg-white rounded-full flex items-center justify-center tabular-nums">{{ sprintf('%02d:%02d:%02d', intdiv($totalSeconds, 3600), intdiv($totalSeconds % 3600, 60), $totalSeconds % 60) }}</span>
        </div>       
        <button type="button" id="start-work" class="w-full bg-green-500 hover:bg-green-700 opacity-75 transition-colors cursor-pointer text-white px-3 py-1 rounded-md">作業を始める</button>
        <button type="button" id="stop-work" disabled class="w-full bg-[#eceef1] hover:bg-[#d5d8dc] border-2 border-[#eceef1] transition-colors cursor-pointer px-3 py-1 rounded-md disabled:cursor-not-allowed ">作業を止める</button>
        <button
            type="button"
            id="start-stretch-countdown"
            class="w-full px-1 py-2 border-green-500 bg-white rounded-xl border-2 hover:bg-green-500 hover:text-white transition-colors cursor-pointer">
        
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="text-green-500 inline-block">
                <path d="M8 5v14l11-7z"/>
        </svg>  
         ストレッチを始める
        </button>
    </div>
</div>
