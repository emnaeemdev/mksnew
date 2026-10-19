<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nashras', function (Blueprint $table) {
            $table->string('featured_image_en')->nullable()->after('featured_image');
            $table->string('pdf_file_en')->nullable()->after('pdf_file');
        });
    }

    public function down(): void
    {
        Schema::table('nashras', function (Blueprint $table) {
            $table->dropColumn(['featured_image_en', 'pdf_file_en']);
        });
    }
};
