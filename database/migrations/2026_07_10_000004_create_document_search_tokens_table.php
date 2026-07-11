<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_search_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('token', 191);
            $table->timestamps();

            $table->unique(['document_id', 'token'], 'dst_document_token_unique');
            $table->index('token', 'dst_token_idx');
            $table->index(['token', 'document_id'], 'dst_token_document_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_search_tokens');
    }
};
