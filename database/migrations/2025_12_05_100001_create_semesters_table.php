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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('year_id')->constrained('years')->onDelete('cascade');
            $table->integer('semester_number'); // 1 or 2
            $table->json('name'); // Multilingual: {"fr": "Semestre 1", "ar": "الفصل الأول"}
            $table->date('start_date');
            $table->date('end_date');
            $table->string('academic_year'); // e.g., "2024-2025"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Ensure unique semester number per year
            $table->unique(['year_id', 'semester_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};

