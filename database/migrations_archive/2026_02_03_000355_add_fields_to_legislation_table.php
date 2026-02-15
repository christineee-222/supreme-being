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
        Schema::table('legislation', function (Blueprint $table) {
            if (! Schema::hasColumn('legislation', 'title')) {
                $table->string('title');
            }

            if (! Schema::hasColumn('legislation', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('legislation', 'status')) {
                $table->string('status')->default('draft');
            }

            if (! Schema::hasColumn('legislation', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('legislation', 'essence_numen_id')) {
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
        Schema::table('legislation', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['essence_numen_id']);

            $table->dropColumn([
                'title',
                'description',
                'status',
                'user_id',
                'essence_numen_id',
            ]);
        });
    }
};
