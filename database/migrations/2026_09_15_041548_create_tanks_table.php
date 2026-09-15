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
        Schema::create('tanks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('capacity_liters', 10, 2)->default(100.00);
            $table->decimal('length_cm', 8, 2)->default(200.00);
            $table->decimal('width_cm', 8, 2)->default(90.00);
            $table->decimal('height_cm', 8, 2)->default(70.00);
            $table->decimal('diameter_cm', 8, 2)->default(90.00);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tanks');
    }
};
