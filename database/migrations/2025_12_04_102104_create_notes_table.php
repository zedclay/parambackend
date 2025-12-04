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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->nullable()->constrained('modules')->onDelete('cascade');
            $table->foreignId('specialite_id')->nullable()->constrained('specialities')->onDelete('cascade');
            $table->foreignId('uploader_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_student_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('filename'); // Original filename
            $table->string('stored_filename'); // Hashed filename on disk
            $table->string('file_path'); // Storage path
            $table->string('mime_type'); // e.g., 'application/pdf', 'image/jpeg'
            $table->bigInteger('file_size'); // bytes
            $table->enum('visibility', ['private', 'module', 'specialite'])->default('private');
            $table->integer('download_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
