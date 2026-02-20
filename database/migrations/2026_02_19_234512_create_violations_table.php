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
        Schema::create('violations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('report_id')->nullable()->constrained('reports')->nullOnDelete();
            $table->foreignUuid('moderator_decision_id')->unique()->constrained('moderator_decisions')->cascadeOnDelete();
            $table->foreignUuid('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rule_reference', 20);
            $table->tinyInteger('violation_number')->unsigned();
            $table->enum('consequence_applied', ['7_day', '30_day', 'indefinite']);
            $table->timestamp('restriction_ends_at')->nullable();
            $table->boolean('applied_to_user')->default(false);
            $table->text('moderator_note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
