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
            if (! Schema::hasColumn('users', 'completed_tasks')) {
                $table->unsignedSmallInteger('completed_tasks')->default(0);
            }

            if (! Schema::hasColumn('users', 'last_reset_date')) {
                $table->date('last_reset_date')->nullable();
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
                Schema::hasColumn('users', 'completed_tasks') ? 'completed_tasks' : null,
                Schema::hasColumn('users', 'last_reset_date') ? 'last_reset_date' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
