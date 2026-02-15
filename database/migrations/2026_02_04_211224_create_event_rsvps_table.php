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
        Schema::create('event_rsvps', function (Blueprint $table) {
            $table->binary('id', 16)->primary();

            $table->binary('user_id', 16)->index();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->binary('event_id', 16)->index();
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();

            $table->string('status')->default('going');

            $table->timestamps();

            $table->unique(['user_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_rsvps');
    }
};
