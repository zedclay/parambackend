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
        Schema::create('schedule_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->string('image_path'); // Path to the uploaded image (required)
            $table->string('original_filename')->nullable(); // Original filename
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade'); // Admin who uploaded
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // One active image per semester
            $table->unique('semester_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_images');
    }
};

