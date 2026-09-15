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
        if (Schema::hasTable('tank_telemetries') && ! Schema::hasColumn('tank_telemetries', 'photo_path')) {
            Schema::table('tank_telemetries', function (Blueprint $table) {
                $table->string('photo_path', 255)->nullable()->after('notes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tank_telemetries') && Schema::hasColumn('tank_telemetries', 'photo_path')) {
            Schema::table('tank_telemetries', function (Blueprint $table) {
                $table->dropColumn('photo_path');
            });
        }
    }
};
