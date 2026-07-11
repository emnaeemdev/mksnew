<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // يسرّع تصفية القسم + المنشور قبل البحث النصي
            $table->index(['section_id', 'is_published', 'published_at'], 'documents_section_published_idx');
        });

        Schema::table('document_field_values', function (Blueprint $table) {
            $table->index(['field_id', 'document_id'], 'dfv_field_document_idx');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_section_published_idx');
        });

        Schema::table('document_field_values', function (Blueprint $table) {
            $table->dropIndex('dfv_field_document_idx');
        });
    }
};
