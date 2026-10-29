<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserTaskController extends Controller
{
    // 1 人が作れるタスクの上限。多すぎるとホーム画面が重くなる
    private const MAX_TASKS = 30;

    public function store(Request $request): RedirectResponse
    {
        if (auth()->user()->tasks()->count() >= self::MAX_TASKS) {
            return back()->withErrors(['name' => 'タスクは'.self::MAX_TASKS.'件まで作成できます。']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'countdown_seconds' => ['required', 'integer', Rule::in(array_keys(config('stretchtasks.options')))],
            'part' => ['nullable', 'string', 'max:20'],
            'effect' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'タスク名を入力してください。',
            'name.max' => 'タスク名は50文字以内で入力してください。',
            'countdown_seconds.required' => '秒数を選択してください。',
            'countdown_seconds.in' => '秒数を選択してください。',
            'part.max' => '部位は20文字以内で入力してください。',
            'effect.max' => '効果は50文字以内で入力してください。',
        ]);

        auth()->user()->tasks()->create($validated);

        return redirect()->route('home');
    }

    public function destroy(Task $task): RedirectResponse
    {
        // 他人のタスクは削除できないようにする
        abort_if($task->user_id !== auth()->id(), 403);

        $task->delete();

        return redirect()->route('home');
    }
}
