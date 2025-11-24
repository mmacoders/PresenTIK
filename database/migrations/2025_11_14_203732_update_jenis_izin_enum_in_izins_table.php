<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum values for jenis_izin column
        DB::statement("ALTER TABLE izins MODIFY jenis_izin ENUM('kedukaan', 'sakit', 'keperluan_mendesak', 'lainnya', 'penuh', 'parsial')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE izins MODIFY jenis_izin ENUM('penuh', 'parsial')");
    }
};