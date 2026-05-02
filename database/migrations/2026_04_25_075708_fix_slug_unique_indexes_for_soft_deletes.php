<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->unique(['slug', 'deleted_at']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->unique(['slug', 'deleted_at']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->unique(['slug', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug', 'deleted_at']);
            $table->unique(['slug']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique(['slug', 'deleted_at']);
            $table->unique(['slug']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique(['slug', 'deleted_at']);
            $table->unique(['slug']);
        });
    }
};
