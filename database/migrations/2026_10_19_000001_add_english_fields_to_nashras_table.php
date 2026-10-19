<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nashras', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title_ar');
            $table->string('subtitle_en')->nullable()->after('subtitle_ar');
            $table->longText('content_en')->nullable()->after('content_ar');
        });
    }

    public function down(): void
    {
        Schema::table('nashras', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'subtitle_en', 'content_en']);
        });
    }
};
