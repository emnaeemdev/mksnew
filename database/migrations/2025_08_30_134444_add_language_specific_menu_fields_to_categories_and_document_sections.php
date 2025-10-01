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
        // Add language-specific menu fields to categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('show_in_menu_ar')->default(true)->after('show_in_menu');
            $table->boolean('show_in_menu_en')->default(true)->after('show_in_menu_ar');
            $table->integer('menu_order_ar')->nullable()->after('menu_order');
            $table->integer('menu_order_en')->nullable()->after('menu_order_ar');
        });

        // Add language-specific menu fields to document_sections table
        Schema::table('document_sections', function (Blueprint $table) {
            $table->boolean('show_in_menu_ar')->default(true)->after('show_in_menu');
            $table->boolean('show_in_menu_en')->default(true)->after('show_in_menu_ar');
            $table->integer('menu_order_ar')->nullable()->after('menu_order');
            $table->integer('menu_order_en')->nullable()->after('menu_order_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove language-specific menu fields from categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['show_in_menu_ar', 'show_in_menu_en', 'menu_order_ar', 'menu_order_en']);
        });

        // Remove language-specific menu fields from document_sections table
        Schema::table('document_sections', function (Blueprint $table) {
            $table->dropColumn(['show_in_menu_ar', 'show_in_menu_en', 'menu_order_ar', 'menu_order_en']);
        });
    }
};
