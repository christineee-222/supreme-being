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
    });

    Schema::table('polls', function (Blueprint $table) {
        $table->unsignedBigInteger('user_id')->nullable()->change();
});


    // add FK separately (since column already exists)
    Schema::table('polls', function (Blueprint $table) {
        $table->foreign('user_id')
              ->references('id')
              ->on('users')
              ->nullOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('polls', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
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
