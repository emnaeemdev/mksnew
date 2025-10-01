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
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('show_in_slider_ar')->default(false)->after('show_in_other_reports');
            $table->boolean('show_in_releases_ar')->default(false)->after('show_in_slider_ar');
            $table->boolean('show_in_other_reports_ar')->default(false)->after('show_in_releases_ar');
            $table->boolean('show_in_slider_en')->default(false)->after('show_in_other_reports_ar');
            $table->boolean('show_in_releases_en')->default(false)->after('show_in_slider_en');
            $table->boolean('show_in_other_reports_en')->default(false)->after('show_in_releases_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'show_in_slider_ar',
                'show_in_releases_ar',
                'show_in_other_reports_ar',
                'show_in_slider_en',
                'show_in_releases_en',
                'show_in_other_reports_en'
            ]);
        });
    }
};
