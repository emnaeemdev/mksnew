<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('nashras', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar');
            $table->string('subtitle_ar')->nullable();
            $table->text('google_drive_url');
            $table->string('google_sheet_id')->nullable();
            $table->datetime('published_at')->nullable();
            $table->longText('content_ar')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('pdf_file')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nashras');
    }
};
