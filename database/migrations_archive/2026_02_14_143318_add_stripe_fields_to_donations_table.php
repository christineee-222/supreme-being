<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->unique()->after('status');
            $table->string('stripe_payment_intent_id')->nullable()->unique()->after('stripe_checkout_session_id');
            $table->string('stripe_webhook_event_id')->nullable()->unique()->after('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropUnique(['stripe_checkout_session_id']);
            $table->dropUnique(['stripe_payment_intent_id']);
            $table->dropUnique(['stripe_webhook_event_id']);

            $table->dropColumn([
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'stripe_webhook_event_id',
            ]);
        });
    }
};
