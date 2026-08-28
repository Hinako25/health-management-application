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
            'password' => ['required', Password::default(), 'confirmed'],
        ], [
            'email.unique' => 'このメールアドレスは既に登録されています。',
            'email.required' => 'メールアドレスを入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'password.confirmed' => 'パスワードが一致しません。',
        
        ]);

        User::create([
            'name' => $validated['email'],
            'email' => $validated['email'],
            'password' => $validated['password'], // hashed キャストで自動ハッシュ
        ]);

        return redirect()->route('login')->with('success', 'アカウントを作成しました。');
    }     
}
