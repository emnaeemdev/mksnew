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
        Schema::create('nashras', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar'); // العنوان العربي
            $table->string('subtitle_ar')->nullable(); // العنوان الفرعي
            $table->text('google_drive_url'); // رابط جوجل درايف
            $table->string('google_sheet_id')->nullable(); // معرف جوجل شيت
            $table->datetime('published_at')->nullable(); // تاريخ النشر
            $table->longText('content_ar')->nullable(); // المحتوى
            $table->string('featured_image')->nullable(); // الصورة المميزة
            $table->string('pdf_file')->nullable(); // ملف النشرة PDF
            $table->boolean('status')->default(true); // حالة النشر
            $table->integer('sort_order')->default(0); // ترتيب العرض
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nashras');
    }
};
