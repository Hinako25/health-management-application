<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserTaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * /home を表示するユーザーを作成する。
     * HomeController::show は last_reset_date が今日でないと completed_tasks を 0 にするため、今日の日付を入れる。
     */
    private function createHomeUser(string $email = 'test@example.com'): User
    {
        return User::factory()->create([
            'email' => $email,
            'password' => 'password#123',
            'total_goals' => 7,
            'countdown_minutes' => 60,
            'daily_tasks' => 3,
            'completed_tasks' => 0,
            'last_reset_date' => now()->toDateString(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createTask(User $user, array $attributes = []): Task
    {
        return $user->tasks()->create([
            'name' => 'テストのストレッチ',
            'countdown_seconds' => 10,
            'part' => '首',
            'effect' => '血流の促進',
            ...$attributes,
        ]);
    }

    /**
     * taskcountcard の #task-countdown-minute-root にある data-tasks（JS の taskcount.js が読む JSON）を取り出す
     *
     * @return array<int, array<string, mixed>>
     */
    private function dataTasks(TestResponse $response): array
    {
        $this->assertMatchesRegularExpression(
            "/<div[^>]*id=\"task-countdown-minute-root\"[^>]*data-tasks='([^']*)'/s",
            $response->getContent(),
        );
        preg_match("/<div[^>]*id=\"task-countdown-minute-root\"[^>]*data-tasks='([^']*)'/s", $response->getContent(), $matches);

        $decoded = json_decode($matches[1], true);
        $this->assertIsArray($decoded);

        return $decoded;
    }

    /**
     * #task-countdown-minute-root の開始タグだけを取り出す（timer-root など他のカードの data-* と区別するため）
     */
    private function rootTag(TestResponse $response): string
    {
        $this->assertMatchesRegularExpression('/<div[^>]*id="task-countdown-minute-root"[^>]*>/s', $response->getContent());
        preg_match('/<div[^>]*id="task-countdown-minute-root"[^>]*>/s', $response->getContent(), $matches);

        return $matches[0];
    }

    // ---- 作成 ----

    public function test_store_task_success(): void
    {
        $user = $this->createHomeUser();

        $response = $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '肩を回す',
            'countdown_seconds' => 20,
            'part' => '肩',
            'effect' => '肩こり軽減',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('tasks', 1);
        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'name' => '肩を回す',
            'countdown_seconds' => 20,
            'part' => '肩',
            'effect' => '肩こり軽減',
        ]);
    }

    public function test_store_task_without_part_and_effect_success(): void
    {
        $user = $this->createHomeUser();

        $response = $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '深呼吸',
            'countdown_seconds' => 30,
            'part' => '',
            'effect' => '',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasNoErrors();
        // ConvertEmptyStringsToNull により空文字は null で保存される
        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'name' => '深呼吸',
            'countdown_seconds' => 30,
            'part' => null,
            'effect' => null,
        ]);

        // 部位・効果がなくてもホームでエラーにならず、タスクが表示される
        $home = $this->actingAs($user)->get(route('home'));
        $home->assertOk();
        $home->assertSee('深呼吸');
    }

    public function test_store_task_accepts_every_option_seconds(): void
    {
        $user = $this->createHomeUser();

        foreach (array_keys(config('stretchtasks.options')) as $seconds) {
            $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
                'name' => $seconds.'秒のストレッチ',
                'countdown_seconds' => (string) $seconds,
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(count(config('stretchtasks.options')), $user->tasks()->count());
    }

    public function test_store_task_requires_name(): void
    {
        $user = $this->createHomeUser();

        $response = $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '',
            'countdown_seconds' => 10,
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors(['name' => 'タスク名を入力してください。']);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_store_task_rejects_too_long_name_and_part_and_effect(): void
    {
        $user = $this->createHomeUser();

        $response = $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => str_repeat('あ', 51),
            'countdown_seconds' => 10,
            'part' => str_repeat('あ', 21),
            'effect' => str_repeat('あ', 51),
        ]);

        $response->assertSessionHasErrors([
            'name' => 'タスク名は50文字以内で入力してください。',
            'part' => '部位は20文字以内で入力してください。',
            'effect' => '効果は50文字以内で入力してください。',
        ]);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_store_task_rejects_seconds_not_in_options(): void
    {
        $user = $this->createHomeUser();

        // 25 秒は config/stretchtasks.php の options にない
        $this->assertArrayNotHasKey(25, config('stretchtasks.options'));

        $response = $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '肩を回す',
            'countdown_seconds' => 25,
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors(['countdown_seconds' => '秒数を選択してください。']);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_store_task_requires_seconds(): void
    {
        $user = $this->createHomeUser();

        $response = $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '肩を回す',
            'countdown_seconds' => '',
        ]);

        $response->assertSessionHasErrors(['countdown_seconds' => '秒数を選択してください。']);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_store_task_ignores_user_id_in_request(): void
    {
        $user = $this->createHomeUser();
        $other = $this->createHomeUser('other@example.com');

        // user_id を送っても、ログイン中のユーザーのタスクとして作られる
        $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '肩を回す',
            'countdown_seconds' => 10,
            'user_id' => $other->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $user->tasks()->count());
        $this->assertSame(0, $other->tasks()->count());
    }

    public function test_validation_error_keeps_create_form_open_on_home(): void
    {
        $user = $this->createHomeUser();

        $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '',
            'countdown_seconds' => 10,
        ]);

        $home = $this->actingAs($user)->get(route('home'));
        $home->assertOk();
        $home->assertSee('タスク名を入力してください。');
        $this->assertMatchesRegularExpression('/<details class="w-full pt-3 shrink-0"\s+open\s*>/', $home->getContent());
    }

    public function test_create_form_is_closed_without_errors(): void
    {
        $user = $this->createHomeUser();

        $home = $this->actingAs($user)->get(route('home'));
        $home->assertOk();
        $this->assertMatchesRegularExpression('/<details class="w-full pt-3 shrink-0"\s*>/', $home->getContent());
    }

    // ---- 上限（30件） ----

    /**
     * @param  int  $count  作成するタスクの件数
     */
    private function createTasks(User $user, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->createTask($user, ['name' => 'タスク'.$i]);
        }
    }

    public function test_store_task_success_when_29_tasks(): void
    {
        $user = $this->createHomeUser();
        $this->createTasks($user, 29);

        $response = $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '30件目',
            'countdown_seconds' => 10,
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasNoErrors();
        $this->assertSame(30, $user->tasks()->count());
        $this->assertDatabaseHas('tasks', ['user_id' => $user->id, 'name' => '30件目']);
    }

    public function test_store_task_is_rejected_when_30_tasks(): void
    {
        $user = $this->createHomeUser();
        $this->createTasks($user, 30);

        $response = $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '31件目',
            'countdown_seconds' => 10,
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors(['name' => 'タスクは30件まで作成できます。']);
        $this->assertSame(30, $user->tasks()->count());
        $this->assertDatabaseMissing('tasks', ['name' => '31件目']);

        // エラーはホームの作成フォームに表示され、フォームが開いたままになる
        // session の serialization が json のため、assertSessionHasErrors の後に別リクエストで GET すると
        // エラーが画面に出ない。もう一度送信し、リダイレクト先をそのままたどって確認する
        $home = $this->actingAs($user)->from(route('home'))->followingRedirects()->post(route('home.tasks.store'), [
            'name' => '31件目',
            'countdown_seconds' => 10,
        ]);
        $home->assertOk();
        $home->assertSee('タスクは30件まで作成できます。');
        $this->assertMatchesRegularExpression('/<details class="w-full pt-3 shrink-0"\s+open\s*>/', $home->getContent());
    }

    public function test_task_limit_counts_only_own_tasks(): void
    {
        $user = $this->createHomeUser();
        $other = $this->createHomeUser('other@example.com');
        $this->createTasks($other, 30);

        // 他人が30件持っていても、自分は作成できる
        $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '自分のタスク',
            'countdown_seconds' => 10,
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $user->tasks()->count());
    }

    public function test_store_task_success_after_deleting_from_30_tasks(): void
    {
        $user = $this->createHomeUser();
        $this->createTasks($user, 30);

        $this->actingAs($user)->delete(route('home.tasks.destroy', $user->tasks()->first()))->assertRedirect(route('home'));

        $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '削除後のタスク',
            'countdown_seconds' => 10,
        ])->assertSessionHasNoErrors();

        $this->assertSame(30, $user->tasks()->count());
    }

    // ---- 削除 ----

    public function test_destroy_own_task_success(): void
    {
        $user = $this->createHomeUser();
        $task = $this->createTask($user);
        $remaining = $this->createTask($user, ['name' => '残すタスク']);

        $response = $this->actingAs($user)->from(route('home'))->delete(route('home.tasks.destroy', $task));

        $response->assertRedirect(route('home'));
        $response->assertSessionMissing('success');
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseHas('tasks', ['id' => $remaining->id]);
    }

    public function test_destroy_other_users_task_is_forbidden(): void
    {
        $user = $this->createHomeUser();
        $other = $this->createHomeUser('other@example.com');
        $otherTask = $this->createTask($other);

        $response = $this->actingAs($user)->delete(route('home.tasks.destroy', $otherTask));

        $response->assertForbidden();
        $this->assertDatabaseHas('tasks', ['id' => $otherTask->id, 'user_id' => $other->id]);
    }

    public function test_destroy_missing_task_returns_404(): void
    {
        $user = $this->createHomeUser();

        $this->actingAs($user)->delete(route('home.tasks.destroy', ['task' => 999999]))->assertNotFound();
    }

    public function test_destroy_with_get_or_post_is_not_allowed(): void
    {
        $user = $this->createHomeUser();
        $task = $this->createTask($user);

        // 削除は DELETE だけ（GET で DB を更新しない）
        $this->actingAs($user)->get('/home/tasks/'.$task->id)->assertMethodNotAllowed();
        $this->actingAs($user)->post('/home/tasks/'.$task->id)->assertMethodNotAllowed();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    // ---- 未ログイン ----

    public function test_guest_cannot_store_task(): void
    {
        $this->post(route('home.tasks.store'), [
            'name' => '肩を回す',
            'countdown_seconds' => 10,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_guest_cannot_destroy_task(): void
    {
        $user = $this->createHomeUser();
        $task = $this->createTask($user);

        $this->delete(route('home.tasks.destroy', $task))->assertRedirect(route('login'));

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    // ---- アカウント作成 ----

    public function test_create_account_copies_initial_tasks(): void
    {
        $items = config('stretchtasks.items');
        $this->assertCount(7, $items);

        $this->post(route('createaccount.submit'), [
            'email' => 'new@example.com',
            'password' => 'password#123',
            'password_confirmation' => 'password#123',
        ])->assertRedirect(route('login'));

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $tasks = $user->tasks()->oldest('id')->get();

        $this->assertCount(7, $tasks);
        // config の順番どおりにコピーされる（taskcount.js は data-tasks の順番で進める）
        foreach ($items as $index => $item) {
            $this->assertSame($item['name'], $tasks[$index]->name);
            $this->assertSame($item['countdown_seconds'], $tasks[$index]->countdown_seconds);
            $this->assertSame($item['part'], $tasks[$index]->part);
            $this->assertSame($item['effect'], $tasks[$index]->effect);
        }
    }

    public function test_initial_tasks_are_copied_per_user(): void
    {
        foreach (['a@example.com', 'b@example.com'] as $email) {
            $this->post(route('createaccount.submit'), [
                'email' => $email,
                'password' => 'password#123',
                'password_confirmation' => 'password#123',
            ])->assertRedirect(route('login'));
        }

        $a = User::where('email', 'a@example.com')->firstOrFail();
        $b = User::where('email', 'b@example.com')->firstOrFail();
        $this->assertDatabaseCount('tasks', 14);

        // A がタスクを削除しても B のタスクは減らない
        $this->actingAs($a)->delete(route('home.tasks.destroy', $a->tasks()->first()))->assertRedirect(route('home'));

        $this->assertSame(6, $a->tasks()->count());
        $this->assertSame(7, $b->tasks()->count());
    }

    public function test_user_is_not_created_when_copying_tasks_fails(): void
    {
        // name が null のタスクは tasks.name の NOT NULL 制約で保存に失敗する
        config(['stretchtasks.items' => [
            ['name' => null, 'countdown_seconds' => 10, 'part' => null, 'effect' => null],
        ]]);

        $response = $this->post(route('createaccount.submit'), [
            'email' => 'new@example.com',
            'password' => 'password#123',
            'password_confirmation' => 'password#123',
        ]);

        $response->assertServerError();
        // DB::transaction でまとめているので、タスクのないユーザーだけが残ることはない
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_failed_account_creation_does_not_create_tasks(): void
    {
        $this->post(route('createaccount.submit'), [
            'email' => 'new@example.com',
            'password' => 'password#123',
            'password_confirmation' => 'different#123',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('tasks', 0);
    }

    // ---- ホームの表示 ----

    public function test_home_shows_only_own_tasks(): void
    {
        $user = $this->createHomeUser();
        $other = $this->createHomeUser('other@example.com');
        $mine1 = $this->createTask($user, ['name' => '自分のタスク1', 'countdown_seconds' => 10]);
        $mine2 = $this->createTask($user, ['name' => '自分のタスク2', 'countdown_seconds' => 20, 'part' => null, 'effect' => null]);
        $others = $this->createTask($other, ['name' => '他人のタスク', 'countdown_seconds' => 30]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('home');
        $response->assertViewHas('tasks', fn ($tasks) => $tasks->pluck('id')->all() === [$mine1->id, $mine2->id]);
        $response->assertSeeInOrder(['自分のタスク1', '自分のタスク2']);
        $response->assertDontSee('他人のタスク');
        $response->assertDontSee('data-task-id="'.$others->id.'"', false);
        $response->assertDontSee(route('home.tasks.destroy', $others), false);

        // taskcount.js が使う .task-item[data-task-id] と data-countdown-seconds
        $response->assertSee('data-task-id="'.$mine1->id.'"', false);
        $response->assertSee('data-task-id="'.$mine2->id.'"', false);
        $this->assertMatchesRegularExpression(
            '/class="task-item[^"]*"\s+data-task-id="'.$mine1->id.'"\s+data-countdown-seconds="10"/',
            $response->getContent(),
        );

        // 合計秒数（data-total-seconds）は自分のタスクだけ
        $this->assertStringContainsString('data-total-seconds="30"', $this->rootTag($response));
    }

    public function test_home_data_tasks_json_matches_task_items(): void
    {
        $user = $this->createHomeUser();
        $task1 = $this->createTask($user, ['name' => '1つ目']);
        $task2 = $this->createTask($user, ['name' => '2つ目', 'countdown_seconds' => 60]);

        $response = $this->actingAs($user)->get(route('home'));
        $response->assertOk();

        // JS は JSON の task.id（数値）と Number(button.dataset.taskId) を比べるので、id が数値で順番どおりに入っていること
        $tasks = $this->dataTasks($response);
        $this->assertSame([$task1->id, $task2->id], array_column($tasks, 'id'));
        $this->assertSame(['1つ目', '2つ目'], array_column($tasks, 'name'));
        $this->assertSame([10, 60], array_column($tasks, 'countdown_seconds'));

        foreach ($tasks as $task) {
            $this->assertIsInt($task['id']);
            $response->assertSee('data-task-id="'.$task['id'].'"', false);
        }
    }

    public function test_home_shows_delete_and_create_forms(): void
    {
        $user = $this->createHomeUser();
        $task = $this->createTask($user);

        $response = $this->actingAs($user)->get(route('home'));
        $content = $response->getContent();

        // 削除フォーム：POST + @method('DELETE') + @csrf がフォームの内側にある
        $this->assertMatchesRegularExpression(
            '#<form method="POST" action="'.preg_quote(route('home.tasks.destroy', $task), '#').'"[^>]*>\s*<input type="hidden" name="_token"[^>]*>\s*<input type="hidden" name="_method" value="DELETE">#',
            $content,
        );

        // 作成フォーム：POST + @csrf がフォームの内側にある
        $this->assertMatchesRegularExpression(
            '#<form method="POST" action="'.preg_quote(route('home.tasks.store'), '#').'"[^>]*>\s*<input type="hidden" name="_token"#',
            $content,
        );

        // 秒数の選択肢は config/stretchtasks.php の options
        foreach (config('stretchtasks.options') as $seconds => $label) {
            $this->assertMatchesRegularExpression('#<option value="'.$seconds.'"\s*>'.preg_quote($label, '#').'</option>#u', $content);
        }
    }

    public function test_home_shows_empty_message_when_no_tasks(): void
    {
        $user = $this->createHomeUser();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee('タスクがありません。');
        $this->assertStringContainsString('data-total-seconds="0"', $this->rootTag($response));
        $this->assertSame([], $this->dataTasks($response));
    }

    public function test_home_escapes_task_name_part_and_effect(): void
    {
        $user = $this->createHomeUser();
        $this->actingAs($user)->from(route('home'))->post(route('home.tasks.store'), [
            'name' => '<script>alert(1)</script>',
            'countdown_seconds' => 10,
            'part' => '<b>首</b>',
            'effect' => "'\"&",
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($user)->get(route('home'));
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $content);
        $this->assertStringNotContainsString('<b>首</b>', $content);
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);

        // data-tasks='...' の JSON が属性から抜け出していない（@json が ' を ' にエスケープする）
        $tasks = $this->dataTasks($response);
        $this->assertSame('<script>alert(1)</script>', $tasks[0]['name']);
        $this->assertSame("'\"&", $tasks[0]['effect']);
    }

    public function test_tasks_are_deleted_with_user(): void
    {
        $user = $this->createHomeUser();
        $this->createTask($user);
        $this->createTask($user);

        $user->delete();

        $this->assertDatabaseCount('tasks', 0);
    }
}
