<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class DocumentPinnedKeyword extends Model
{
    protected $fillable = [
        'document_section_id',
        'keyword_id',
        'sort_order',
        'label_override',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'document_section_id' => 'integer',
    ];

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(DocumentSection::class, 'document_section_id');
    }

    /**
     * الكلمات المثبتة كاختصارات ظاهرة في صفحة قسم معيّن.
     * العدد المعروض = كل الوثائق المنشورة المرتبطة بالكلمة في كل الأقسام.
     */
    public static function orderedKeywordsForSection(int $sectionId, bool $withDocumentCounts = false): Collection
    {
        $pins = static::query()
            ->where('document_section_id', $sectionId)
            ->with(['keyword' => function ($q) use ($withDocumentCounts) {
                $q->where('scope', 'document');
                if ($withDocumentCounts) {
                    $q->withCount(['documents as docs_count' => function ($docs) {
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
                'document_section_id' => $pin->document_section_id,
            ];

            return $keyword;
        })->filter()->values();
    }

    /**
     * @deprecated استخدم orderedKeywordsForSection
     */
    public static function orderedKeywords(bool $withDocumentCounts = false): Collection
    {
        return collect();
    }

    /**
     * @param array<int, string>|string|null $names
     */
    public static function syncFromNames($names, int $sectionId): void
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
                [
                    'document_section_id' => $sectionId,
                    'keyword_id' => $keyword->id,
                ],
                [
                    'sort_order' => $index,
                    'label_override' => null,
                ]
            );

            $syncIds[] = $keyword->id;
        }

        $query = static::query()->where('document_section_id', $sectionId);

        if (empty($syncIds)) {
            $query->delete();
            return;
        }

        $query->whereNotIn('keyword_id', $syncIds)->delete();
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
