<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ストレッチを目標回数やった日が連続で何日続いているか
            if (! Schema::hasColumn('users', 'streak_days')) {
                $table->unsignedSmallInteger('streak_days')->default(0);
            }

            // 最後にストレッチを目標回数やった日（連続が途切れたかの判定に使う）
            if (! Schema::hasColumn('users', 'last_streak_date')) {
                $table->date('last_streak_date')->nullable();
            }

            // 連続日数のご褒美でたまった合計ポイント
            if (! Schema::hasColumn('users', 'points')) {
                $table->unsignedInteger('points')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('users', 'streak_days') ? 'streak_days' : null,
                Schema::hasColumn('users', 'last_streak_date') ? 'last_streak_date' : null,
                Schema::hasColumn('users', 'points') ? 'points' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
