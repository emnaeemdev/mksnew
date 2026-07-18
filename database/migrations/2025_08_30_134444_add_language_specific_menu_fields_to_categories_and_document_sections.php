<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('show_in_menu_ar')->default(true)->after('show_in_menu');
            $table->boolean('show_in_menu_en')->default(true)->after('show_in_menu_ar');
            $table->integer('menu_order_ar')->nullable()->after('menu_order');
            $table->integer('menu_order_en')->nullable()->after('menu_order_ar');
        });

        Schema::table('document_sections', function (Blueprint $table) {
            $table->boolean('show_in_menu_ar')->default(true)->after('show_in_menu');
            $table->boolean('show_in_menu_en')->default(true)->after('show_in_menu_ar');
            $table->integer('menu_order_ar')->nullable()->after('menu_order');
            $table->integer('menu_order_en')->nullable()->after('menu_order_ar');
        });
    }

    public function down(): void
    {

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['show_in_menu_ar', 'show_in_menu_en', 'menu_order_ar', 'menu_order_en']);
        });

        Schema::table('document_sections', function (Blueprint $table) {
            $table->dropColumn(['show_in_menu_ar', 'show_in_menu_en', 'menu_order_ar', 'menu_order_en']);
        });
    }
};
