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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('year_id')->nullable()->after('role')->constrained('years')->onDelete('set null');
            $table->foreignId('filiere_id')->nullable()->after('year_id')->constrained('filieres')->onDelete('set null');
            $table->foreignId('speciality_id')->nullable()->after('filiere_id')->constrained('specialities')->onDelete('set null');
            $table->foreignId('group_id')->nullable()->after('speciality_id')->constrained('groups')->onDelete('set null');
            $table->string('student_number')->nullable()->unique()->after('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['year_id']);
            $table->dropForeign(['filiere_id']);
            $table->dropForeign(['speciality_id']);
            $table->dropForeign(['group_id']);
            $table->dropColumn(['year_id', 'filiere_id', 'speciality_id', 'group_id', 'student_number']);
        });
    }
};

