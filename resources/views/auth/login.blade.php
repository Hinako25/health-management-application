<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Login') }} - {{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css'])
        @livewireStyles
    </head>
    <body class="min-h-screen">
        <div class="min-h-screen flex flex-col md:flex-row">
            <div class="flex flex-1 flex-col items-center justify-center gap-8 px-4 py-8">
                <div class="text-center w-full max-w-full">
                    <img src="{{ asset('img/logo.png') }}" alt="Kenkou Task Manager" width="120" height="96" class="mx-auto block h-auto w-[200px] max-w-full object-contain">
                    <p class="mt-3 text-lg">Kenkou Task Managerへようこそ</p>
                </div>
                <div class="border-gray-300 border-2 w-full max-w-sm rounded-md p-6 bg-white">
                    <h1 class="font-bold text-center">ログイン</h1>
                    <p class="text-center my-2">ログイン情報を入力してください。</p>
                    <form class="flex flex-col gap-4 text-sm" method="POST" action="{{ route('login.submit') }}">
                        @csrf
                        <input class="border-2 rounded-md p-2 border-gray-300 hover:border-black" type="text" name="email" placeholder="Email" value="{{ old('email') }}" required>
                        <input class="border-2 rounded-md p-2 border-gray-300 hover:border-black" type="password" name="password" placeholder="Password" required>
                        <div class="flex items-center gap-2 ml-4">
                            <input type="checkbox" name="remember" id="remember" @checked(old('remember'))>
                            <label for="remember">ログイン状態を保持する</label>
                        </div>
                        <button type="submit" class="bg-green-500 hover:bg-green-700 opacity-75 text-white px-3 py-1 rounded-md cursor-pointer">ログイン</button>
                        @error('email')
                            <p class="text-red-600 text-sm border border-red-300 bg-red-50 rounded-md p-3" role="alert">{{ $message }}</p>
                        @enderror
                    </form>
                    <div class="mt-4 text-center">
                        <a method="GET" href="{{ route('createaccount') }}" class="text-green-500 hover:text-green-700 rounded-lg px-3 py-1 inline-block cursor-pointer">アカウント作成</a>
                    </div>
                </div>
            </div>
            <div class="relative w-full min-h-screen md:block md:w-[50%] overflow-hidden">
             <div class="absolute inset-0 w-full h-screen flex flex-col justify-center items-center">
                <div class="flex flex-col gap-4 w-2/3 ml-2">
                   <p class="text-left text-2xl font-bold text-green-600 block">あなたの健康習慣をサポートする<br>シンプルなタスク管理アプリ</p>
                   <br>
                   <div class="flex items-center gap-2">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 border-2 border-green-600 rounded-full ml-5 text-green-600">
                       <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                     </svg>
                      <div class="flex flex-col">
                        <p class="text-left text-xl text-green-600 mx-5 block">作業と休憩の時間を管理</p>
                        <p class="text-left text-sm text-green-500  mx-5 block">集中とストレッチのバランスをとる</p>
                     </div>
                    </div>
                    <br>
                    <div class="flex items-center gap-3">
                     <img src="{{ asset('img/strechimg.png') }}" alt="ストレッチ" class="w-12 h-12 ml-5 opacity-75 rounded-full">
                     <div class="flex flex-col">
                        <p class="text-left text-xl text-green-600 mx-5 block">3分のストレッチで肩こり・首の痛みを直す</p>
                        <p class="text-left text-sm text-green-500 mx-5 block">休憩時間に体をリフレッシュ<br>※画像はイメージです</p>
                     </div>
                    </div>
                    <br>
                    <div class="flex items-center gap-3">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 border-2 border-green-600 rounded-full ml-5 text-green-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                     </svg>
                     <div class="flex flex-col">    
                        <p class="text-left text-xl text-green-600 mx-5 block">習慣の記録を可視化</p>
                        <p class="text-left text-sm text-green-500  mx-5 block">続けるほどに変化がわかる</p>
                     </div>
                    </div>
                </div>
             </div>
             <img
                    src="{{ asset('img/bgdesign.png') }}"
                    alt=""
                    class="absolute inset-0 w-full h-full object-fit pointer-events-none -z-10"
             >
            </div>
        </div>
    </body>
</html>
