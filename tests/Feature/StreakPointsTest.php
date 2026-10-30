<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StreakPointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 日付で結果が変わるため、今日を固定する
        $this->travelTo(Carbon::parse('2026-10-27 12:00:00'));
    }

    /**
     * 連続達成の判定に使うユーザーを作成する。
     * HomeController::show は last_reset_date が今日でないと completed_tasks を 0 にするため、今日の日付を入れる。
     */
    private function createStreakUser(
        int $dailyTasks = 3,
        int $completedTasks = 0,
        int $streakDays = 0,
        ?string $lastStreakDate = null,
        int $points = 0,
    ): User {
        return User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password#123',
            'total_goals' => 7,
            'countdown_minutes' => 60,
            'daily_tasks' => $dailyTasks,
            'completed_tasks' => $completedTasks,
            'goal_reward' => '東京に行く！',
            'last_reset_date' => now()->toDateString(),
            'streak_days' => $streakDays,
            'last_streak_date' => $lastStreakDate,
            'points' => $points,
        ]);
    }

    private function postCompletedTasks(User $user, int $completedTasks): TestResponse
    {
        return $this->actingAs($user)->postJson(route('home.completed-tasks'), [
            'completed_tasks' => $completedTasks,
        ]);
    }

    public function test_first_achievement_starts_streak_at_1(): void
    {
        $user = $this->createStreakUser(dailyTasks: 3, completedTasks: 2);

        $this->postCompletedTasks($user, 3)
            ->assertOk()
            ->assertExactJson([
                'completed_tasks' => 3,
                'streak_days' => 1,
                'points' => 50,
                'rewarded_points' => 50,
            ]);

        $user->refresh();
        $this->assertSame(3, $user->completed_tasks);
        $this->assertSame(1, $user->streak_days);
        $this->assertSame('2026-10-27', $user->last_streak_date?->toDateString());
        $this->assertSame(50, $user->points);
    }

    public function test_not_reaching_daily_tasks_does_not_change_streak(): void
    {
        $user = $this->createStreakUser(dailyTasks: 3, streakDays: 2, lastStreakDate: '2026-10-26', points: 50);

        $this->postCompletedTasks($user, 2)
            ->assertOk()
            ->assertExactJson([
                'completed_tasks' => 2,
                'streak_days' => 2,
                'points' => 50,
                'rewarded_points' => 0,
            ]);

        $user->refresh();
        $this->assertSame(2, $user->streak_days);
        $this->assertSame('2026-10-26', $user->last_streak_date?->toDateString());
        $this->assertSame(50, $user->points);
    }

    public function test_streak_continues_when_last_streak_date_is_yesterday(): void
    {
        $user = $this->createStreakUser(dailyTasks: 3, streakDays: 1, lastStreakDate: '2026-10-26');

        $this->postCompletedTasks($user, 3)
            ->assertOk()
            ->assertJson(['streak_days' => 2, 'points' => 50, 'rewarded_points' => 50]);

        $user->refresh();
        $this->assertSame(2, $user->streak_days);
        $this->assertSame('2026-10-27', $user->last_streak_date?->toDateString());
    }

    public function test_streak_restarts_at_1_when_last_streak_date_is_2_days_ago(): void
    {
        $user = $this->createStreakUser(dailyTasks: 3, streakDays: 6, lastStreakDate: '2026-10-25', points: 100);

        $this->postCompletedTasks($user, 3)
            ->assertOk()
            ->assertExactJson([
                'completed_tasks' => 3,
                'streak_days' => 1,
                'points' => 150,
                'rewarded_points' => 50,
            ]);

        $user->refresh();
        $this->assertSame(1, $user->streak_days);
        // 連続が途切れても、たまったポイントは減らず、毎日のポイントはもらえる
        $this->assertSame(150, $user->points);
    }

    /**
     * 毎日の 50pt に、config('streakpoints.rewards') の日数に達したときだけ連続ボーナスが足される
     *
     * @return array<string, array{int, int}>
     */
    public static function rewardProvider(): array
    {
        return [
            '1日目は50pt' => [1, 50],
            '2日目は50pt' => [2, 50],
            '3日目は50+100pt' => [3, 150],
            '4日目は50pt' => [4, 50],
            '7日目は50+300pt' => [7, 350],
            '14日目は50+1000pt' => [14, 1050],
            '15日目は50pt（数え続ける）' => [15, 50],
            '21日目は50+2000pt' => [21, 2050],
            '30日目は50+3000pt' => [30, 3050],
            '60日目は50+5000pt' => [60, 5050],
            '61日目は50pt（数え続ける）' => [61, 50],
        ];
    }

    #[DataProvider('rewardProvider')]
    public function test_points_are_rewarded_on_configured_streak_days(int $streakDays, int $reward): void
    {
        $this->assertSame(50, config('streakpoints.daily'));
        $this->assertSame([3 => 100, 7 => 300, 14 => 1000, 21 => 2000, 30 => 3000, 60 => 5000], config('streakpoints.rewards'));

        // 1 日目は、まだ一度も目標回数を達成していない状態にする
        $lastStreakDate = $streakDays === 1 ? null : '2026-10-26';
        $user = $this->createStreakUser(dailyTasks: 3, streakDays: $streakDays - 1, lastStreakDate: $lastStreakDate, points: 50);

        $this->postCompletedTasks($user, 3)
            ->assertOk()
            ->assertExactJson([
                'completed_tasks' => 3,
                'streak_days' => $streakDays,
                'points' => 50 + $reward,
                'rewarded_points' => $reward,
            ]);

        $user->refresh();
        $this->assertSame($streakDays, $user->streak_days);
        $this->assertSame(50 + $reward, $user->points);
    }

    public function test_second_post_on_same_day_does_not_change_streak_or_points(): void
    {
        $user = $this->createStreakUser(dailyTasks: 3, streakDays: 2, lastStreakDate: '2026-10-26');

        $this->postCompletedTasks($user, 3)
            ->assertOk()
            ->assertJson(['streak_days' => 3, 'points' => 150, 'rewarded_points' => 150]);

        // 同じ日に目標回数を超えて続けても、もう一度は数えない
        $this->postCompletedTasks($user->fresh(), 4)
            ->assertOk()
            ->assertExactJson([
                'completed_tasks' => 4,
                'streak_days' => 3,
                'points' => 150,
                'rewarded_points' => 0,
            ]);

        $user->refresh();
        $this->assertSame(4, $user->completed_tasks);
        $this->assertSame(3, $user->streak_days);
        $this->assertSame(150, $user->points);
    }

    public function test_streak_is_counted_again_on_next_day(): void
    {
        $user = $this->createStreakUser(dailyTasks: 3, streakDays: 1, lastStreakDate: '2026-10-26');
        $this->postCompletedTasks($user, 3)->assertOk()->assertJson(['streak_days' => 2, 'points' => 50]);

        $this->travelTo(Carbon::parse('2026-10-28 09:00:00'));

        $this->postCompletedTasks($user->fresh(), 3)
            ->assertOk()
            ->assertJson(['streak_days' => 3, 'points' => 200, 'rewarded_points' => 150]);

        $this->assertSame('2026-10-28', $user->fresh()->last_streak_date?->toDateString());
    }

    public function test_streak_is_not_judged_when_daily_tasks_is_0(): void
    {
        $user = $this->createStreakUser(dailyTasks: 0, streakDays: 2, lastStreakDate: '2026-10-26');

        $this->postCompletedTasks($user, 0)
            ->assertOk()
            ->assertExactJson([
                'completed_tasks' => 0,
                'streak_days' => 2,
                'points' => 0,
                'rewarded_points' => 0,
            ]);

        $user->refresh();
        $this->assertSame(2, $user->streak_days);
        $this->assertSame('2026-10-26', $user->last_streak_date?->toDateString());
    }

    public function test_completed_tasks_requires_login(): void
    {
        $this->postJson(route('home.completed-tasks'), ['completed_tasks' => 1])->assertUnauthorized();
    }

    public function test_invalid_completed_tasks_is_rejected(): void
    {
        $user = $this->createStreakUser(dailyTasks: 3, completedTasks: 1);

        $this->postCompletedTasks($user, -1)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['completed_tasks']);

        $user->refresh();
        $this->assertSame(1, $user->completed_tasks);
        $this->assertSame(0, $user->streak_days);
    }

    public function test_current_streak_days(): void
    {
        $this->assertSame(0, $this->createStreakUser(streakDays: 5, lastStreakDate: null)->currentStreakDays());
        User::query()->delete();
        $this->assertSame(5, $this->createStreakUser(streakDays: 5, lastStreakDate: '2026-10-27')->currentStreakDays());
        User::query()->delete();
        $this->assertSame(5, $this->createStreakUser(streakDays: 5, lastStreakDate: '2026-10-26')->currentStreakDays());
        User::query()->delete();
        $this->assertSame(0, $this->createStreakUser(streakDays: 5, lastStreakDate: '2026-10-25')->currentStreakDays());
    }

    public function test_home_shows_points_and_streak_days(): void
    {
        $user = $this->createStreakUser(dailyTasks: 3, streakDays: 7, lastStreakDate: '2026-10-26', points: 400);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('home');
        $response->assertSee('<span id="goals-streak-display">7</span>日', false);
        $response->assertSee('<span id="daily-chart-points-display">400</span>pt', false);
    }

    public function test_home_shows_0_streak_days_when_last_streak_date_is_2_days_ago(): void
    {
        $user = $this->createStreakUser(dailyTasks: 3, streakDays: 7, lastStreakDate: '2026-10-25', points: 400);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee('<span id="goals-streak-display">0</span>日', false);
        // 連続が途切れても、たまったポイントは減らない
        $response->assertSee('<span id="daily-chart-points-display">400</span>pt', false);
    }

    public function test_home_has_hidden_reward_card_for_js(): void
    {
        $user = $this->createStreakUser();

        $content = $this->actingAs($user)->get(route('home'))->assertOk()->getContent();

        // dailyreport.js の updateStreakPoints / initRewardCard が getElementById で使う id
        $this->assertMatchesRegularExpression('/<[^>]*id="goals-reward-card"[^>]*>/', $content);
        preg_match('/<[^>]*id="goals-reward-card"[^>]*>/', $content, $matches);
        $this->assertMatchesRegularExpression('/class="[^"]*\bhidden\b[^"]*"/', $matches[0]);
        $this->assertStringContainsString('id="goals-reward-message"', $content);
        $this->assertStringContainsString('id="goals-reward-close"', $content);

        // id は 1 つずつだけ（重複すると getElementById が別の要素を取る）
        foreach (['goals-streak-display', 'daily-chart-points-display', 'goals-reward-card', 'goals-reward-message', 'goals-reward-close'] as $id) {
            $this->assertSame(1, substr_count($content, 'id="'.$id.'"'), $id);
        }
    }
}
