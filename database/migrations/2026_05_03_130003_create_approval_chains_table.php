<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_chains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_laporan_id')->constrained('jenis_laporans');
            $table->foreignId('departemen_id')->constrained('departements');
            $table->foreignId('approver_level_1_id')->constrained('users');
            $table->foreignId('approver_level_2_id')->constrained('users');
            $table->timestamps();

            $table->unique(['jenis_laporan_id', 'departemen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_chains');
    }
};
