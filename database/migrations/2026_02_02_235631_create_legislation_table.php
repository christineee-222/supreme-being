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
        Schema::create('legislation', function (Blueprint $table) {
            $table->binary('id', 16)->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');

            $table->binary('user_id', 16)->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->binary('essence_numen_id', 16)->nullable()->index();
            $table->foreign('essence_numen_id')->references('id')->on('essence_numen')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legislation');
    }
};
