<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('podcasts', function (Blueprint $table) {
            if (!Schema::hasColumn('podcasts', 'series_label')) {
                $table->string('series_label')->nullable()->after('content');
            }
        });

        Schema::table('podcast_tracks', function (Blueprint $table) {
            if (!Schema::hasColumn('podcast_tracks', 'cover_image_path')) {
                $table->string('cover_image_path')->nullable()->after('description');
            }
            if (!Schema::hasColumn('podcast_tracks', 'spotify_url')) {
                $table->string('spotify_url')->nullable()->after('cover_image_path');
            }
            if (!Schema::hasColumn('podcast_tracks', 'apple_podcasts_url')) {
                $table->string('apple_podcasts_url')->nullable()->after('spotify_url');
            }
            if (!Schema::hasColumn('podcast_tracks', 'soundcloud_url')) {
                $table->string('soundcloud_url')->nullable()->after('apple_podcasts_url');
            }
            if (!Schema::hasColumn('podcast_tracks', 'youtube_url')) {
                $table->string('youtube_url')->nullable()->after('soundcloud_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('podcasts', function (Blueprint $table) {
            if (Schema::hasColumn('podcasts', 'series_label')) {
                $table->dropColumn('series_label');
            }
        });

        Schema::table('podcast_tracks', function (Blueprint $table) {
            foreach (['cover_image_path', 'spotify_url', 'apple_podcasts_url', 'soundcloud_url', 'youtube_url'] as $col) {
                if (Schema::hasColumn('podcast_tracks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
