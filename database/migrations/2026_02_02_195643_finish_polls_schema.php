<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            if (!Schema::hasColumn('polls', 'title')) {
                $table->string('title');
            }

            if (!Schema::hasColumn('polls', 'description')) {
                $table->text('description')->nullable();
            }

            if (!Schema::hasColumn('polls', 'status')) {
                $table->string('status')->default('draft');
            }

            if (!Schema::hasColumn('polls', 'starts_at')) {
                $table->timestamp('starts_at')->nullable();
            }

            if (!Schema::hasColumn('polls', 'ends_at')) {
                $table->timestamp('ends_at')->nullable();
            }

            // Ensure user_id column exists
            if (!Schema::hasColumn('polls', 'user_id')) {
                $table->foreignId('user_id')->nullable();
            }
        });

        // Add foreign key ONLY if not SQLite
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('polls', function (Blueprint $table) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('polls', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        Schema::table('polls', function (Blueprint $table) {
            if (Schema::hasColumn('polls', 'user_id')) {
                $table->dropColumn('user_id');
            }

            $table->dropColumn([
                'title',
                'description',
                'status',
                'starts_at',
                'ends_at',
            ]);
        });
    }
};

