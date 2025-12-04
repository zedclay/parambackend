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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialite_id')->constrained('specialities')->onDelete('cascade');
            $table->string('code'); // e.g., "MOD101"
            $table->json('title'); // Multilingual
            $table->json('description')->nullable(); // Multilingual
            $table->integer('credits')->nullable();
            $table->integer('hours')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
