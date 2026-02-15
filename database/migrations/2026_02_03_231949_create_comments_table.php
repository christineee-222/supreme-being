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
        Schema::create('comments', function (Blueprint $table) {
            $table->binary('id', 16)->primary();

            $table->binary('user_id', 16)->index();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->binary('forum_id', 16)->index();
            $table->foreign('forum_id')->references('id')->on('forums')->cascadeOnDelete();

            $table->text('body');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
