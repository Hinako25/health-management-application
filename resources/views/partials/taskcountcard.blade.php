@php
    // タスクは HomeController から受け取る
    $tasks = $tasks ?? collect();
    $taskTotalSeconds = $tasks->sum('countdown_seconds');
    $completedTasks = $completedTasks ?? auth()->user()->completed_tasks ?? 0;
@endphp

<div
    id="task-countdown-minute-root"
    data-tasks='@json($tasks)'
    data-total-seconds="{{ $taskTotalSeconds }}"
    data-completed-tasks="{{ $completedTasks }}"
    class="border-gray-300 border-2 rounded-md w-full flex flex-col min-h-0 bg-white p-5"
>

    <div class="flex items-center justify-between w-full pb-3 shrink-0">
        <h2 class="text-base font-semibold text-[#1a1d23]">ストレッチ　タスク</h2>
        <button
            type="button"
            id="start-task-countdown-minute"
            class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-green-500 opacity-75 hover:bg-green-600 opacity-75 transition-colors cursor-pointer text-white"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M8 5v14l11-7z"/>
            </svg>
            タスクを開始
        </button>
    </div>

    <span id="task-countdown-display" class="text-2xl font-bold tabular-nums shrink-0 px-5 pb-3">00:00</span>


    <div class="flex flex-col divide-y divide-[#f0f1f3] w-full flex-1 min-h-0 overflow-y-auto">
        @forelse ($tasks as $task)
            <div class="flex items-center">
                <button
                    type="button"
                    class="task-item group flex items-center gap-3 flex-1 px-5 py-2 text-2xl text-left hover:bg-[#f8f9fa] transition-colors disabled:opacity-50 "
                    data-task-id="{{ $task->id }}"
                    data-countdown-seconds="{{ $task->countdown_seconds }}"
                >
                    <span class="task-name flex-1 text-lg transition-colors group-[.is-checked]:line-through group-[.is-checked]:text-[#8b919a]">{{ $task->name }}</span>
                    @if ($task->part)
                        <span class="block bg-red-200 opacity-75 text-red-600 text-lg tabular-nums px-2 py-1 rounded-xl">{{ $task->part }}</span>
                    @endif
                    @if ($task->effect)
                        <span class="block bg-orange-200 opacity-75 text-orange-600 text-lg tabular-nums px-2 py-1 rounded-xl">{{ $task->effect }}</span>
                    @endif
                    <span class="block bg-green-200 opacity-75 text-green-600 text-lg tabular-nums px-2 py-1 rounded-xl transition-colors">{{ $task->countdown_seconds }}秒</span>
                </button>

                {{-- タスクの削除。button の中に form は入れられないので、横に並べる --}}
                <form method="POST" action="{{ route('home.tasks.destroy', $task) }}" class="shrink-0 px-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-2 py-1 rounded-xl text-sm text-gray-500 enabled:hover:bg-[#eceef1] transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" aria-label="{{ $task->name }}を削除">
                        削除
                    </button>
                </form>
            </div>
        @empty
            <p class="px-5 py-2 text-sm text-gray-500">タスクがありません。「+タスクを作る」から追加してください。</p>
        @endforelse
    </div>

    {{-- タスクの作成。入力エラーがあるときは開いたままにする --}}
    <details class="w-full pt-3 shrink-0" @if ($errors->hasAny(['name', 'countdown_seconds', 'part', 'effect'])) open @endif>
        <summary class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#eceef1] hover:bg-[#d5d8dc] transition-colors cursor-pointer text-gray-700">
            +タスクを作る
        </summary>

        <form method="POST" action="{{ route('home.tasks.store') }}" class="grid grid-cols-2 gap-3 mt-3">
            @csrf
            <div class="col-span-2">
                <input type="text" name="name" value="{{ old('name') }}" maxlength="50" placeholder="ストレッチの名前" class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <input type="text" name="part" value="{{ old('part') }}" maxlength="20" placeholder="部位（なくてもいい）" class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('part')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <input type="text" name="effect" value="{{ old('effect') }}" maxlength="50" placeholder="効果（なくてもいい）" class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('effect')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <select name="countdown_seconds" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="">秒数を選択してください</option>
                    @foreach (config('stretchtasks.options') as $seconds => $label)
                        <option value="{{ $seconds }}" @selected((string) old('countdown_seconds') === (string) $seconds)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('countdown_seconds')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-start justify-end">
                <button type="submit" class="px-3 py-2 rounded-xl bg-green-500 opacity-75 enabled:hover:bg-green-600 transition-colors cursor-pointer text-white disabled:opacity-50 disabled:cursor-not-allowed">
                    作成する
                </button>
            </div>
        </form>
    </details>
</div>
