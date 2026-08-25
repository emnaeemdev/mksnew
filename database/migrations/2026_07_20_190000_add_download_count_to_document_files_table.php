<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_files', function (Blueprint $table) {
            if (!Schema::hasColumn('document_files', 'download_count')) {
                $table->unsignedInteger('download_count')->default(0)->after('sort_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_files', function (Blueprint $table) {
            if (Schema::hasColumn('document_files', 'download_count')) {
                $table->dropColumn('download_count');
            }
        });
    }
};
