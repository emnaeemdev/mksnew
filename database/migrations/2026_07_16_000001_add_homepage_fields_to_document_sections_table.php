<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_sections', function (Blueprint $table) {
            $table->boolean('show_on_homepage')->default(false)->after('sort_order');
            $table->string('home_icon')->nullable()->after('show_on_homepage');
            $table->string('home_label')->nullable()->after('home_icon');
            $table->unsignedInteger('home_sort_order')->default(0)->after('home_label');
        });

        $defaults = [
            1 => ['home_label' => 'قوانين', 'home_icon' => 'fa-balance-scale', 'home_sort_order' => 1],
            2 => ['home_label' => 'قرارات', 'home_icon' => 'fa-clipboard-check', 'home_sort_order' => 2],
            4 => ['home_label' => 'المحكمة الدستورية', 'home_icon' => 'fa-gavel', 'home_sort_order' => 3],
            5 => ['home_label' => 'محكمة النقض', 'home_icon' => 'fa-building', 'home_sort_order' => 4],
            3 => ['home_label' => 'المحكمة الادارية العليا', 'home_icon' => 'fa-institution', 'home_sort_order' => 5],
        ];

        foreach ($defaults as $id => $data) {
            DB::table('document_sections')
                ->where('id', $id)
                ->update(array_merge($data, ['show_on_homepage' => true]));
        }
    }

    public function down(): void
    {
        Schema::table('document_sections', function (Blueprint $table) {
            $table->dropColumn(['show_on_homepage', 'home_icon', 'home_label', 'home_sort_order']);
        });
    }
};
