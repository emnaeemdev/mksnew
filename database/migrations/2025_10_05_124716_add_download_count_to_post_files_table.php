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
        Schema::table('post_files', function (Blueprint $table) {
            // إضافة عمود العدّاد مع قيمة افتراضية
            if (!Schema::hasColumn('post_files', 'download_count')) {
                $table->unsignedBigInteger('download_count')->default(0)->after('file_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_files', function (Blueprint $table) {
            if (Schema::hasColumn('post_files', 'download_count')) {
                $table->dropColumn('download_count');
            }
        });
    }
};
