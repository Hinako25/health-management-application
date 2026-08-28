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
      $validated = $request->validate([
        'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore(auth()->id())],
        'password' => ['nullable', 'required_with:passwordConfirm', 'current_password'],
        'passwordConfirm' => ['nullable', 'required_with:password', Password::default()],
        'countdown_minutes' => ['required', 'integer', Rule::in(array_keys(config('workcountdown.options')))],
        'total_goals' => ['required', 'integer', Rule::in(array_keys(config('goals.options')))],
        'daily_tasks' => ['required', 'integer', Rule::in(array_keys(config('dailytaskcontdown.options')))],
]);

      $user = auth()->user();

      if ($request->filled('email')) {
          $user->email = $validated['email'];
       }

       if ($request->filled('passwordConfirm')) {
          // 今のパスワード確認 + 新パスワード保存
          $user->password = $validated['passwordConfirm'];
        }

        $user->countdown_minutes = $validated['countdown_minutes'];
        $user->total_goals = $validated['total_goals'];
        $user->daily_tasks = $validated['daily_tasks'];
        $user->save();

        return back()->with('success', '設定を保存しました。');
    }

}
