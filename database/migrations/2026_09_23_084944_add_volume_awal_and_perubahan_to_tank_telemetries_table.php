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
            $table->decimal('volume_awal', 10, 2)->default(0)->after('user_id');
            $table->decimal('volume_perubahan', 10, 2)->default(0)->after('volume_awal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tank_telemetries', function (Blueprint $table) {
            $table->dropColumn(['volume_awal', 'volume_perubahan']);
        });
    }
};
