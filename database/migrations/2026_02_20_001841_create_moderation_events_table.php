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
        Schema::create('moderation_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_type', 50);
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('subject_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('report_id')->nullable()->constrained('reports')->nullOnDelete();
            $table->foreignUuid('violation_id')->nullable()->constrained('violations')->nullOnDelete();
            $table->foreignUuid('appeal_id')->nullable()->constrained('appeals')->nullOnDelete();
            $table->foreignUuid('moderator_application_id')->nullable()->constrained('moderator_applications')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('event_type');
            $table->index(['subject_user_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
            $table->index(['report_id', 'created_at']);
            $table->index('violation_id');
            $table->index('appeal_id');
            $table->index('moderator_application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderation_events');
    }
};
