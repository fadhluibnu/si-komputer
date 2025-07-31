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
        Schema::create('komputers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->string('kode_barang');
            $table->string('nama_komputer')->default('00000');
            $table->string('merek_komputer');
            $table->integer('tahun_pengadaan');
            $table->string('spesifikasi_ram');
            $table->string('spesifikasi_vga');
            $table->string('spesifikasi_processor');
            $table->string('spesifikasi_penyimpanan');
            $table->string('sistem_operasi');
            $table->string('nama_pengguna_sekarang');
            $table->string('kesesuaian_pc');
            $table->string('kondisi_komputer');
            $table->string('keterangan_kondisi');
            $table->string('penggunaan_sekarang');
            $table->foreignId('ruangan_id');
            $table->string('barcode');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komputers');
    }
};
