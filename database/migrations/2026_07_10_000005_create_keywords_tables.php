<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('scope', 32); // document|post|nashra|podcast
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->unique(['scope', 'slug']);
            $table->index(['scope', 'name']);
            $table->index(['scope', 'usage_count']);
        });

        Schema::create('keywordables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained('keywords')->cascadeOnDelete();
            $table->morphs('keywordable');
            $table->timestamps();

            $table->unique(['keyword_id', 'keywordable_type', 'keywordable_id'], 'keywordables_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keywordables');
        Schema::dropIfExists('keywords');
    }
};
