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
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug', 'deleted_at']);
            $table->unique(['module_type', 'slug', 'deleted_at']);
            $table->unique(['module_type', 'name', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['module_type', 'slug', 'deleted_at']);
            $table->dropUnique(['module_type', 'name', 'deleted_at']);
            $table->unique(['slug', 'deleted_at']);
        });
    }
};
