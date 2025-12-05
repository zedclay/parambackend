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
        Schema::create('years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('speciality_id')->constrained('specialities')->onDelete('cascade');
            $table->integer('year_number'); // 1, 2, 3, 4, 5
            $table->json('name'); // Multilingual: {"fr": "Première Année", "ar": "السنة الأولى"}
            $table->json('description')->nullable(); // Multilingual
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Ensure unique year number per speciality
            $table->unique(['speciality_id', 'year_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('years');
    }
};

