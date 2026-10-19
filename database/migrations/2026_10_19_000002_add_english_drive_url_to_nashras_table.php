<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nashras', function (Blueprint $table) {
            $table->text('google_drive_url_en')->nullable()->after('google_drive_url');
            $table->string('google_sheet_id_en')->nullable()->after('google_sheet_id');
        });
    }

    public function down(): void
    {
        Schema::table('nashras', function (Blueprint $table) {
            $table->dropColumn(['google_drive_url_en', 'google_sheet_id_en']);
        });
    }
};
