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
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn(['waktu_keluar', 'lat_keluar', 'lng_keluar', 'status_lokasi_keluar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->time('waktu_keluar')->nullable();
            $table->decimal('lat_keluar', 10, 8)->nullable();
            $table->decimal('lng_keluar', 11, 8)->nullable();
            $table->string('status_lokasi_keluar')->nullable();
        });
    }
};