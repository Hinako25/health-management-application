<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalsCardAchievementRateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 目標カード付きの /home を表示するユーザーを作成する。
     * HomeController::show は last_reset_date が今日でないと completed_tasks を 0 にするため、今日の日付を入れる。
     */
    private function createHomeUser(int $dailyTasks, int $completedTasks, ?string $lastResetDate = null): User
    {
        return User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password#123',
            'total_goals' => 7,
            'countdown_minutes' => 60,
            'daily_tasks' => $dailyTasks,
            'completed_tasks' => $completedTasks,
            'goal_reward' => '東京に行く！',
            'last_reset_date' => $lastResetDate ?? now()->toDateString(),
        ]);
    }

    private function assertAchievementRate(User $user, int $completedTasks, int $dailyTasks, int $rate): void
    {
        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('home');
        $response->assertViewHas('completedTasks', $completedTasks);
        // JS (dailyreport.js) が読む data-* 属性。taskcountcard にも data-completed-tasks があるため、
        // #goals-card-root の開始タグだけを取り出して確認する
        $this->assertMatchesRegularExpression('/<[^>]*id="goals-card-root"[^>]*>/', $response->getContent());
        preg_match('/<[^>]*id="goals-card-root"[^>]*>/', $response->getContent(), $matches);
        $this->assertStringContainsString('data-daily-tasks="'.$dailyTasks.'"', $matches[0]);
        $this->assertStringContainsString('data-completed-tasks="'.$completedTasks.'"', $matches[0]);
        // 回数表示
        $response->assertSee('<span id="goals-completed-display">'.$completedTasks.'/'.$dailyTasks.'</span>', false);
        // 達成率の文字表示と進捗バーの幅
        $response->assertSee('達成率:<span id="goals-achievement-rate-display">'.$rate.'%</span>', false);
        $response->assertSee('id="goals-achievement-rate-bar"', false);
        $response->assertSee('style="width: '.$rate.'%"', false);
        $response->assertSee('aria-valuenow="'.$rate.'"', false);
    }

    public function test_achievement_rate_is_40_percent_when_2_of_5_completed(): void
    {
        $user = $this->createHomeUser(dailyTasks: 5, completedTasks: 2);

        $this->assertAchievementRate($user, completedTasks: 2, dailyTasks: 5, rate: 40);

        $user->refresh();
        $this->assertEquals(2, $user->completed_tasks);
    }

    public function test_achievement_rate_is_0_percent_when_daily_tasks_is_0(): void
    {
        $user = $this->createHomeUser(dailyTasks: 0, completedTasks: 0);

        $this->assertAchievementRate($user, completedTasks: 0, dailyTasks: 0, rate: 0);
    }

    public function test_achievement_rate_is_capped_at_100_percent_when_exceeded(): void
    {
        $user = $this->createHomeUser(dailyTasks: 3, completedTasks: 5);

        $this->assertAchievementRate($user, completedTasks: 5, dailyTasks: 3, rate: 100);
        // 166% のような上限超えの値が出ていないこと
        $this->actingAs($user)->get(route('home'))
            ->assertDontSee('166%', false)
            ->assertDontSee('167%', false);
    }

    public function test_achievement_rate_is_floored(): void
    {
        // 1/3 = 33.33...% は切り捨てで 33%
        $user = $this->createHomeUser(dailyTasks: 3, completedTasks: 1);

        $this->assertAchievementRate($user, completedTasks: 1, dailyTasks: 3, rate: 33);
    }

    public function test_achievement_rate_has_no_floating_point_error(): void
    {
        // 29 / 100 * 100 は浮動小数点で 28.999... になるため、計算順によっては 28% になってしまう
        $user = $this->createHomeUser(dailyTasks: 100, completedTasks: 29);

        $this->assertAchievementRate($user, completedTasks: 29, dailyTasks: 100, rate: 29);
    }

    public function test_completed_tasks_and_rate_reset_when_last_reset_date_is_not_today(): void
    {
        $user = $this->createHomeUser(
            dailyTasks: 5,
            completedTasks: 4,
            lastResetDate: now()->subDay()->toDateString(),
        );

        $this->assertAchievementRate($user, completedTasks: 0, dailyTasks: 5, rate: 0);

        $user->refresh();
        $this->assertEquals(0, $user->completed_tasks);
        $this->assertEquals(now()->toDateString(), $user->last_reset_date);
    }

    public function test_home_requires_login(): void
    {
        $this->get(route('home'))->assertRedirect(route('login'));
    }
}
