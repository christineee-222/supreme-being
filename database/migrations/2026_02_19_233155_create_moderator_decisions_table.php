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
        Schema::create('moderator_decisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('moderator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->enum('decision', ['confirmed', 'dismissed', 'escalated']);
            $table->boolean('requires_cosign')->default(false);
            $table->foreignUuid('cosigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cosigned_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['moderator_id', 'created_at']);
            $table->index('report_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderator_decisions');
    }
};
