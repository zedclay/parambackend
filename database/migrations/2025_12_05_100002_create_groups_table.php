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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('speciality_id')->constrained('specialities')->onDelete('cascade');
            $table->foreignId('year_id')->constrained('years')->onDelete('cascade');
            $table->string('name'); // e.g., "G1", "G2"
            $table->string('code')->unique(); // Unique identifier: e.g., "LPISP-1-G1"
            $table->integer('capacity')->nullable(); // Max number of students
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Ensure unique group name per year and speciality
            $table->unique(['speciality_id', 'year_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};

