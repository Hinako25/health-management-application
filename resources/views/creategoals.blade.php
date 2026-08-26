<!DOCTYPE html>
<html lang="ja">
 <head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ __('目標設定') }} - {{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css'])
    @livewireStyles
 </head>
 <body class="w-full h-full mt-5 flex items-start relative">
  <img
        src="{{ asset('img/homegbdesign.png') }}"
        alt=""
        aria-hidden="true"
        class="absolute inset-0 w-full h-full object-cover -z-10 pointer-events-none"
    >
  <div class="border-gray-300 border-2 rounded-md p-8 bg-white">
    <h1 class="font-bold text-center text-2xl my-2">目標設定</h1>
    <p class="text-center text-green-500 my-2">目標を設定して継続に役立ててください。</p>
    <form class="flex flex-col gap-4 m-4" method="POST" action="{{ route('creategoals.submit') }}">
       @csrf
        <div class="flex flex-col gap-2 w-full text-left">
          <p class="my-2">・どのくらいの期間ストレッチを行いますか？</p>
          <select class="border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2" name="total_goals">
            @foreach(config('goals.options') as $days => $label)
              <option 
                 id="total-goals-{{ $days }}"
                 class="text-lg"
                 value="{{ $days }}" 
                 {{ auth()->user()->total_goals == $days ? 'selected' : '' }}
                >
                 {{ $label }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="flex flex-col gap-2 w-full text-left">
           <p class="my-2">・仕事(勉強)のうち何時間ごとにストレッチを行いますか？</p>
           <select class="border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2 mx-2" name="countdown_minutes">
             @foreach(config('workcountdown.options') as $minutes => $label)
               <option
                  class="text-lg"
                  value="{{ $minutes }}"
                  {{ auth()->user()->countdown_minutes == $minutes ? 'selected' : '' }}
                >
                  {{ $label }}
               </option>
             @endforeach
           </select>
        </div>
        <div class="flex flex-col gap-2 w-full text-left">
          <p class="my-2">・一日に何回ストレッチを行いますか？</p>
          <select class="border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2 mx-2" name="daily_tasks">
             @foreach(config('dailytaskcontdown.options') as $count => $label)
                <option 
                   id="daily-tasks-{{ $count }}"
                   class="text-lg" 
                   value="{{ $count }}" 
                   {{ auth()->user()->daily_tasks == $count ? 'selected' : '' }}
                >
                  {{ $label }}
                </option>
              @endforeach
          </select>
        </div>
        <div class="flex flex-col gap-2 text-left my-3">
          <p class="text-center font-bold my-2">ストレッチ完走後の自分へのご褒美を書きましょう！</p>
          <textarea class="border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2" name="reward" placeholder="おいしいレストランに行く、温泉に行く、遊びに行く等" required maxlength="255"></textarea>
        </div>
        <button type="submit" class="bg-green-500 hover:bg-green-700 opacity-75 text-white mt-5 px-3 py-1 rounded-md cursor-pointer">目標設定</button>
     </form>
 </div>
 </body>
</html>
