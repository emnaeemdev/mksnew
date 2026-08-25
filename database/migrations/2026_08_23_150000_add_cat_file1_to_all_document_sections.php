<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var list<string> */
    private array $catFileOptions = [
        'الجامعة',
        'الآثار',
        'الصحافة',
        'التخصيص',
        'المنح والقروض',
    ];

    public function up(): void
    {
        $optionsJson = json_encode($this->catFileOptions, JSON_UNESCAPED_UNICODE);

        foreach ([1, 2, 3, 4] as $sectionId) {
            $exists = DB::table('document_custom_fields')
                ->where('section_id', $sectionId)
                ->where('name', 'cat_file1')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('document_custom_fields')->insert([
                'section_id' => $sectionId,
                'name' => 'cat_file1',
                'label' => 'التصنيف وفقًا للملف',
                'type' => 'select',
                'options' => $optionsJson,
                'is_required' => false,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('document_custom_fields')
            ->where('name', 'cat_file1')
            ->update([
                'options' => $optionsJson,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $fieldIds = DB::table('document_custom_fields')
            ->whereIn('section_id', [1, 2, 3, 4])
            ->where('name', 'cat_file1')
            ->pluck('id');

        if ($fieldIds->isNotEmpty()) {
            DB::table('document_field_values')->whereIn('field_id', $fieldIds)->delete();
            DB::table('document_custom_fields')->whereIn('id', $fieldIds)->delete();
        }

        DB::table('document_custom_fields')
            ->where('name', 'cat_file1')
            ->where('section_id', 5)
            ->update([
                'options' => json_encode(['الجامعة', 'الآثار', 'الصحافة'], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
    }
};
