<?php

use App\Models\Podcast;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('podcast_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('podcast_id')->constrained('podcasts')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('audio_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['podcast_id', 'sort_order']);
        });

        // نقل الملفات الحالية إلى جدول الحلقات
        $podcasts = DB::table('podcasts')->whereNotNull('audio_path')->where('audio_path', '!=', '')->get();
        foreach ($podcasts as $podcast) {
            $exists = DB::table('podcast_tracks')->where('podcast_id', $podcast->id)->exists();
            if ($exists) {
                continue;
            }

            DB::table('podcast_tracks')->insert([
                'podcast_id' => $podcast->id,
                'title' => $podcast->title,
                'description' => null,
                'audio_path' => $podcast->audio_path,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('podcast_tracks');
    }
};
