<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;


class CreateAccountController extends Controller
{
    public function create(): View
    {
        return view('createaccount');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->max(15)->numbers()->letters()->symbols(),
            ],
        ], [
            'email.unique' => 'このメールアドレスは既に登録されています。',
            'email.required' => 'メールアドレスを入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'password.confirmed' => 'パスワードが一致しません。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.max' => 'パスワードは15文字以内で入力してください。',
            'password.numbers' => 'パスワードには数字を含めてください。',
            'password.letters' => 'パスワードには英字を含めてください。',
            'password.symbols' => 'パスワードには記号を含めてください。',
        ]);

        User::create([
            'name' => $validated['email'],
            'email' => $validated['email'],
            'password' => $validated['password'], // hashed キャストで自動ハッシュ
        ]);

        return redirect()->route('login')->with('success', 'アカウントを作成しました。');
    }     
}
