<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('document_pinned_keywords')) {
            return;
        }

        // احتفظ بالبيانات الحالية
        $existingPins = DB::table('document_pinned_keywords')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->values();

        Schema::dropIfExists('document_pinned_keywords');

        Schema::create('document_pinned_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_section_id')->constrained('document_sections')->cascadeOnDelete();
            $table->foreignId('keyword_id')->constrained('keywords')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('label_override', 255)->nullable();
            $table->timestamps();

            $table->unique(['document_section_id', 'keyword_id'], 'document_pinned_section_keyword_unique');
            $table->index(['document_section_id', 'sort_order'], 'document_pinned_section_sort_idx');
        });

        $sectionIds = Schema::hasTable('document_sections')
            ? DB::table('document_sections')->pluck('id')
            : collect();

        if ($existingPins->isEmpty() || $sectionIds->isEmpty()) {
            return;
        }

        // إن كانت الصفوف قديمة بدون قسم: انسخها لكل الأقسام
        $legacyPins = $existingPins->filter(fn ($pin) => empty($pin->document_section_id))->values();
        $sectionPins = $existingPins->filter(fn ($pin) => !empty($pin->document_section_id))->values();

        $now = now();

        if ($legacyPins->isNotEmpty()) {
            foreach ($sectionIds as $sectionId) {
                foreach ($legacyPins as $index => $pin) {
                    DB::table('document_pinned_keywords')->insertOrIgnore([
                        'document_section_id' => $sectionId,
                        'keyword_id' => $pin->keyword_id,
                        'sort_order' => $index,
                        'label_override' => $pin->label_override,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        foreach ($sectionPins as $pin) {
            DB::table('document_pinned_keywords')->insertOrIgnore([
                'document_section_id' => $pin->document_section_id,
                'keyword_id' => $pin->keyword_id,
                'sort_order' => $pin->sort_order ?? 0,
                'label_override' => $pin->label_override,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('document_pinned_keywords')) {
            return;
        }

        $rows = DB::table('document_pinned_keywords')
            ->select('keyword_id', DB::raw('MIN(sort_order) as sort_order'), DB::raw('MAX(label_override) as label_override'))
            ->groupBy('keyword_id')
            ->orderBy('sort_order')
            ->get();

        Schema::dropIfExists('document_pinned_keywords');

        Schema::create('document_pinned_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained('keywords')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('label_override', 255)->nullable();
            $table->timestamps();

            $table->unique('keyword_id');
            $table->index('sort_order');
        });

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
    }
};
