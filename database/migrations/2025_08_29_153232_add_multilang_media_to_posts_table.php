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
        Schema::table('posts', function (Blueprint $table) {
            // إضافة حقول الصور متعددة اللغات
            $table->string('featured_image_ar')->nullable()->after('featured_image');
            $table->string('featured_image_en')->nullable()->after('featured_image_ar');
            $table->string('background_image_ar')->nullable()->after('background_image');
            $table->string('background_image_en')->nullable()->after('background_image_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'featured_image_ar',
                'featured_image_en', 
                'background_image_ar',
                'background_image_en'
            ]);
        });
    }
};
