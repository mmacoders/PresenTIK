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
        // First, drop the foreign key constraint on users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['bidang_id']);
            $table->dropColumn('bidang_id');
        });
        
        // Drop the bidangs table
        Schema::dropIfExists('bidangs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the bidangs table
        Schema::create('bidangs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Add bidang_id column back to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('bidang_id')->nullable()->constrained('bidangs')->onDelete('set null');
        });
    }
};