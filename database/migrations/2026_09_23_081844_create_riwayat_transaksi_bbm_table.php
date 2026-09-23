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
        Schema::create('riwayat_transaksi_bbm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tank_id')->constrained('tanks')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('jenis_transaksi', 20); // pemasukan, pemakaian
            $table->decimal('volume_awal', 10, 2)->default(0);
            $table->decimal('volume_perubahan', 10, 2)->default(0);
            $table->decimal('volume_akhir', 10, 2)->default(0);
            $table->decimal('ketinggian_awal_cm', 8, 2)->default(0);
            $table->decimal('ketinggian_akhir_cm', 8, 2)->default(0);
            $table->string('nomor_do', 100)->nullable();
            $table->string('unit_tujuan', 100)->nullable();
            $table->string('foto_bukti', 255)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_transaksi_bbm');
    }
};
