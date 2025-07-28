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
        Schema::create('riwayat_perbaikan_komputers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('asset_id')->constrained('komputers')->onDelete('cascade');
            $table->string('jenis_maintenance');
            $table->string('keterangan');
            $table->string('teknisi');
            $table->string('komponen_diganti')->nullable();
            $table->string('biaya_maintenance')->nullable();
            $table->string('hasil_maintenance')->nullable();
            $table->string('rekomendasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_perbaikan_komputers');
    }
};
