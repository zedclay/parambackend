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
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // Multilingual title
            $table->json('subtitle')->nullable(); // Multilingual subtitle
            $table->string('image_path')->nullable();
            $table->string('image_filename')->nullable();
            $table->integer('order')->default(0); // Order of display
            $table->boolean('is_active')->default(true);
            $table->string('gradient')->nullable(); // CSS gradient classes (e.g., 'from-blue-600 to-cyan-500')
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
