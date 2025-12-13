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
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('target_audience');
            $table->string('pdf_path')->nullable()->after('image_path');
            $table->string('image_filename')->nullable()->after('pdf_path');
            $table->string('pdf_filename')->nullable()->after('image_filename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'pdf_path', 'image_filename', 'pdf_filename']);
        });
    }
};
