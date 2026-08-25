<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>アカウント作成</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen">
    <div class="min-h-screen flex flex-col md:flex-row">
        <div class="flex flex-1 flex-col items-center justify-center gap-8 px-4 py-8">
            <div class="text-center w-full max-w-full">
                <img src="{{ asset('img/logo.png') }}" alt="Kenkou Task Manager" width="120" height="96" class="mx-auto block h-auto w-[200px] max-w-full object-contain">
                <p class="mt-3 text-lg">Kenkou Task Manager</p>
            </div>
            <div class="border-gray-300 border-2 w-full max-w-xl rounded-md p-8 bg-white">
                <form class="flex flex-col gap-4" method="POST" action="{{ route('createaccount.submit') }}">
                    @csrf
                    <h1 class="font-bold text-center text-2xl">アカウント作成</h1>
                    <p class="text-center my-2">アカウント作成情報を入力してください。</p>

                    {{-- メールアドレス --}}
                    <div class="w-full text-left">
                        <label class="text-lg mb-2 block">メールアドレス</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2" required />
                    </div>
                    @error('email')
                        <p class="text-red-600 text-sm border border-red-300 bg-red-50 rounded-md p-3 -mt-2" role="alert">{{ $message }}</p>
                    @enderror

                    {{-- パスワード --}}
                    <div class="w-full text-left">
                        <label class="text-lg mb-2 block">パスワード</label>
                        <input type="password" name="password"
                            class="w-full border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2" required />
                    </div>
                    @error('password')
                        <p class="text-red-600 text-sm border border-red-300 bg-red-50 rounded-md p-3 -mt-2" role="alert">{{ $message }}</p>
                    @enderror

                    {{-- パスワード確認 --}}
                    <div class="w-full text-left">
                        <label class="text-lg mb-2 block">パスワード確認</label>
                        <input type="password" name="password_confirmation"
                            class="w-full border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2" required />
                    </div>
                    @error('password_confirmation')
                        <p class="text-red-600 text-sm border border-red-300 bg-red-50 rounded-md p-3 -mt-2" role="alert">{{ $message }}</p>
                    @enderror

                    @if(session('success'))
                        <p class="text-green-600 text-sm border border-green-300 bg-green-50 rounded-md p-3" role="status">{{ session('success') }}</p>
                    @endif

                    {{-- ボタン --}}
                    <div class="flex flex-col sm:flex-row justify-center gap-4 mt-2">
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white rounded-md px-8 py-2 transition-colors cursor-pointer">
                            アカウント作成
                        </button>
                        <button type="button"
                            onclick="location.href='{{ route('login') }}'"
                            class="border border-gray-400 rounded-md text-gray-600 text-lg font-bold px-8 py-2 hover:bg-gray-50 transition-colors cursor-pointer">
                            キャンセル
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="hidden md:block md:w-[50%] md:h-screen overflow-hidden">
            <img
                src="{{ asset('img/bgdesign.png') }}"
                alt=""
                class="h-full w-full object-cover object-center"
            >
        </div>
    </div>
</body>
</html>
