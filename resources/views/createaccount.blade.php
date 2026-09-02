<!DOCTYPE html>
<html lang="en">
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
            </div>
            <div class="border-gray-300 border-2 w-2/3 max-w-xl rounded-md p-4 bg-white">
                <form class="flex flex-col gap-4" method="POST" action="{{ route('createaccount.submit') }}">
                    @csrf
                    <h1 class="font-bold text-center text-xl">アカウント作成</h1>
                    <p class="text-center my-2 text-sm">アカウント作成情報を入力してください。</p>

                    {{-- メールアドレス --}}
                    <div class="w-full text-left text-sm">
                        <label class="mb-2 block">メールアドレス</label>
                        <input type="email" name="email" value="{{ old('email') }}" maxlength="25"
                            class="w-full border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2" required />
                    </div>
                    @error('email')
                        <p class="text-red-600 text-sm border border-red-300 bg-red-50 rounded-md p-3 -mt-2" role="alert">{{ $message }}</p>
                    @enderror

                    {{-- パスワード --}}
                    <div class="w-full text-left text-sm">
                        <label class="mb-2 block">パスワード</label>
                        <input type="password" name="password" maxlength="15"
                            class="w-full border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2" required />
                    </div>
                    @error('password')
                        <p class="text-red-600 text-sm border border-red-300 bg-red-50 rounded-md p-3 -mt-2" role="alert">{{ $message }}</p>
                    @enderror

                    {{-- パスワード確認 --}}
                    <div class="w-full text-left">
                        <label class="text-sm mb-2 block">パスワード確認</label>
                        <input type="password" name="password_confirmation" maxlength="15"
                            class="w-full border-gray-300 hover:border-black transition-colors border-2 rounded-md p-2" required />
                    </div>

                    @if(session('success'))
                        <p class="text-green-600 text-sm border border-green-300 bg-green-50 rounded-md p-3" role="status">{{ session('success') }}</p>
                    @endif

                    {{-- ボタン --}}
                    <div class="flex justify-evenly sm:flex-row gap-2 mt-2 text-lg">
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white rounded-md px-4 py-2 transition-colors cursor-pointer">
                            作成
                        </button>
                        <button type="button"
                            onclick="location.href='{{ route('login') }}'"
                            class="border border-gray-400 rounded-md text-gray-600 px-4 py-2 hover:bg-gray-50 transition-colors cursor-pointer">
                            キャンセル
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="relative w-full min-h-screen md:block md:w-[50%] overflow-hidden">
            <div class="absolute inset-0 w-full h-screen flex flex-col justify-center items-center">
                <div class="flex flex-col gap-4 w-2/3">
                  <p class="text-center text-xl font-bold text-yellow-500 opacity-75 block">仕事や勉強の合間に少しストレッチを行うことで<br>肩や首の痛みを改善しませんか？</p>
                   <br>
                   <p class="text-left text-sm mx-5 block">
                    このサイトは設定した期間内で仕事や勉強時間を計り、休憩時間にストレッチを行うサイトです。
                    <br>
                    実際に指定したストレッチが3分程度あり、その後自分の指定した時間勉強や仕事を好きなタイミングで行うことができます。
                    </p>
                    <br>
                    <div class="w-full h-full aspect-3/2 rounded-sm flex justify-center items-center relative z-10">
                  <img src="{{ asset('img/trialimg.png') }}" alt="ストレッチ画像" class="w-full h-full object-contain object-center">
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
