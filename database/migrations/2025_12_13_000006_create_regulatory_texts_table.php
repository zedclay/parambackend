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
        Schema::create('regulatory_texts', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // Multilingual
            $table->json('content')->nullable(); // Multilingual description
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->enum('target_audience', ['all', 'students', 'specific_specialite'])->default('all');
            $table->string('file_filename')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('image_filename')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_mime_type')->nullable();
            $table->integer('image_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulatory_texts');
    }
};
