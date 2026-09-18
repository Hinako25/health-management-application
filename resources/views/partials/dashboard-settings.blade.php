@php
    $user = auth()->user();
    $currentReward = $user->goal_reward ?? '未設定';
    $currentTotalGoals = config('goals.options')[$user->total_goals] ?? '未設定';
    $currentDailyTasks = config('dailytaskcontdown.options')[$user->daily_tasks] ?? '未設定';
    $currentCountdown = config('workcountdown.options')[$user->countdown_minutes] ?? '未設定';
@endphp

<div id="settings" class="bg-white p-2 rounded-lg shadow-md w-2/5 min-w-5/6 mx-auto border-2 border-gray-300 ">
  
  <form method="POST" action="{{ route('dashboard.update') }}" class="flex-1 space-y-6">
    @csrf

          <div class="flex flex-col gap-2 text-lg mt-5">
             <p class="text-xl font-bold">アカウント設定</p>
               <p class="text-lg font-bold mt-4">メールアドレス</p>
               {{-- email address --}}
               <flux:field variant="inline">
                 <flux:label class="text-sm font-normal text-right shrink-0 flex justify-between">今までのメールアドレス
                  <flux:input type="email" name="email" maxlength="25" value="{{ $user->email }}"
                    class="px-3 py-1.5 text-lg max-w-xs!" />
                  </flux:label>
                </flux:field>
               @error('email')
                  <p class="text-red-500 text-xs ml-32 -mt-3 mb-2">{{ $message }}</p>
               @enderror

               {{-- new email address --}}
               <flux:field variant="inline">
                 <flux:label class="text-sm font-normal text-right shrink-0 flex justify-between">新たなメールアドレス
                  <p class="text-sm">※変更しない場合は今のメールアドレスを入力してください</p>
                  <flux:input type="email" name="new_email" maxlength="25"
                    class="px-3 py-1.5 text-lg max-w-xs!" />
                  </flux:label>
                </flux:field>
               @error('new_email')
                  <p class="text-red-500 text-xs ml-32 -mt-3 mb-2">{{ $message }}</p>
               @enderror

              {{-- password --}}
              <p class="text-xl font-bold mt-5">パスワード</p>
               {{-- update password --}}
               <flux:field variant="inline">
                  <flux:label class="text-sm font-normal text-right shrink-0 flex justify-between">今までのパスワード
                    <flux:input type="password" name="password" maxlength="15"
                      class="px-3 py-1 text-lg max-w-xs!" />
                  </flux:label>
               </flux:field>
              @error('password')
                 <p class="text-red-500 text-xs ml-32 mt-3 mb-2">{{ $message }}</p>
              @enderror

              {{-- change password --}}
               <flux:field variant="inline">
                 <flux:label class="text-sm font-normal text-right shrink-0 flex justify-between">新たなパスワード
                   <p class="text-sm">※変更しない場合は今のパスワードを入力してください</p>
                   <flux:input type="password" name="change_password" maxlength="15"
                    class="px-3 py-1 text-lg max-w-xs!" />
                 </flux:label>
                </flux:field>
               @error('change_password')
                   <p class="text-red-500 text-xs ml-32 mt-3 mb-2">{{ $message }}</p>
               @enderror

               {{-- chnage password confirmation --}}
               <flux:field variant="inline">
                 <flux:label class="text-sm font-normal text-right shrink-0 flex justify-between">確認パスワード
                   <flux:input type="password" name="change_password_confirmation" maxlength="15"
                    class="px-3 py-1 text-lg max-w-xs!" />
                 </flux:label>
                </flux:field>
               @error('change_password_confirmation')
                   <p class="text-red-500 text-xs ml-32 mt-3 mb-2">{{ $message }}</p>
               @enderror

          </div>

          <div class="flex flex-col gap-3">
            <p class="text-xl font-bold">ストレッチ 設定</p>

            <div class="rounded-md border border-gray-200 bg-gray-50 p-4 text-sm">
              <p class="font-bold mb-2">現在の設定</p>
              <ul class="space-y-1">
                <li class="flex justify-between gap-4">
                  <span class="text-gray-600">仕事(勉強)時間</span>
                  <span>{{ $currentCountdown }}</span>
                </li>
                <li class="flex justify-between gap-4">
                  <span class="text-gray-600">目標日数</span>
                  <span>{{ $currentTotalGoals }}</span>
                </li>
                <li class="flex justify-between gap-4">
                  <span class="text-gray-600">一日の目標ストレッチ回数</span>
                  <span>{{ $currentDailyTasks }}</span>
                </li>
                <li class="flex justify-between gap-4">
                  <span class="text-gray-600">目標日数達成後の自分へのご褒美</span>
                  <span>{{ $currentReward }}</span>
                </li>

              </ul>
            </div>

              {{-- ストレッチ 時間 --}}
            <flux:field variant="inline">
              <flux:label class="text-sm font-normal text-right flex justify-between">仕事(勉強)時間
                <flux:select
                  name="countdown_minutes"
                  placeholder="仕事(勉強)時間を選択してください。"
                  size="lg"
                  class="max-w-xs! shrink-0"
                >
                  @foreach(config('workcountdown.options') as $minutes => $label)
                    <flux:select.option
                      class="text-lg"
                      value="{{ $minutes }}"
                      {{ $user->countdown_minutes == $minutes ? 'selected' : '' }}
                    >
                      {{ $label }}
                    </flux:select.option>
                  @endforeach
                </flux:select>
                @error('countdown_minutes')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
              </flux:label>
            </flux:field>
              {{-- 目標 日数 --}}
            <flux:field variant="inline">
              <flux:label class="text-sm font-normal text-right flex justify-between">目標日数
               <flux:select
                name="total_goals"
                placeholder=""
                size="lg"
                class="max-w-xs! shrink-0"
              >
                @foreach(config('goals.options') as $days => $label)
                  <flux:select.option
                    id="total-goals-{{ $days }}"
                    value="{{ $days }}"
                    {{ $user->total_goals == $days ? 'selected' : '' }}
                  >
                    {{ $label }}
                  </flux:select.option>
                @endforeach
              </flux:select>
             </flux:label>
             @error('total_goals')
               <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
             @enderror
            </flux:field>
            {{-- 一日のストレッチ回数 --}}
            <flux:field variant="inline">
              <flux:label class="text-sm font-normal text-right flex justify-between">一日の目標ストレッチ回数
              <flux:select
                name="daily_tasks"
                placeholder=""
                size="lg"
                class="max-w-xs! shrink-0"
              >
                @foreach(config('dailytaskcontdown.options') as $count => $label)
                  <flux:select.option
                    id="daily-tasks-{{ $count }}"
                    value="{{ $count }}"
                    {{ $user->daily_tasks == $count ? 'selected' : '' }}
                  >
                    {{ $label }}
                  </flux:select.option>
                @endforeach
              </flux:select>
              </flux:label>
            </flux:field>
            @error('daily_tasks')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="flex flex-col items-center my-5">
            <button
              name="save_settings"
              type="submit"
              class="items-center text-xl py-1 px-6 bg-green-500 hover:bg-green-700 opacity-75 text-white transition-colors rounded-md cursor-pointer"
            >
              保存
            </button>
            @if(session('success'))
             <div class="text-center my-2">
               <p class="text-green-600 opacity-75 text-lg">保存が成功しました!</p>
             </div>
            @endif
          </div>
  </form>
</div>
