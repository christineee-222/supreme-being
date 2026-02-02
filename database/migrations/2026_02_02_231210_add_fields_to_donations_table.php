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
    Schema::table('donations', function (Blueprint $table) {
        if (!Schema::hasColumn('donations', 'amount')) {
            $table->integer('amount'); // store in cents
        }

        if (!Schema::hasColumn('donations', 'currency')) {
            $table->string('currency', 3)->default('USD');
        }

        if (!Schema::hasColumn('donations', 'status')) {
            $table->string('status')->default('pending');
        }

        if (!Schema::hasColumn('donations', 'user_id')) {
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
        }

        if (!Schema::hasColumn('donations', 'essence_numen_id')) {
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
    Schema::table('donations', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropForeign(['essence_numen_id']);

        $table->dropColumn([
            'amount',
            'currency',
            'status',
            'user_id',
            'essence_numen_id',
        ]);
    });
}

};
