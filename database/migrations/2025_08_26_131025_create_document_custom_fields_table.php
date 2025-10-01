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
        Schema::create('document_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('document_sections')->onDelete('cascade');
            $table->string('name');
            $table->string('label');
            $table->string('type');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('validation_rules')->nullable();
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['section_id', 'is_active', 'sort_order']);
            $table->unique(['section_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_custom_fields');
    }
};
