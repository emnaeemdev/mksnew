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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title_ar');
            $table->string('title_en');
            $table->string('slug')->unique();
            $table->longText('content_ar');
            $table->longText('content_en');
            $table->string('background_image')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->enum('status', ['draft', 'under_review', 'published'])->default('draft');
            $table->boolean('show_in_slider')->default(false);
            $table->boolean('show_in_releases')->default(false);
            $table->boolean('show_in_other_reports')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
