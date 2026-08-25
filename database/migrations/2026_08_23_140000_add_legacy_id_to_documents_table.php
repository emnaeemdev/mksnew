<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'legacy_id')) {
                $table->unsignedBigInteger('legacy_id')->nullable()->after('id');
                $table->unique(['section_id', 'legacy_id'], 'documents_section_legacy_id_unique');
                $table->index('legacy_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'legacy_id')) {
                $table->dropUnique('documents_section_legacy_id_unique');
                $table->dropIndex(['legacy_id']);
                $table->dropColumn('legacy_id');
            }
        });
    }
};
