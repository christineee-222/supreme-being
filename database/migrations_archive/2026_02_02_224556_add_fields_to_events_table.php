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
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'title')) {
                $table->string('title');
            }

            if (! Schema::hasColumn('events', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('events', 'status')) {
                $table->string('status')->default('draft');
            }

            if (! Schema::hasColumn('events', 'starts_at')) {
                $table->timestamp('starts_at')->nullable();
            }

            if (! Schema::hasColumn('events', 'ends_at')) {
                $table->timestamp('ends_at')->nullable();
            }

            if (! Schema::hasColumn('events', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('events', 'essence_numen_id')) {
                $table->foreignId('essence_numen_id')
                    ->nullable()
                    ->constrained('essence_numen')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['essence_numen_id']);

            $table->dropColumn([
                'title',
                'description',
                'status',
                'starts_at',
                'ends_at',
                'user_id',
                'essence_numen_id',
            ]);
        });
    }
};
