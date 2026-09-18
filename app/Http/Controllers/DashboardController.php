<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function show(): View
    {
        return view('dashboard');
    }

    public function dashboardUpdate(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'email' => ['nullable', 'email'],
            'new_email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'required_with:change_password', 'current_password:web', Password::min(8)->max(15)->numbers()->letters()->symbols()],
            'change_password' => ['nullable', 'required_with:password', Password::min(8)->max(15)->numbers()->letters()->symbols(),
            ],

            'change_password_confirmation' => ['nullable', 'required_with:change_password', 'same:change_password'],
            'countdown_minutes' => ['required', 'integer', Rule::in(array_keys(config('workcountdown.options')))],
            'total_goals' => ['required', 'integer', Rule::in(array_keys(config('goals.options')))],
            'daily_tasks' => ['required', 'integer', Rule::in(array_keys(config('dailytaskcontdown.options')))],
        ], [
            'email.email' => 'メールアドレスを正しく入力してください。',
            'email.unique' => 'このメールアドレスは既に登録されています。',
            'password.required' => 'パスワードを入力してください。',
            'password.current_password' => '現在のパスワードが正しくありません。',
            'change_password.min' => '新しいパスワードは8文字以上で入力してください。',
            'change_password.max' => '新しいパスワードは15文字以内で入力してください。',
            'change_password.numbers' => '新しいパスワードには数字を含めてください。',
            'change_password.letters' => '新しいパスワードには英字を含めてください。',
            'change_password.symbols' => '新しいパスワードには記号を含めてください。',
            'change_password_confirmation.same' => 'パスワードが一致しません。',
            'countdown_minutes.required' => '作業時間を選択してください。',
            'countdown_minutes.in' => '作業時間を選択してください。',
            'total_goals.required' => '目標を選択してください。',
            'daily_tasks.required' => '毎日のタスクを選択してください。',
        ]);

        if ($request->filled('new_email') && $validated['new_email'] !== $user->email) {
            $user->email = $validated['new_email'];
        }

        if ($request->filled('change_password')) {
            $user->password = Hash::make($validated['change_password']);
        }

        $user->countdown_minutes = $validated['countdown_minutes'];
        $user->total_goals = $validated['total_goals'];
        $user->daily_tasks = $validated['daily_tasks'];
        $user->save();

        return back()->with('success', '保存が成功しました!');
    }
}
