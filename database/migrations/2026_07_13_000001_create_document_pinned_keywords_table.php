<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_pinned_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained('keywords')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('label_override', 255)->nullable();
            $table->timestamps();

            $table->unique('keyword_id');
            $table->index('sort_order');
        });

        if (Schema::hasTable('document_section_keyword')) {
            $rows = DB::table('document_section_keyword')
                ->select('keyword_id', DB::raw('MIN(sort_order) as sort_order'), DB::raw('MAX(label_override) as label_override'))
                ->groupBy('keyword_id')
                ->orderBy('sort_order')
                ->get();

            $now = now();
            foreach ($rows as $index => $row) {
                DB::table('document_pinned_keywords')->insert([
                    'keyword_id' => $row->keyword_id,
                    'sort_order' => $index,
                    'label_override' => $row->label_override,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            Schema::dropIfExists('document_section_keyword');
        }
    }

    public function down(): void
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

        Schema::dropIfExists('document_pinned_keywords');
    }
};
