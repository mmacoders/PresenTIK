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
        Schema::table('system_settings', function (Blueprint $table) {
            $table->integer('grace_period_minutes')->default(10); // 10 minutes grace period
            $table->time('cutoff_time')->default('10:00:00'); // Cutoff time at 10:00 AM
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['grace_period_minutes', 'cutoff_time']);
        });
    }
};