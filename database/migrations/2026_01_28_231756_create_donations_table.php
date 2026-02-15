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
        Schema::create('donations', function (Blueprint $table) {
            $table->binary('id', 16)->primary();
            $table->integer('amount');
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending');

            $table->binary('user_id', 16)->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->binary('essence_numen_id', 16)->nullable()->index();
            $table->foreign('essence_numen_id')->references('id')->on('essence_numen')->nullOnDelete();

            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->string('stripe_webhook_event_id')->nullable()->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
