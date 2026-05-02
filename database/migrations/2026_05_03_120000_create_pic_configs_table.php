<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pic_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departemen_id')->constrained('departements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->timestamps();
            $table->unique(['departemen_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pic_configs');
    }
};
