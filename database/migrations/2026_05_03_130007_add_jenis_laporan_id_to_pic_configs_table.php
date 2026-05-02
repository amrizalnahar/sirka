<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pic_configs', function (Blueprint $table) {
            $table->foreignId('jenis_laporan_id')->nullable()->after('departemen_id')->constrained('jenis_laporans');
        });
    }

    public function down(): void
    {
        Schema::table('pic_configs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jenis_laporan_id');
        });
    }
};
