<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // يغطي نطاق القسم المنشور ثم ترتيب النتائج الافتراضي.
            $table->index(
                ['section_id', 'is_published', 'sort_order', 'published_at'],
                'documents_search_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_search_sort_idx');
        });
    }
};
