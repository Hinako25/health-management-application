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
    class="border-gray-300 border-2 rounded-md w-full flex flex-col min-h-0 bg-white dark:bg-black"
>
    <div class="p-5 pb-3 shrink-0 flex items-center justify-between">
     <h2 class="text-base font-semibold text-[#1a1d23] dark:text-white">仕事(勉強)時間</h2>
     <div class="flex items-center gap-1">
      <img src="{{ asset('img/x-bell.png') }}" alt="タイマー音:ON" class="inline-block w-4 h-4 rounded-full dark:bg-white"/>
      <flux:switch
         id="timer-sound-toggle-button"
         :checked="(bool) auth()->user()->sound_enabled"
         align="left"
      />
      <img src="{{ asset('img/bell.png') }}" alt="タイマー音:OFF" class="inline-block w-4 h-4 rounded-full dark:bg-white"/>
     </div>
    </div>
    <div class="flex flex-col items-center gap-3 px-5 pb-5">
        <div
            id="timer-display"
            class="w-50 h-50 shrink-0 rounded-full flex items-center justify-center"
            style="background: conic-gradient(#cffce1 0deg var(--cut-angle, 0deg), #e5e7eb var(--cut-angle, 0deg) 360deg);"
        >
            <span id="timer-text" class="w-40 h-40 text-2xl font-bold bg-white rounded-full flex items-center justify-center tabular-nums dark:bg-black dark:text-white">{{ sprintf('%02d:%02d:%02d', intdiv($totalSeconds, 3600), intdiv($totalSeconds % 3600, 60), $totalSeconds % 60) }}</span>
        </div>       
        <button type="button" id="start-work" class="w-full bg-green-500 hover:bg-green-700 opacity-75 transition-colors cursor-pointer text-white px-3 py-1 rounded-md">作業を始める</button>
        <button type="button" id="stop-work" disabled class="w-full bg-[#eceef1] hover:bg-[#d5d8dc] border-2 border-[#eceef1] transition-colors cursor-pointer px-3 py-1 rounded-md disabled:cursor-not-allowed dark:bg-black dark:text-white">作業を止める</button>
        <button
            type="button"
            id="start-stretch-countdown"
            class="w-full px-1 py-2 border-green-500 bg-white rounded-xl border-2 hover:bg-green-500 hover:text-white transition-colors cursor-pointer dark:bg-black dark:text-white"
        >
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="text-green-500 inline-block dark:text-white">
                <path d="M8 5v14l11-7z"/>
        </svg>  
         ストレッチを始める
        </button>
    </div>
</div>
