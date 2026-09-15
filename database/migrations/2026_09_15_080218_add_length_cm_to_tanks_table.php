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
        if (Schema::hasTable('tanks') && ! Schema::hasColumn('tanks', 'length_cm')) {
            Schema::table('tanks', function (Blueprint $table) {
                $table->decimal('length_cm', 8, 2)->default(200.00)->after('capacity_liters');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tanks') && Schema::hasColumn('tanks', 'length_cm')) {
            Schema::table('tanks', function (Blueprint $table) {
                $table->dropColumn('length_cm');
            });
        }
    }
};
