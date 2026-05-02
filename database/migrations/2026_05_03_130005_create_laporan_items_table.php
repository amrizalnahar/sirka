<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporans')->cascadeOnDelete();
            $table->string('kode_kegiatan', 20);
            $table->string('nama_kegiatan');
            $table->string('kode_akun', 20);
            $table->string('kode_kategori', 20);
            $table->string('satuan', 50);
            $table->decimal('volume_rencana', 15, 2);
            $table->decimal('volume_realisasi', 15, 2);
            $table->decimal('pagu_anggaran', 18, 2);
            $table->decimal('realisasi_anggaran', 18, 2);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status_kegiatan', ['selesai', 'berlangsung', 'belum_dimulai']);
            $table->text('keterangan')->nullable();
            $table->decimal('persen_realisasi_anggaran', 6, 2)->storedAs('(CASE WHEN pagu_anggaran > 0 THEN (realisasi_anggaran / pagu_anggaran) * 100 ELSE 0 END)');
            $table->decimal('persen_realisasi_volume', 6, 2)->storedAs('(CASE WHEN volume_rencana > 0 THEN (volume_realisasi / volume_rencana) * 100 ELSE 0 END)');
            $table->decimal('sisa_anggaran', 18, 2)->storedAs('(pagu_anggaran - realisasi_anggaran)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_items');
    }
};
