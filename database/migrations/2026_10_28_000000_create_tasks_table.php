<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            // ユーザーを削除したら、そのユーザーのタスクも一緒に削除する
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // ストレッチの名前
            $table->string('name');
            // ストレッチを行う秒数（config/stretchtasks.php の options から選ぶ）
            $table->unsignedSmallInteger('countdown_seconds');
            // 伸ばす部位（入力しなくてもいい）
            $table->string('part')->nullable();
            // 期待できる効果（入力しなくてもいい）
            $table->string('effect')->nullable();
            $table->timestamps();
        });

        // すでに登録しているユーザーにも、最初のタスクをコピーしておく
        // config に今後キーが増えても壊れないように、使うキーだけを取り出す
        $now = now();

        foreach (DB::table('users')->pluck('id') as $userId) {
            DB::table('tasks')->insert(array_map(fn (array $item) => [
                ...Arr::only($item, ['name', 'countdown_seconds', 'part', 'effect']),
                'user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ], config('stretchtasks.items')));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
