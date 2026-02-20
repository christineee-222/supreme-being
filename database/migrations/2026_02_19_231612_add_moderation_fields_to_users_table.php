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
            $table->boolean('is_moderator_probationary')->default(false)->after('is_admin');
            $table->tinyInteger('violation_count')->unsigned()->default(0)->after('is_moderator_probationary');
            $table->tinyInteger('appeal_count')->unsigned()->default(0)->after('violation_count');
            $table->boolean('is_indefinitely_restricted')->default(false)->after('appeal_count');
            $table->timestamp('restriction_ends_at')->nullable()->after('is_indefinitely_restricted');
            $table->timestamp('next_appeal_eligible_at')->nullable()->after('restriction_ends_at');
            $table->timestamp('account_created_at')->nullable()->after('next_appeal_eligible_at');
            $table->boolean('is_system_bot')->default(false)->after('account_created_at');
            $table->json('moderation_metadata')->nullable()->after('is_system_bot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_moderator_probationary',
                'violation_count',
                'appeal_count',
                'is_indefinitely_restricted',
                'restriction_ends_at',
                'next_appeal_eligible_at',
                'account_created_at',
                'is_system_bot',
                'moderation_metadata',
            ]);
        });
    }
};
