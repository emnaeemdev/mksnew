<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_section_keyword', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_section_id')->constrained('document_sections')->cascadeOnDelete();
            $table->foreignId('keyword_id')->constrained('keywords')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('label_override', 255)->nullable();
            $table->timestamps();

            $table->unique(['document_section_id', 'keyword_id']);
            $table->index(['document_section_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_section_keyword');
    }
};
