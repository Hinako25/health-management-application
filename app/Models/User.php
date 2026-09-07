<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $new_email
 * @property string|null $change_password
 * @property string|null $remember_token
 * @property int|null $total_goals
 * @property int|null $daily_tasks
 * @property int|null $completed_stretch_task_ids
 * @property int|null $completed_tasks
 * @property date|null $last_reset_date
 * @property string|null $goal_reward
 * @property json|null $daily_chart_data
 * @property int|null $countdown_minutes
 * @property boolean $sound_enabled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'email', 
    'password', 
    'new_email', 
    'change_password', 
    'countdown_minutes', 
    'completed_stretch_task_ids', 
    'total_goals', 
    'daily_tasks', 
    'completed_tasks', 
    'last_reset_date', 
    'goal_reward', 
    'daily_chart_data', 
    'sound_enabled', 
    'created_at', 
    'updated_at'
])]
#[Hidden([
    'password', 
    'new_email',
    'change_password',
    'remember_token',
    'created_at', 
    'updated_at'
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'completed_stretch_task_ids' => 'array',
            'sound_enabled' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Timecard, $this>
     */
    public function timecards(): HasMany
    {
        return $this->hasMany(Timecard::class);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::length($this->email) > 1
            ? Str::substr($this->email, 0, 1).Str::substr($this->email, -1)
            : $this->email;
    }
}
