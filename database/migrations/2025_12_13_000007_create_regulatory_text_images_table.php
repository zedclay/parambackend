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
        Schema::create('regulatory_text_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulatory_text_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('image_filename');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulatory_text_images');
    }
};
