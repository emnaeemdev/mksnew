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
        Schema::table('nashras', function (Blueprint $table) {
            if (!Schema::hasColumn('nashras', 'download_count')) {
                // ضع العمود بعد عمود pdf_file الموجود
                $table->unsignedBigInteger('download_count')->default(0)->after('pdf_file');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nashras', function (Blueprint $table) {
            if (Schema::hasColumn('nashras', 'download_count')) {
                $table->dropColumn('download_count');
            }
        });
    }
};
