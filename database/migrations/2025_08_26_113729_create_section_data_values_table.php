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
        Schema::create('section_data_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_data_id')->constrained('section_data')->onDelete('cascade');
            $table->foreignId('custom_field_id')->constrained()->onDelete('cascade');
            $table->json('value')->nullable();
            $table->timestamps();
            
            $table->unique(['section_data_id', 'custom_field_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_data_values');
    }
};
