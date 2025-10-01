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
        Schema::table('document_sections', function (Blueprint $table) {
            $table->boolean('show_in_menu')->default(false)->after('is_active');
            $table->integer('menu_order')->default(0)->after('show_in_menu');
            $table->boolean('is_dropdown')->default(false)->after('menu_order');
            $table->string('dropdown_title')->nullable()->after('is_dropdown');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_sections', function (Blueprint $table) {
            $table->dropColumn(['show_in_menu', 'menu_order', 'is_dropdown', 'dropdown_title']);
        });
    }
};
