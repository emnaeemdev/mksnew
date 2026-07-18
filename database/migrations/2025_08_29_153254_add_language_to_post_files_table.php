<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('post_files', function (Blueprint $table) {

            $table->string('language', 2)->default('ar')->after('sort_order');
            $table->index('language');
        });
    }

    public function down(): void
    {
        Schema::table('post_files', function (Blueprint $table) {
            $table->dropIndex(['language']);
            $table->dropColumn('language');
        });
    }
};
