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
        $table->id();

        $table->string('title');
        $table->text('description')->nullable();
        $table->string('status')->default('draft');

        $table->foreignId('user_id')
              ->nullable()
              ->constrained()
              ->nullOnDelete();

        $table->foreignId('essence_numen_id')
              ->nullable()
              ->constrained('essence_numen')
              ->nullOnDelete();

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
