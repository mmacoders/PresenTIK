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
        Schema::table('izins', function (Blueprint $table) {
            // Add tanggal_mulai if not exists
            if (!Schema::hasColumn('izins', 'tanggal_mulai')) {
                $table->date('tanggal_mulai')->nullable()->after('keterangan');
            }
            
            // Add tanggal_selesai if not exists
            if (!Schema::hasColumn('izins', 'tanggal_selesai')) {
                $columnName = Schema::hasColumn('izins', 'tanggal_mulai') ? 'tanggal_mulai' : 'keterangan';
                $table->date('tanggal_selesai')->nullable()->after($columnName);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izins', function (Blueprint $table) {
            if (Schema::hasColumn('izins', 'tanggal_selesai')) {
                $table->dropColumn('tanggal_selesai');
            }
        });
    }
};