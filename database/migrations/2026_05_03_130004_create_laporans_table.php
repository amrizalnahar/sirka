<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_laporan', 30)->unique();
            $table->string('judul_laporan');
            $table->foreignId('departemen_id')->constrained('departements');
            $table->foreignId('jenis_laporan_id')->constrained('jenis_laporans');
            $table->tinyInteger('periode_bulan');
            $table->year('periode_tahun');
            $table->enum('status', ['draft', 'submitted', 'revision', 'approved_1', 'approved_2', 'archived', 'rejected'])->default('draft');
            $table->text('catatan_pic')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->integer('revision_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['departemen_id', 'jenis_laporan_id', 'periode_bulan', 'periode_tahun', 'deleted_at'], 'laporan_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
