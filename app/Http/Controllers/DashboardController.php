<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;

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
            'newEmail' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'required_with:changePassword', 'current_password'],
            'changePassword' => ['nullable', 'required_with:password', Password::default(
                'required',
                'confirmed',
                Password::min(8)->max(15)->numbers()->letters()->symbols(),
            )],

            'changePasswordConfirm' => ['nullable', 'required_with:changePassword', 'same:changePassword'],
            'countdown_minutes' => ['required', 'integer', Rule::in(array_keys(config('workcountdown.options')))],
            'total_goals' => ['required', 'integer', Rule::in(array_keys(config('goals.options')))],
            'daily_tasks' => ['required', 'integer', Rule::in(array_keys(config('dailytaskcontdown.options')))],
        ], [
            'password.required' => 'パスワードを入力してください。',
            'password.current_password' => '現在のパスワードが正しくありません。',
            'changePassword.required' => '新しいパスワードを入力してください。',
            'changePasswordConfirm.same' => 'パスワードが一致しません。',
            'countdown_minutes.required' => '作業時間を選択してください。',
            'countdown_minutes.in' => '作業時間を選択してください。',
            'total_goals.required' => '目標を選択してください。',
            'daily_tasks.required' => '毎日のタスクを選択してください。',
        ]);

        if ($request->filled('newEmail') && $validated['newEmail'] !== $user->email) {
            $user->email = $validated['newEmail'];
        }

        if ($request->filled('changePassword')) {
            $user->password = $validated['changePassword'];
        }

        $user->countdown_minutes = $validated['countdown_minutes'];
        $user->total_goals = $validated['total_goals'];
        $user->daily_tasks = $validated['daily_tasks'];
        $user->save();

        return back()->with('success', '設定を保存しました。');
    }
}
