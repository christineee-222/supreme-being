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
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('reported_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reportable_type', 100);
            $table->uuid('reportable_id');
            $table->enum('reason', ['hate_speech', 'violence', 'manipulation', 'spam', 'harassment', 'language', 'other']);
            $table->text('reporter_note')->nullable();
            $table->enum('status', ['pending', 'assigned', 'under_review', 'resolved', 'dismissed', 'escalated'])->default('pending');
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignUuid('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('resolution', ['violation_confirmed', 'dismissed', 'escalated_to_admin'])->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_against_moderator')->default(false);
            $table->json('ai_analysis')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('reporter_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index(['reported_user_id', 'created_at']);
            $table->index(['reportable_type', 'reportable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
