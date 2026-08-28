<!DOCTYPE html>
<html lang="ja">
 <head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ __('目標設定') }} - {{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css'])
    @livewireStyles
 </head>
 <body class="min-h-screen w-full relative flex justify-center px-4 py-8">
  <img
        src="{{ asset('img/homegbdesign.png') }}"
        alt=""
        aria-hidden="true"
        class="fixed inset-0 w-full h-full object-cover -z-10 pointer-events-none"
  >
   <div class="w-full max-w-sm border-gray-300 border-2 rounded-md p-6 bg-white">
    <h1 class="font-bold text-center text-xl my-2">目標設定</h1>
    <p class="text-center text-green-500 text-sm my-2">目標を設定して継続に役立ててください。</p>
    <form class="flex flex-col gap-3" method="POST" action="{{ route('creategoals.submit') }}">
       @csrf
        <div class="flex flex-col gap-2 w-full text-left py-2">
          <p class="text-sm">・どのくらいの期間ストレッチを行いますか？</p>
          <select class="w-full border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2 text-sm" name="total_goals">
            @foreach(config('goals.options') as $days => $label)
              <option 
                 id="total-goals-{{ $days }}"
                 value="{{ $days }}" 
                 {{ auth()->user()->total_goals == $days ? 'selected' : '' }}
                >
                 {{ $label }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="flex flex-col gap-2 w-full text-left py-2">
           <p class="text-sm">・仕事(勉強)は何時間行いますか？</p>
           <p class="text-sm text-red-500">※仕事(勉強)時間の後にストレッチが始まります。</p>
           <select class="w-full border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2 text-sm" name="countdown_minutes">
             @foreach(config('workcountdown.options') as $minutes => $label)
               <option
                  value="{{ $minutes }}"
                  {{ auth()->user()->countdown_minutes == $minutes ? 'selected' : '' }}
                >
                  {{ $label }}
               </option>
             @endforeach
           </select>
        </div>
        <div class="flex flex-col gap-2 w-full text-left py-2">
          <p class="text-sm">・一日に何回ストレッチを行いますか？</p>
          <select class="w-full border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2 text-sm" name="daily_tasks">
             @foreach(config('dailytaskcontdown.options') as $count => $label)
                <option 
                   id="daily-tasks-{{ $count }}"
                   value="{{ $count }}" 
                   {{ auth()->user()->daily_tasks == $count ? 'selected' : '' }}
                >
                  {{ $label }}
                </option>
              @endforeach
          </select>
        </div>
        <div class="flex flex-col gap-2 text-left py-2">
          <p class="text-center font-bold text-sm">ストレッチ後、自分へのご褒美を書きましょう！</p>
          <textarea type="text" name="goal_reward" class="w-full border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2 text-sm min-h-24" placeholder="おいしいレストランに行く等" required maxlength="50"></textarea>
        </div>
        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white mt-2 px-3 py-2 rounded-md cursor-pointer text-sm">ストレッチを始めましょう！</button>
     </form>
 </div>
 </body>
</html>
