@php
    $totalSeconds = 0;
    $remainingSeconds = 0;
@endphp
<div
    id="timer-root"
    data-sound-enabled="{{ auth()->user()->sound_enabled ? '1' : '0' }}"
    data-total-seconds="{{ $totalSeconds }}"
    data-remaining-seconds="{{ $remainingSeconds }}"
    class="border-gray-300 border-2 rounded-md w-full flex flex-col min-h-0 bg-white"
>
    <div class="p-5 pb-3 shrink-0">
     <h2 class="text-base font-semibold text-[#1a1d23]">仕事(勉強)時間</h2>
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
        <div class="w-full flex items-center justify-center flex-col gap-2">
         <button 
            data-sound-enabled="1"
            type="button" 
            id="timer-sound-toggle" 
            class="w-full text-white bg-green-500 opacity-75 hover:bg-green-700 cursor-pointer transition-colors px-3 py-1 rounded-md {{ auth()->user()->sound_enabled ? '' : 'hidden' }}"
         >
            <img src="{{ asset('img/bell.png') }}" alt="タイマー音:ON" class="inline-block w-4 h-4"/>
            タイマー音:ON
         </button>
         <button 
            data-sound-enabled="0"
            type="button" 
            id="timer-sound-toggle-off" 
            class="w-full bg-[#eceef1] hover:bg-[#d5d8dc] trensition-colors cursor-pointer px-3 py-1 rounded-md {{ auth()->user()->sound_enabled ? 'hidden' : '' }}"
         >
          <img src="{{ asset('img/x-bell.png') }}" alt="タイマー音:OFF" class="w-4 h-4 inline-block"/>
           タイマー音:OFF 
         </button>
        </div>
        <button type="button" id="stop-work" disabled class="w-full bg-[#eceef1] hover:bg-[#d5d8dc] transition-colors cursor-pointer px-3 py-1 rounded-md disabled:cursor-not-allowed">作業を止める</button>
        <button
            type="button"
            id="start-stretch-countdown"
            class="w-full px-1 py-2 border-green-500 bg-white rounded-xl border-2 hover:bg-green-500 hover:text-white transition-colors cursor-pointer"
        >
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="text-green-500 inline-block">
                <path d="M8 5v14l11-7z"/>
        </svg>  
         ストレッチを始める
        </button>
    </div>
</div>
