<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('nashras', function (Blueprint $table) {
            if (!Schema::hasColumn('nashras', 'download_count')) {
                
                $table->unsignedBigInteger('download_count')->default(0)->after('pdf_file');
            }
        });
    }

    
    public function down(): void
    {
        Schema::table('nashras', function (Blueprint $table) {
            if (Schema::hasColumn('nashras', 'download_count')) {
                $table->dropColumn('download_count');
            }
        });
    }
};
