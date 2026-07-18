<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('document_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('field_id')->constrained('document_custom_fields')->onDelete('cascade');
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'field_id']);
            $table->index(['field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_field_values');
    }
};
