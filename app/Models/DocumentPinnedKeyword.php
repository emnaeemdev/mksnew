<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class DocumentPinnedKeyword extends Model
{
    protected $fillable = [
        'keyword_id',
        'sort_order',
        'label_override',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }

    /**
     * الكلمات المثبتة كاختصارات للزائر (نطاق الوثائق فقط).
     */
    public static function orderedKeywords(bool $withDocumentCounts = false): Collection
    {
        $pins = static::query()
            ->with(['keyword' => function ($q) use ($withDocumentCounts) {
                $q->where('scope', 'document');
                if ($withDocumentCounts) {
                    $q->withCount(['documents as section_docs_count' => function ($docs) {
                        $docs->published();
                    }]);
                }
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $pins->map(function (self $pin) {
            $keyword = $pin->keyword;
            if (!$keyword) {
                return null;
            }

            $keyword->pivot = (object) [
                'sort_order' => $pin->sort_order,
                'label_override' => $pin->label_override,
            ];

            return $keyword;
        })->filter()->values();
    }

    /**
     * @param array<int, string>|string|null $names
     */
    public static function syncFromNames($names): void
    {
        $list = self::normalizeNames($names);
        $syncIds = [];

        foreach ($list as $index => $name) {
            $keyword = Keyword::query()
                ->where('scope', 'document')
                ->where(function ($q) use ($name) {
                    $q->where('name', $name)
                        ->orWhere('slug', Keyword::makeSlug($name));
                })
                ->first();

            if (!$keyword) {
                $keyword = Keyword::findOrCreateForScope('document', $name);
            }

            if (!$keyword) {
                continue;
            }

            static::updateOrCreate(
                ['keyword_id' => $keyword->id],
                [
                    'sort_order' => $index,
                    'label_override' => null,
                ]
            );

            $syncIds[] = $keyword->id;
        }

        if (empty($syncIds)) {
            static::query()->delete();
            return;
        }

        static::query()->whereNotIn('keyword_id', $syncIds)->delete();
    }

    /**
     * @param array<int, string>|string|null $names
     * @return array<int, string>
     */
    protected static function normalizeNames($names): array
    {
        if ($names === null || $names === '') {
            return [];
        }

        if (is_string($names)) {
            $decoded = json_decode($names, true);
            $names = is_array($decoded) ? $decoded : preg_split('/[,،\n]+/u', $names);
        }

        if (!is_array($names)) {
            return [];
        }

        $out = [];
        foreach ($names as $name) {
            $name = Keyword::normalizeName((string) $name);
            if ($name !== '') {
                $out[] = $name;
            }
        }

        return array_values(array_unique($out));
    }
}
