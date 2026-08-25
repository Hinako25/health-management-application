<div id="settings" class="bg-white p-4 rounded-lg shadow-md w-4/5 mx-auto border-2 border-gray-300">
  <form method="POST" action="{{ route('dashboard.update') }}" class="flex-1 space-y-6">
    @csrf

          <div class="flex flex-col gap-2 text-lg mt-5">
             <p class="text-2xl font-bold text-gray-700">ユーザー設定</p>
               <p class="text-lg text-gray-700 font-bold mt-4">ユーザーのメールアドレス</p>
               {{-- email address --}}
               <div class="flex items-center gap-2 mb-4">
                   <label class="block text-right text-sm text-gray-700 shrink-0">新たなメールアドレス:</label>
                   <input type="email" name="email"
                    class="flex-1 border border-gray-300 rounded px-3 py-1.5 text-lg text-black focus:outline-none focus:border-blue-400" />
               </div>
               @error('email')
                  <p class="text-red-500 text-xs ml-32 -mt-3 mb-2">{{ $message }}</p>
               @enderror

              {{-- password --}}
              <p class="text-xl text-gray-700 font-bold mt-5">ユーザーのパスワード</p>
               {{-- update password --}}
               <div class="flex items-center gap-2 mb-4">
                   <label class="block text-right text-sm text-gray-700 shrink-0">今までのパスワード:</label>
                   <input type="password" name="password"
                    class="flex-1 border border-gray-300 rounded px-3 py-1.5 text-lg text-black focus:outline-none focus:border-blue-400 w-40" />
               </div>
              @error('password')
                 <p class="text-red-500 text-xs ml-32 -mt-3 mb-2">{{ $message }}</p>
              @enderror

              {{-- update password --}}
               <div class="flex items-center gap-2 mb-4">
                   <label class="block text-right text-sm text-black shrink-0">新たなパスワード：</label>
                   <input type="password" name="passwordConfirm"
                    class="flex-1 border border-gray-300 rounded px-3 py-1.5 text-lg text-black focus:outline-none focus:border-blue-400" />
               </div>
               @error('passwordConfirm')
                   <p class="text-red-500 text-xs ml-32 -mt-3 mb-2">{{ $message }}</p>
               @enderror

          </div>

          <div class="flex flex-col gap-2">
              <p class="text-2xl font-bold text-gray-700">仕事(勉強)時間 設定</p>
              <flux:select
                name="countdown_minutes"
                label="仕事(勉強)時間"
                description="仕事(勉強)時間を選択してください。値は分単位で保存されます。"
                placeholder="仕事(勉強)時間を選択してください。"
                size="lg"
                class="max-w-xs! mt-4"
              >
                @foreach(config('workcountdown.options') as $minutes => $label)
                   <flux:select.option
                       class="text-lg"
                       value="{{ $minutes }}"
                       {{ auth()->user()->countdown_minutes == $minutes ? 'selected' : '' }}  
                    >
                    {{ $label }}
                    </flux:select.option>
                @endforeach
              </flux:select>
              @error('countdown_minutes')
                 <p class="text-red-500 text-xs ml-32 -mt-3 mb-2">{{ $message }}</p>
              @enderror
          </div>
          <div class="flex flex-col items-center my-5">
        
            <button
              name="save_settings"
              type="submit"
              class="items-center text-2xl py-1 px-6 bg-green-500 hover:bg-green-700 opacity-75 text-white transition-colors rounded-md cursor-pointer"
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
