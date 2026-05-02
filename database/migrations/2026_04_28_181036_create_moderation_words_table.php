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
        Schema::create('moderation_words', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->enum('category', ['vulgar', 'sara', 'hate_speech', 'spam']);
            $table->enum('severity', ['low', 'medium', 'high'])->default('medium');
            $table->boolean('is_regex')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderation_words');
    }
};
