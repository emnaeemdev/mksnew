<?php

namespace App\Models\Concerns;

use App\Models\Keyword;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait HasKeywords
{
    public function keywords(): MorphToMany
    {
        return $this->morphToMany(Keyword::class, 'keywordable')->withTimestamps();
    }

    public function keywordScope(): string
    {
        return match (static::class) {
            \App\Models\Document::class => 'document',
            \App\Models\Post::class => 'post',
            \App\Models\Nashra::class => 'nashra',
            \App\Models\Podcast::class => 'podcast',
            default => 'document',
        };
    }

    /**
     * @param array<int, string>|string|null $names
     */
    public function syncKeywordNames($names): void
    {
        $scope = $this->keywordScope();
        $list = $this->normalizeKeywordInput($names);

        $ids = [];
        foreach ($list as $name) {
            $keyword = Keyword::findOrCreateForScope($scope, $name);
            if ($keyword) {
                $ids[] = $keyword->id;
            }
        }

        $previousIds = $this->keywords()->pluck('keywords.id')->all();
        $this->keywords()->sync(array_values(array_unique($ids)));

        foreach (array_unique(array_merge($previousIds, $ids)) as $keywordId) {
            Keyword::whereKey($keywordId)->update([
                'usage_count' => DB::table('keywordables')->where('keyword_id', $keywordId)->count(),
            ]);
        }
    }

    /**
     * @param array<int, string>|string|null $names
     * @return array<int, string>
     */
    protected function normalizeKeywordInput($names): array
    {
        if ($names === null || $names === '') {
            return [];
        }

        if (is_string($names)) {
            $decoded = json_decode($names, true);
            if (is_array($decoded)) {
                $names = $decoded;
            } else {
                $names = preg_split('/[,،\n]+/u', $names) ?: [];
            }
        }

        if (!is_array($names)) {
            return [];
        }

        $out = [];
        foreach ($names as $name) {
            if (!is_string($name)) {
                continue;
            }
            $name = Keyword::normalizeName($name);
            if ($name !== '') {
                $out[] = $name;
            }
        }

        return array_values(array_unique($out));
    }

    public function keywordNames(): Collection
    {
        return $this->keywords->pluck('name');
    }
}
