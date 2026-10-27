<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyChartDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_seconds_are_added_to_daily_chart_data(): void
    {
        $user = User::factory()->create([
            'daily_chart_data' => ['2026-10-25' => ['work' => 600, 'stretch' => 30]],
        ]);

        $response = $this->actingAs($user)->postJson(route('home.daily-chart-data'), [
            'entries' => [
                ['date' => '2026-10-25', 'type' => 'work', 'seconds' => 120],
                ['date' => '2026-10-26', 'type' => 'stretch', 'seconds' => 45],
            ],
        ]);

        $expected = [
            '2026-10-25' => ['work' => 720, 'stretch' => 30],
            '2026-10-26' => ['work' => 0, 'stretch' => 45],
        ];
        $response->assertOk()->assertExactJson(['daily_chart_data' => $expected]);
        $this->assertEquals($expected, $user->refresh()->daily_chart_data);
    }

    public function test_invalid_entries_are_rejected(): void
    {
        $user = User::factory()->create(['daily_chart_data' => null]);

        $this->actingAs($user)->postJson(route('home.daily-chart-data'), [
            'entries' => [
                ['date' => '25-10-2026', 'type' => 'sleep', 'seconds' => 0],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'entries.0.date',
            'entries.0.type',
            'entries.0.seconds',
        ]);

        $this->assertNull($user->refresh()->daily_chart_data);
    }

    public function test_daily_chart_data_is_passed_to_home_as_json(): void
    {
        $user = User::factory()->create([
            'total_goals' => 7,
            'daily_tasks' => 3,
            'last_reset_date' => now()->toDateString(),
            'daily_chart_data' => ['2026-10-25' => ['work' => 600, 'stretch' => 30]],
        ]);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('data-chart-data=\'{"2026-10-25":{"work":600,"stretch":30}}\'', false);
    }

    public function test_daily_chart_data_requires_login(): void
    {
        $this->postJson(route('home.daily-chart-data'), ['entries' => []])->assertUnauthorized();
    }

    // daily_chart_data が null のユーザーでも、最初の1件から保存できる（JS の addWorkSeconds が送る形）
    public function test_first_entry_is_saved_when_daily_chart_data_is_null(): void
    {
        $user = User::factory()->create(['daily_chart_data' => null]);

        $this->actingAs($user)->postJson('/home/daily-chart-data', [
            'entries' => [
                ['date' => '2026-10-27', 'type' => 'work', 'seconds' => 1500],
                ['date' => '2026-10-27', 'type' => 'work', 'seconds' => 300],
            ],
        ])->assertOk()->assertExactJson([
            'daily_chart_data' => ['2026-10-27' => ['work' => 1800, 'stretch' => 0]],
        ]);

        $this->assertSame(
            ['2026-10-27' => ['work' => 1800, 'stretch' => 0]],
            $user->refresh()->daily_chart_data,
        );
    }

    // 送っていない日付の既存データは消えずに残る
    public function test_other_dates_are_kept(): void
    {
        $user = User::factory()->create([
            'daily_chart_data' => [
                '2026-10-20' => ['work' => 100, 'stretch' => 10],
                '2026-10-21' => ['work' => 200, 'stretch' => 20],
            ],
        ]);

        $this->actingAs($user)->postJson(route('home.daily-chart-data'), [
            'entries' => [['date' => '2026-10-21', 'type' => 'stretch', 'seconds' => 5]],
        ])->assertOk();

        $this->assertEquals([
            '2026-10-20' => ['work' => 100, 'stretch' => 10],
            '2026-10-21' => ['work' => 200, 'stretch' => 25],
        ], $user->refresh()->daily_chart_data);
    }

    // 他のユーザーのデータは変わらない
    public function test_other_users_data_is_not_changed(): void
    {
        $user = User::factory()->create(['daily_chart_data' => null]);
        $other = User::factory()->create([
            'daily_chart_data' => ['2026-10-25' => ['work' => 600, 'stretch' => 0]],
        ]);

        $this->actingAs($user)->postJson(route('home.daily-chart-data'), [
            'entries' => [['date' => '2026-10-25', 'type' => 'work', 'seconds' => 60]],
        ])->assertOk();

        $this->assertEquals(
            ['2026-10-25' => ['work' => 600, 'stretch' => 0]],
            $other->refresh()->daily_chart_data,
        );
    }

    // 境界値：1秒と86400秒は保存でき、86401秒は拒否される
    public function test_seconds_boundary(): void
    {
        $user = User::factory()->create(['daily_chart_data' => null]);

        $this->actingAs($user)->postJson(route('home.daily-chart-data'), [
            'entries' => [
                ['date' => '2026-10-25', 'type' => 'work', 'seconds' => 1],
                ['date' => '2026-10-26', 'type' => 'work', 'seconds' => 86400],
            ],
        ])->assertOk();

        $this->actingAs($user)->postJson(route('home.daily-chart-data'), [
            'entries' => [['date' => '2026-10-27', 'type' => 'work', 'seconds' => 86401]],
        ])->assertUnprocessable()->assertJsonValidationErrors(['entries.0.seconds']);

        $this->assertEquals([
            '2026-10-25' => ['work' => 1, 'stretch' => 0],
            '2026-10-26' => ['work' => 86400, 'stretch' => 0],
        ], $user->refresh()->daily_chart_data);
    }

    // 小数の秒数は拒否される（JS 側で Math.floor していない値が来た場合）
    public function test_decimal_seconds_are_rejected(): void
    {
        $user = User::factory()->create(['daily_chart_data' => null]);

        $this->actingAs($user)->postJson(route('home.daily-chart-data'), [
            'entries' => [['date' => '2026-10-25', 'type' => 'work', 'seconds' => 1.5]],
        ])->assertUnprocessable()->assertJsonValidationErrors(['entries.0.seconds']);

        $this->assertNull($user->refresh()->daily_chart_data);
    }

    // entries が空・欠けている・501件以上のときは拒否され、1件も保存されない
    public function test_empty_missing_or_too_many_entries_are_rejected(): void
    {
        $user = User::factory()->create(['daily_chart_data' => null]);

        $this->actingAs($user)->postJson(route('home.daily-chart-data'), ['entries' => []])
            ->assertUnprocessable()->assertJsonValidationErrors(['entries' => '作業時間のデータがありません。']);

        $this->actingAs($user)->postJson(route('home.daily-chart-data'), [])
            ->assertUnprocessable()->assertJsonValidationErrors(['entries']);

        $entries = array_fill(0, 501, ['date' => '2026-10-25', 'type' => 'work', 'seconds' => 1]);
        $this->actingAs($user)->postJson(route('home.daily-chart-data'), ['entries' => $entries])
            ->assertUnprocessable()->assertJsonValidationErrors(['entries' => '一度に保存できる作業時間のデータが多すぎます。']);

        $this->assertNull($user->refresh()->daily_chart_data);
    }

    // 500件ちょうどは保存できる
    public function test_500_entries_are_accepted(): void
    {
        $user = User::factory()->create(['daily_chart_data' => null]);

        $entries = array_fill(0, 500, ['date' => '2026-10-25', 'type' => 'stretch', 'seconds' => 2]);
        $this->actingAs($user)->postJson(route('home.daily-chart-data'), ['entries' => $entries])
            ->assertOk();

        $this->assertEquals(
            ['2026-10-25' => ['work' => 0, 'stretch' => 1000]],
            $user->refresh()->daily_chart_data,
        );
    }

    // 1件でも不正なエントリがあれば、正しいエントリも保存されない
    public function test_valid_entries_are_not_saved_when_one_is_invalid(): void
    {
        $user = User::factory()->create(['daily_chart_data' => null]);

        $this->actingAs($user)->postJson(route('home.daily-chart-data'), [
            'entries' => [
                ['date' => '2026-10-25', 'type' => 'work', 'seconds' => 60],
                ['date' => '2026-02-30', 'type' => 'work', 'seconds' => 60],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors(['entries.1.date']);

        $this->assertNull($user->refresh()->daily_chart_data);
    }

    // GET では保存できない（状態を変えるルートは POST のみ）
    public function test_get_method_is_not_allowed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/home/daily-chart-data')->assertMethodNotAllowed();
    }

    // データがないユーザーでは、data-chart-data に [] が入る（JS の normalizeChartData が {} に直す前提）
    public function test_empty_daily_chart_data_is_passed_to_home_as_empty_array(): void
    {
        $user = User::factory()->create([
            'total_goals' => 7,
            'daily_tasks' => 3,
            'last_reset_date' => now()->toDateString(),
            'daily_chart_data' => null,
        ]);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('data-chart-data=\'[]\'', false)
            ->assertSee('id="daily-chart-root"', false)
            ->assertSee('data-user-id="'.$user->id.'"', false);
    }
}
