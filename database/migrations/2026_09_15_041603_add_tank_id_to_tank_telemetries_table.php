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
        Schema::table('tank_telemetries', function (Blueprint $table) {
            $table->foreignId('tank_id')->nullable()->after('id')->constrained('tanks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tank_telemetries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tank_id');
        });
    }
};
