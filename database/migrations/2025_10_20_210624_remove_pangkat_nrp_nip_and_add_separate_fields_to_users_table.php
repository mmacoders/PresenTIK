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
        Schema::table('users', function (Blueprint $table) {
            // Remove the combined column
            $table->dropColumn('pangkat_nrp_nip');
            
            // Add separate columns
            $table->string('pangkat')->nullable()->after('name');
            $table->string('nrp')->nullable()->after('pangkat');
            $table->string('nip')->nullable()->after('nrp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove separate columns
            $table->dropColumn(['pangkat', 'nrp', 'nip']);
            
            // Add back the combined column
            $table->string('pangkat_nrp_nip')->nullable()->after('name');
        });
    }
};