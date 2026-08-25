<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // destorya_date holds full dates (YYYY-MM-DD) but was mislabeled as "سنة الحكم" (text).
        // Align with edarya_date / naqd_date: label «تاريخ الإصدار», type date.
        DB::table('document_custom_fields')
            ->where('section_id', 4)
            ->where('name', 'destorya_date')
            ->whereNull('deleted_at')
            ->update([
                'label' => 'تاريخ الإصدار',
                'type' => 'date',
                'sort_order' => 5,
                'updated_at' => now(),
            ]);

        DB::table('document_custom_fields')
            ->where('section_id', 4)
            ->where('name', 'destorya_year')
            ->whereNull('deleted_at')
            ->update([
                'sort_order' => 3,
                'updated_at' => now(),
            ]);

        // Optional clarity for laws: release date label closer to legacy meaning
        DB::table('document_custom_fields')
            ->where('section_id', 1)
            ->where('name', 'law_date')
            ->whereNull('deleted_at')
            ->where('label', 'تاريخ الوثيقة')
            ->update([
                'label' => 'تاريخ الإصدار',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('document_custom_fields')
            ->where('section_id', 4)
            ->where('name', 'destorya_date')
            ->update([
                'label' => 'سنة الحكم',
                'type' => 'text',
                'sort_order' => 3,
                'updated_at' => now(),
            ]);

        DB::table('document_custom_fields')
            ->where('section_id', 4)
            ->where('name', 'destorya_year')
            ->update([
                'sort_order' => 5,
                'updated_at' => now(),
            ]);

        DB::table('document_custom_fields')
            ->where('section_id', 1)
            ->where('name', 'law_date')
            ->update([
                'label' => 'تاريخ الوثيقة',
                'updated_at' => now(),
            ]);
    }
};
