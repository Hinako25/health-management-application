<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    public function test_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertViewIs('welcome');
    }
    public function test_create_account_success()
    {
        $this->get(route('createaccount'))->assertOk()->assertViewIs('createaccount');
        $response = $this->from(route('createaccount'))->post(route('createaccount.submit'), [
            'email' => 'test@example.com',
            'password' => 'password#123',
            'password_confirmation' =>'password#123',
        ]);
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'アカウントを作成しました。');
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue(Hash::check('password#123', $user->password));
        $this->assertNull($user->total_goals);
         // デフォルト値が60
        $this->assertEquals(60,$user->countdown_minutes); 
        $this->assertNull($user->daily_tasks);
        $this->assertNull($user->goal_reward);
       
    }
    public function test_create_login_success()
    {
        $this->get(route('login'))->assertOk()->assertViewIs('auth.login');
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password#123',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
        $response = $this->post(route('login.submit'), [
            'email' => 'test@example.com',
            'password' => 'password#123',
        ]);
        $response->assertRedirect(route('creategoals'));
        $response->assertSessionHasNoErrors();
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue(Hash::check('password#123', $user->password));
    }
    public function test_create_goals_success()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password#123',
        ]);
        $this->actingAs($user)->get(route('creategoals'))->assertOk()->assertViewIs('creategoals');
        $response = $this->actingAs($user)->post(route('creategoals.submit'), [
            'total_goals' => 7,
            'countdown_minutes' => 60,
            'daily_tasks' => 6,
            'goal_reward' => '東京に行く！',
        ]);
        $response->assertRedirect(route('home'));
        $user->refresh();
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue(Hash::check('password#123', $user->password));
        $this->assertEquals(7, $user->total_goals);
        $this->assertEquals(60, $user->countdown_minutes);
        $this->assertEquals(6, $user->daily_tasks);
        $this->assertEquals('東京に行く！', $user->goal_reward);
    }
    public function test_dashboard_update_success()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password#123',
            'countdown_minutes' => 60,
            'total_goals' => 7,
            'daily_tasks' => 6,
        ]);
        $response = $this->actingAs($user)->from(route('dashboard'))->post(route('dashboard.update'), [
            'new_email' => 'test2@example.com',
            'password' => 'password#123',
            'change_password' => 'password#456',
            'change_password_confirmation' => 'password#456',
            'countdown_minutes' => 90,
            'total_goals' => 60,
            'daily_tasks' => 5,
        ]);
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', '保存が成功しました!');
        $user->refresh();
        $this->assertEquals('test2@example.com', $user->email);
        $this->assertTrue(Hash::check('password#456', $user->password));
        $this->assertEquals(90, $user->countdown_minutes);
        $this->assertEquals(60, $user->total_goals);
        $this->assertEquals(5, $user->daily_tasks);
    }
    
    public function test_work_success(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password#123',
            'countdown_minutes' => 60,
            'sound_enabled' => 0,
        ]);
        $response = $this->actingAs($user)->from(route('home'))->post(route('home.work'), [
            'countdown_minutes' => 90,
        ]);
        $response->assertRedirect(route('home'));
        $user->refresh();
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue(Hash::check('password#123', $user->password));
        $this->assertEquals(90, $user->countdown_minutes);
        $this->assertEquals(0, $user->sound_enabled);
    }

    public function test_update_sound_enabled_success(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password#123',
            'countdown_minutes' => 60,
            'sound_enabled' => 0,
        ]);
        $response = $this->actingAs($user)->postJson(route('home.sound-enabled'), [
            'sound_enabled' => 1,
        ]);
        $response->assertOk()->assertJson([
            'sound_enabled' => 1,
        ]);
        $user->refresh();
        $this->assertEquals(1, $user->sound_enabled);
    }

    public function test_completed_tasks_success(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password#123',
            'completed_tasks' => 0,
        ]);
        $response = $this->actingAs($user)->postJson(route('home.completed-tasks'), [
            'completed_tasks' => 6,
        ]);
        $response->assertOk()->assertJson([
            'completed_tasks' => 6,
        ]);
        $user->refresh();
        $this->assertEquals(6, $user->completed_tasks);
    }
}


