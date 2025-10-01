<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentSection;
use App\Models\DocumentCustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\Category;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        // منع عرض الوثائق في الواجهة الإنجليزية لعدم توفر ترجمة
        if (app()->getLocale() === 'en') {
            return view('frontend.no-translation');
        }
        // صفحة الهبوط للوثائق (بدون تعقيد الفلاتر الخاصة بصفحة القسم)
        // جلب الأقسام النشطة مع الإحصائيات الأساسية
        $sections = DocumentSection::active()
            ->withCount(['documents', 'customFields'])
            ->orderBy('sort_order')
            ->get();

        // الوثائق المميزة
        $featuredDocuments = Document::with(['section', 'user'])
            ->published()
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(6)
            ->get();

        // أحدث الوثائق
        $recentDocuments = Document::with(['section', 'user'])
            ->published()
            ->latest('published_at')
            ->take(9)
            ->get();

        // حسائيات سريعة للواجهة
        $totalDocuments = Document::published()->count();
        $totalSections = DocumentSection::active()->count();
        $totalViews = (int) Document::published()->sum('views_count');
        $totalFields = DocumentCustomField::active()->count();

        return view('frontend.documents.index', compact(
            'sections', 'featuredDocuments', 'recentDocuments',
            'totalDocuments', 'totalSections', 'totalViews', 'totalFields'
        ));
    }

    public function show($locale, $document)
    {
        \Log::info('Frontend.DocumentController@show invoked', ['locale' => $locale, 'param' => is_object($document) ? get_class($document) : $document]);
        // في حال لم يعمل الربط الضمني، نقوم بحلّ الـ slug يدوياً
        if ($document instanceof Document) {
            $doc = $document;
        } else {
            $doc = Document::where('slug', $document)
                ->orWhere('id', $document)
                ->firstOrFail();
        }

        if (!$doc->is_published) {
            \Log::warning('Document not published, aborting 404', ['slug' => $doc->slug, 'id' => $doc->id]);
            abort(404);
        }

        // منع عرض الوثائق في الواجهة الإنجليزية لعدم توفر ترجمة
        if (app()->getLocale() === 'en') {
            return view('frontend.no-translation');
        }

        // زيادة عدد المشاهدات
        $doc->increment('views_count');

        // الوثائق السابقة والتالية داخل نفس القسم (حسب تاريخ النشر، ثم المعرّف لكسر التعادل)
        $previousDocument = Document::published()
            ->where('section_id', $doc->section_id)
            ->where(function($q) use ($doc) {
                $q->where('published_at', '<', $doc->published_at)
                  ->orWhere(function($qq) use ($doc) {
                      $qq->where('published_at', $doc->published_at)
                         ->where('id', '<', $doc->id);
                  });
            })
            ->orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $nextDocument = Document::published()
            ->where('section_id', $doc->section_id)
            ->where(function($q) use ($doc) {
                $q->where('published_at', '>', $doc->published_at)
                  ->orWhere(function($qq) use ($doc) {
                      $qq->where('published_at', $doc->published_at)
                         ->where('id', '>', $doc->id);
                  });
            })
            ->orderBy('published_at', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        // الوثائق ذات الصلة
        $relatedDocuments = Document::published()
            ->where('section_id', $doc->section_id)
            ->where('id', '!=', $doc->id)
            ->latest('published_at')
            ->take(6)
            ->get();

        // إحصائيات القسم المستخدم في صفحة عرض الوثيقة
        $sectionStats = [
            'total_documents' => Document::published()->where('section_id', $doc->section_id)->count(),
            'total_views' => (int) Document::published()->where('section_id', $doc->section_id)->sum('views_count'),
        ];

        return view('frontend.documents.show', [
            'document' => $doc,
            'relatedDocuments' => $relatedDocuments,
            'previousDocument' => $previousDocument,
            'nextDocument' => $nextDocument,
            'sectionStats' => $sectionStats,
        ]);
    }

    public function search(Request $request)
    {
        // منع عرض نتائج الوثائق في الواجهة الإنجليزية لعدم توفر ترجمة
        if (app()->getLocale() === 'en') {
            return view('frontend.no-translation');
        }
        // Accept both 'search' and legacy 'q'
        $rawSearch = $request->get('search', $request->get('q', ''));
        $searchTerm = is_string($rawSearch) ? trim($rawSearch) : '';

        // Sections list for filters and suggestions
        $sections = DocumentSection::active()->withCount('documents')->orderBy('sort_order')->get();
        $popularSearches = collect();
        $customFieldFilters = false; // placeholder for view condition
        $activeFilters = [];

        // If no query and no filters, show landing with suggestions
        if ($searchTerm === '' && !$request->filled('section') && !$request->filled('date_from') && !$request->filled('date_to')) {
            // Simple popular searches suggestion based on most viewed titles
            $popularSearches = Document::published()
                ->orderBy('views_count', 'desc')
                ->limit(10)
                ->pluck('title');

            $documents = collect(); // empty
            $totalResults = 0;
            $categorizedResults = null;

            return view('frontend.documents.search', compact(
                'documents', 'searchTerm', 'totalResults', 'sections', 'popularSearches', 'customFieldFilters', 'activeFilters', 'categorizedResults'
            ));
        }

        // Normalize and tokenize Arabic query
        $normalizedPhrase = $this->normalizeArabic($searchTerm);
        $tokens = $this->tokenizeArabic($normalizedPhrase);
        $filteredTokens = array_values(array_filter($tokens, function($t) {
            return mb_strlen($t) >= 2 && !in_array($t, $this->arabicStopWords(), true);
        }));

        $perPage = (int) $request->get('per_page', 12);
        $sort = $request->get('sort', 'relevance');

        // Base query with common filters
        $baseQuery = Document::with(['section', 'user'])
            ->published();

        // Apply section filter (expects ID from form)
        if ($request->filled('section')) {
            $baseQuery->where('section_id', $request->input('section'));
            $activeFilters[] = ['label' => __('القسم'), 'value' => (string)$request->input('section'), 'remove_url' => request()->fullUrlWithQuery(['section' => null])];
        }
        // Apply date range on published_at
        if ($request->filled('date_from')) {
            $baseQuery->whereDate('published_at', '>=', $request->input('date_from'));
            $activeFilters[] = ['label' => __('من'), 'value' => (string)$request->input('date_from'), 'remove_url' => request()->fullUrlWithQuery(['date_from' => null])];
        }
        if ($request->filled('date_to')) {
            $baseQuery->whereDate('published_at', '<=', $request->input('date_to'));
            $activeFilters[] = ['label' => __('إلى'), 'value' => (string)$request->input('date_to'), 'remove_url' => request()->fullUrlWithQuery(['date_to' => null])];
        }

        // Helper for applying sort
        $applySort = function($query) use ($sort) {
            switch ($sort) {
                case 'newest':
                    return $query->latest('published_at');
                case 'oldest':
                    return $query->oldest('published_at');
                case 'title':
                    return $query->orderBy('title');
                case 'views':
                    return $query->orderBy('views_count', 'desc');
                default:
                    return $query->latest('published_at');
            }
        };

        // في حال وجود عبارة بحث، ابنِ ثلاة نتائج: مطابقة العبارة، جميع الكلمات، وكل كلمة على حدة
        if ($searchTerm !== '') {
            $exprTitle = $this->normalizeSql('documents.title');
            $exprContent = $this->normalizeSql('documents.content');
            $exprExcerpt = $this->normalizeSql('documents.excerpt');
            $exprFieldHas = $this->normalizeSql('value');

            // مطابقة العبارة كاملة
            $likePhrase = "%$normalizedPhrase%";
            $phraseQuery = (clone $baseQuery)
                ->where(function($q) use ($likePhrase, $exprTitle, $exprContent, $exprExcerpt, $exprFieldHas) {
                    $q->whereRaw("$exprTitle LIKE ?", [$likePhrase])
                      ->orWhereRaw("$exprContent LIKE ?", [$likePhrase])
                      ->orWhereRaw("$exprExcerpt LIKE ?", [$likePhrase])
                      ->orWhereExists(function($sub) use ($likePhrase, $exprFieldHas) {
                          $sub->select(DB::raw(1))
                              ->from('document_field_values as dfv')
                              ->whereColumn('dfv.document_id', 'documents.id')
                              ->whereRaw("$exprFieldHas LIKE ?", [$likePhrase]);
                      });
                });
            $phrasePaginated = $applySort($phraseQuery)
                ->paginate($perPage, ['*'], 'page_p')
                ->appends($request->except('page_p'));
            $phraseIds = $phrasePaginated->pluck('id')->all();

            // جميع الكلمات
            $allQuery = (clone $baseQuery)
                ->where(function($q) use ($filteredTokens, $exprTitle, $exprContent, $exprExcerpt, $exprFieldHas) {
                    foreach ($filteredTokens as $token) {
                        $like = "%$token%";
                        $q->where(function($qq) use ($like, $exprTitle, $exprContent, $exprExcerpt, $exprFieldHas) {
                            $qq->whereRaw("$exprTitle LIKE ?", [$like])
                               ->orWhereRaw("$exprContent LIKE ?", [$like])
                               ->orWhereRaw("$exprExcerpt LIKE ?", [$like])
                               ->orWhereExists(function($sub) use ($like, $exprFieldHas) {
                                   $sub->select(DB::raw(1))
                                       ->from('document_field_values as dfv')
                                       ->whereColumn('dfv.document_id', 'documents.id')
                                       ->whereRaw("$exprFieldHas LIKE ?", [$like]);
                               });
                        });
                    }
                });
            if (!empty($phraseIds)) {
                $allQuery->whereNotIn('documents.id', $phraseIds);
            }
            $allWordsPaginated = $applySort($allQuery)
                ->paginate($perPage, ['*'], 'page_a')
                ->appends($request->except('page_a'));
            $allIds = $allWordsPaginated->pluck('id')->all();

            // كل كلمة على حدة
            $perWord = [];
            foreach ($filteredTokens as $idx => $word) {
                $like = "%$word%";
                $wordQuery = (clone $baseQuery)
                    ->where(function($q) use ($like, $exprTitle, $exprContent, $exprExcerpt, $exprFieldHas) {
                        $q->whereRaw("$exprTitle LIKE ?", [$like])
                          ->orWhereRaw("$exprContent LIKE ?", [$like])
                          ->orWhereRaw("$exprExcerpt LIKE ?", [$like])
                          ->orWhereExists(function($sub) use ($like, $exprFieldHas) {
                              $sub->select(DB::raw(1))
                                  ->from('document_field_values as dfv')
                                  ->whereColumn('dfv.document_id', 'documents.id')
                                  ->whereRaw("$exprFieldHas LIKE ?", [$like]);
                          });
                    });
                // استبعاد باقي الكلمات لمنع تكرار النتائج
                foreach ($filteredTokens as $other) {
                    if ($other === $word) continue;
                    $notLike = "%$other%";
                    $wordQuery->where(function($q) use ($notLike, $exprTitle, $exprContent, $exprExcerpt, $exprFieldHas) {
                        $q->whereRaw("$exprTitle NOT LIKE ?", [$notLike])
                          ->whereRaw("$exprContent NOT LIKE ?", [$notLike])
                          ->whereRaw("$exprExcerpt NOT LIKE ?", [$notLike])
                          ->whereNotExists(function($sub) use ($notLike, $exprFieldHas) {
                              $sub->select(DB::raw(1))
                                  ->from('document_field_values as dfv')
                                  ->whereColumn('dfv.document_id', 'documents.id')
                                  ->whereRaw("$exprFieldHas LIKE ?", [$notLike]);
                          });
                    });
                }
                if (!empty($phraseIds)) {
                    $wordQuery->whereNotIn('documents.id', $phraseIds);
                }
                if (!empty($allIds)) {
                    $wordQuery->whereNotIn('documents.id', $allIds);
                }

                $perWord[$word] = $applySort($wordQuery)
                    ->paginate($perPage, ['*'], 'page_w' . $idx)
                    ->appends($request->except('page_w' . $idx));
            }

            $categorizedResults = [
                'phrase' => $phrasePaginated,
                'all' => $allWordsPaginated,
                'per_word' => $perWord,
                'tokens' => $filteredTokens,
                'raw' => $searchTerm,
            ];

            // لا حاجة لقائمة documents مسطحة هنا
            $documents = collect();
            $totalResults = ($phrasePaginated->total() + $allWordsPaginated->total()); // إحصاء تقريبي بدون احتساب per_word

            return view('frontend.documents.search', compact(
                'documents', 'searchTerm', 'totalResults', 'sections', 'popularSearches', 'customFieldFilters', 'activeFilters', 'categorizedResults'
            ));
        }

        // إن لم توجد عبارة بحث (مثلاً عبر فلاتر فقط) فنعرض الصفحة مع الأقسام والاقتراحات
        $documents = collect();
        $totalResults = 0;
        $categorizedResults = null;
        return view('frontend.documents.search', compact(
            'documents', 'searchTerm', 'totalResults', 'sections', 'popularSearches', 'customFieldFilters', 'activeFilters', 'categorizedResults'
        ));
    }

    /**
     * Arabic normalization helpers used by search()
     */
    protected function normalizeArabic(string $text): string
    {
        $text = trim(mb_strtolower($text, 'UTF-8'));
        // Remove tashkeel and tatweel
        $tashkeel = ["\u{064B}", "\u{064C}", "\u{064D}", "\u{064E}", "\u{064F}", "\u{0650}", "\u{0651}", "\u{0652}", "\u{0640}"]; // tanween, fatha, damma, kasra, shadda, sukun, tatweel
        $text = str_replace($tashkeel, '', $text);
        // Normalize hamza forms and alef/ya/ta marbuta variants
        $map = [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا',
            'ؤ' => 'و', 'ئ' => 'ي',
            'ى' => 'ي',
            'ة' => 'ه',
        ];
        $text = strtr($text, $map);
        // Collapse whitespace
        $text = preg_replace('/\s+/u', ' ', $text);
        return $text ?? '';
    }

    protected function normalizeSql(string $column): string
    {
        // Build SQL expression to normalize Arabic for LIKE comparisons
        $expr = "LOWER($column)";
        // Remove tashkeel and tatweel
        foreach (["\u{064B}", "\u{064C}", "\u{064D}", "\u{064E}", "\u{064F}", "\u{0650}", "\u{0651}", "\u{0652}", "\u{0640}"] as $mark) {
            $expr = "REPLACE($expr, '" . json_decode('"' . $mark . '"') . "', '')";
        }
        // Normalize characters
        foreach ([['أ','ا'],['إ','ا'],['آ','ا'],['ؤ','و'],['ئ','ي'],['ى','ي'],['ة','ه']] as $pair) {
            [$from, $to] = $pair;
            $expr = "REPLACE($expr, '$from', '$to')";
        }
        return $expr;
    }

    protected function tokenizeArabic(string $normalized): array
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);
        return array_values($parts ?: []);
    }

    protected function arabicStopWords(): array
    {
        return [
            'او','أو','او','على','الى','إلى','في','رقم','طعن','قانون','من','عن','ما','ماذا','هل','ثم','كما','بل','لكن','لم','لن','لا','أن','إن','اذا','إذا','قد','و','يا','ذلك','هذه','هذا','هناك','هنا','مع','كل','بعد','قبل','حتى','بين','أي','أى','اي','أين','أين','هي','هو','هم','هن','أنا','نحن','انت','أنت','انتم','أنتم','كان','كانت','يكون','تكون','يكونون'
        ];
    }

    /**
     * معالجة الحقول المخصصة من نوع النص
     */
    protected function processTextField($field, $value, $query)
    {
        if (!empty($value)) {
            $query->whereHas('fieldValues', function($q) use ($field, $value) {
                $q->where('field_id', $field->id)
                  ->where('value', 'like', "%{$value}%");
            });
        }
        return $query;
    }

    /**
     * معالجة الحقول المخصصة من نوع الرقم
     */
    protected function processNumberField($field, $request, $query)
    {
        $minValue = $request->input("fields.{$field->id}.min");
        $maxValue = $request->input("fields.{$field->id}.max");
        
        if (!empty($minValue) || !empty($maxValue)) {
            $query->whereHas('fieldValues', function($q) use ($field, $minValue, $maxValue) {
                $q->where('field_id', $field->id);
                
                if (!empty($minValue)) {
                    $q->where('value', '>=', $minValue);
                }
                if (!empty($maxValue)) {
                    $q->where('value', '<=', $maxValue);
                }
            });
        }
        return $query;
    }

    /**
     * معالجة الحقول المخصصة من نوع التاريخ
     */
    protected function processDateField($field, $request, $query)
    {
        $dayValue = $request->input("fields.{$field->id}.day");
        $monthValue = $request->input("fields.{$field->id}.month");
        $yearValue = $request->input("fields.{$field->id}.year");
        
        if (!empty($dayValue) || !empty($monthValue) || !empty($yearValue)) {
            $query->whereHas('fieldValues', function($q) use ($field, $dayValue, $monthValue, $yearValue) {
                $q->where('field_id', $field->id);
                
                if (!empty($dayValue)) {
                    $q->whereRaw('DAY(value) = ?', [intval($dayValue)]);
                }
                if (!empty($monthValue)) {
                    $q->whereRaw('MONTH(value) = ?', [intval($monthValue)]);
                }
                if (!empty($yearValue)) {
                    $q->whereRaw('YEAR(value) = ?', [$yearValue]);
                }
            });
        }
        return $query;
    }

    /**
     * معالجة الحقول المخصصة من نوع القائمة المنسدلة
     */
    protected function processSelectField($field, $value, $query)
    {
        if (!empty($value)) {
            $query->whereHas('fieldValues', function($q) use ($field, $value) {
                $q->where('field_id', $field->id)
                  ->where('value', $value);
            });
        }
        return $query;
    }

    /**
     * معالجة الحقول المخصصة من نوع الاختيارات المتعددة
     */
    protected function processMultiSelectField($field, $values, $query)
    {
        if (!empty($values) && is_array($values)) {
            $query->whereHas('fieldValues', function($q) use ($field, $values) {
                $q->where('field_id', $field->id);
                foreach ($values as $value) {
                    $q->where('value', 'like', "%{$value}%");
                }
            });
        }
        return $query;
    }

    /**
     * معالجة الحقول المخصصة من نوع مربع الاختيار
     */
    protected function processCheckboxField($field, $value, $query)
    {
        if ($value !== null) {
            $query->whereHas('fieldValues', function($q) use ($field, $value) {
                $q->where('field_id', $field->id)
                  ->where('value', $value ? '1' : '0');
            });
        }
        return $query;
    }

    /**
     * معالجة الحقول المخصصة من نوع الملف
     */
    protected function processFileField($field, $value, $query)
    {
        if (!empty($value)) {
            $query->whereHas('fieldValues', function($q) use ($field, $value) {
                $q->where('field_id', $field->id)
                  ->where('value', 'like', "%{$value}%");
            });
        }
        return $query;
    }

    /**
     * معالجة الحقول المخصصة من نوع البريد الإلكتروني
     */
    protected function processEmailField($field, $value, $query)
    {
        if (!empty($value)) {
            $query->whereHas('fieldValues', function($q) use ($field, $value) {
                $q->where('field_id', $field->id)
                  ->where('value', 'like', "%{$value}%");
            });
        }
        return $query;
    }

    /**
     * معالجة الحقول المخصصة من نوع الرابط
     */
    protected function processUrlField($field, $value, $query)
    {
        if (!empty($value)) {
            $query->whereHas('fieldValues', function($q) use ($field, $value) {
                $q->where('field_id', $field->id)
                  ->where('value', 'like', "%{$value}%");
            });
        }
        return $query;
    }

    /**
     * الحصول على خيارات الحقل المخصص
     */
    protected function getFieldOptions($field, $section)
    {
        if (in_array($field->type, ['select', 'multiselect', 'radio'])) {
            return $section->documents()
                ->published()
                ->join('document_field_values', 'documents.id', '=', 'document_field_values.document_id')
                ->where('document_field_values.field_id', $field->id)
                ->distinct()
                ->pluck('document_field_values.value')
                ->filter()
                ->sort()
                ->values();
        }
        return collect();
    }

    /**
     * تطبيق فلاتر الحقول المخصصة
     */
    protected function applyCustomFieldFilters($query, $customFields, $request)
    {
        $appliedFilters = [];
        
        foreach ($customFields as $field) {
            switch ($field->type) {
                case 'text':
                case 'textarea':
                    $value = $request->input("fields.{$field->id}");
                    if (!empty($value)) {
                        $query = $this->processTextField($field, $value, $query);
                        $appliedFilters[$field->name] = $value;
                    }
                    break;
                    
                case 'number':
                    $query = $this->processNumberField($field, $request, $query);
                    $minValue = $request->input("fields.{$field->id}.min");
                    $maxValue = $request->input("fields.{$field->id}.max");
                    if (!empty($minValue) || !empty($maxValue)) {
                        $appliedFilters[$field->name] = ['min' => $minValue, 'max' => $maxValue];
                    }
                    break;
                    
                case 'date':
                case 'datetime':
                    $query = $this->processDateField($field, $request, $query);
                    $dayValue = $request->input("fields.{$field->id}.day");
                    $monthValue = $request->input("fields.{$field->id}.month");
                    $yearValue = $request->input("fields.{$field->id}.year");
                    if (!empty($dayValue) || !empty($monthValue) || !empty($yearValue)) {
                        $appliedFilters[$field->name] = ['day' => $dayValue, 'month' => $monthValue, 'year' => $yearValue];
                    }
                    break;
                    
                case 'select':
                case 'radio':
                    $value = $request->input("fields.{$field->id}");
                    if (!empty($value)) {
                        $query = $this->processSelectField($field, $value, $query);
                        $appliedFilters[$field->name] = $value;
                    }
                    break;
                
                case 'multiselect':
                    $values = $request->input("fields.{$field->id}");
                    if (!empty($values)) {
                        $query = $this->processMultiSelectField($field, $values, $query);
                        $appliedFilters[$field->name] = $values;
                    }
                    break;
                
                case 'checkbox':
                    $value = $request->input("fields.{$field->id}");
                    if ($value !== null) {
                        $query = $this->processCheckboxField($field, $value, $query);
                        $appliedFilters[$field->name] = $value;
                    }
                    break;
                
                case 'email':
                    $value = $request->input("fields.{$field->id}");
                    if (!empty($value)) {
                        $query = $this->processEmailField($field, $value, $query);
                        $appliedFilters[$field->name] = $value;
                    }
                    break;
                
                case 'url':
                    $value = $request->input("fields.{$field->id}");
                    if (!empty($value)) {
                        $query = $this->processUrlField($field, $value, $query);
                        $appliedFilters[$field->name] = $value;
                    }
                    break;
                
                case 'file':
                    $value = $request->input("fields.{$field->id}");
                    if (!empty($value)) {
                        $query = $this->processFileField($field, $value, $query);
                        $appliedFilters[$field->name] = $value;
                    }
                    break;
            }
        }
        return [$query, $appliedFilters];
    }

    /**
     * تطبيق الفلاتر باستثناء حقل معين (لاستخدامه عند حساب الأعداد)
     */
    protected function applyFiltersExcludingField($baseQuery, $customFields, Request $request, $excludeFieldId)
    {
        $query = clone $baseQuery;

        // البحث البسيط
        if ($request->filled('search')) {
            $search = trim($request->search);
            if (strlen($search) >= 1) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%")
                      ->orWhereHas('fieldValues', function($fieldQ) use ($search) {
                          $fieldQ->where('value', 'like', "%{$search}%");
                      });
                });
            }
        }

        foreach ($customFields as $field) {
            if ($field->id === $excludeFieldId) continue;
            switch ($field->type) {
                case 'text':
                case 'textarea':
                    $value = $request->input("fields.{$field->id}");
                    if (!empty($value)) {
                        $query = $this->processTextField($field, $value, $query);
                    }
                    break;
                case 'number':
                    $query = $this->processNumberField($field, $request, $query);
                    break;
                case 'date':
                case 'datetime':
                    $query = $this->processDateField($field, $request, $query);
                    break;
                case 'select':
                case 'radio':
                    $value = $request->input("fields.{$field->id}");
                    if (!empty($value)) {
                        $query = $this->processSelectField($field, $value, $query);
                    }
                    break;
                case 'multiselect':
                    $values = $request->input("fields.{$field->id}");
                    if (!empty($values)) {
                        $query = $this->processMultiSelectField($field, $values, $query);
                    }
                    break;
                case 'checkbox':
                    $value = $request->input("fields.{$field->id}");
                    if ($value !== null) {
                        $query = $this->processCheckboxField($field, $value, $query);
                    }
                    break;
                case 'email':
                    $value = $request->input("fields.{$field->id}");
                    if (!empty($value)) {
                        $query = $this->processEmailField($field, $value, $query);
                    }
                    break;
                case 'url':
                    $value = $request->input("fields.{$field->id}");
                    if (!empty($value)) {
                        $query = $this->processUrlField($field, $value, $query);
                    }
                    break;
                case 'file':
                    $value = $request->input("fields.{$field->id}");
                    if (!empty($value)) {
                        $query = $this->processFileField($field, $value, $query);
                    }
                    break;
            }
        }
        return $query;
    }

    /**
     * حساب الأعداد لكل الخيارات في القوائم المنسدلة ومكونات التاريخ
     */
    protected function computeFieldCounts(DocumentSection $section, $customFields, Request $request)
    {
        $counts = [];
        $baseQuery = $section->documents()->published();

        foreach ($customFields as $field) {
            // طبّق جميع الفلاتر ما عدا هذا الحقل
            $filteredQuery = $this->applyFiltersExcludingField($baseQuery, $customFields, $request, $field->id);

            if (in_array($field->type, ['select', 'radio'])) {
                $rows = (clone $filteredQuery)
                    ->join('document_field_values as dfv', 'documents.id', '=', 'dfv.document_id')
                    ->where('dfv.field_id', $field->id)
                    ->selectRaw('dfv.value as val, COUNT(DISTINCT documents.id) as cnt')
                    ->groupBy('dfv.value')
                    ->pluck('cnt', 'val')
                    ->toArray();
                $counts[$field->id] = $rows;
            } elseif (in_array($field->type, ['date', 'datetime'])) {
                $counts[$field->id] = [
                    'day' => [],
                    'month' => [],
                    'year' => [],
                ];

                // قيود اختيارية خاصة بنفس الحقل (لجعل اليوم يتأثر بالشهر والسنة المختارين مثلاً)
                $selectedDay = $request->input("fields.{$field->id}.day");
                $selectedMonth = $request->input("fields.{$field->id}.month");
                $selectedYear = $request->input("fields.{$field->id}.year");

                // السنة
                $yearQuery = (clone $filteredQuery)
                    ->join('document_field_values as dfy', 'documents.id', '=', 'dfy.document_id')
                    ->where('dfy.field_id', $field->id);
                if (!empty($selectedMonth)) {
                    $yearQuery->whereRaw("LPAD(MONTH(dfy.value),2,'0') = ?", [$selectedMonth]);
                }
                if (!empty($selectedDay)) {
                    $yearQuery->whereRaw("LPAD(DAY(dfy.value),2,'0') = ?", [$selectedDay]);
                }
                $yearRows = $yearQuery
                    ->selectRaw('YEAR(dfy.value) as y, COUNT(DISTINCT documents.id) as cnt')
                    ->whereNotNull('dfy.value')
                    ->groupBy(DB::raw('YEAR(dfy.value)'))
                    ->pluck('cnt', 'y')
                    ->toArray();
                $counts[$field->id]['year'] = $yearRows;

                // الشهر (إذا تم اختيار سنة، حدّد بها)
                $monthQuery = (clone $filteredQuery)
                    ->join('document_field_values as dfm', 'documents.id', '=', 'dfm.document_id')
                    ->where('dfm.field_id', $field->id);
                if (!empty($selectedYear)) {
                    $monthQuery->whereRaw('YEAR(dfm.value) = ?', [$selectedYear]);
                }
                if (!empty($selectedDay)) {
                    $monthQuery->whereRaw("LPAD(DAY(dfm.value),2,'0') = ?", [$selectedDay]);
                }
                $monthRows = $monthQuery
                    ->selectRaw('LPAD(MONTH(dfm.value),2,\'0\') as m, COUNT(DISTINCT documents.id) as cnt')
                    ->whereNotNull('dfm.value')
                    ->groupBy(DB::raw('LPAD(MONTH(dfm.value),2,\'0\')'))
                    ->pluck('cnt', 'm')
                    ->toArray();
                $counts[$field->id]['month'] = $monthRows;

                // اليوم (إذا تم اختيار شهر/سنة، حدّد بهما)
                $dayQuery = (clone $filteredQuery)
                    ->join('document_field_values as dfd', 'documents.id', '=', 'dfd.document_id')
                    ->where('dfd.field_id', $field->id);
                if (!empty($selectedYear)) {
                    $dayQuery->whereRaw('YEAR(dfd.value) = ?', [$selectedYear]);
                }
                if (!empty($selectedMonth)) {
                    $dayQuery->whereRaw('LPAD(MONTH(dfd.value),2,\'0\') = ?', [$selectedMonth]);
                }
                $dayRows = $dayQuery
                    ->selectRaw('LPAD(DAY(dfd.value),2,\'0\') as d, COUNT(DISTINCT documents.id) as cnt')
                    ->whereNotNull('dfd.value')
                    ->groupBy(DB::raw('LPAD(DAY(dfd.value),2,\'0\')'))
                    ->pluck('cnt', 'd')
                    ->toArray();
                $counts[$field->id]['day'] = $dayRows;
            }
        }

        return $counts;
    }

    public function section($locale, Request $request, $section)
    {
        // منع عرض قسم الوثائق في الواجهة الإنجليزية لعدم توفر ترجمة
        if (app()->getLocale() === 'en') {
            return view('frontend.no-translation');
        }
        \Log::info('Frontend.DocumentController@section invoked', ['locale' => $locale, 'param' => is_object($section) ? get_class($section) : $section]);
        if (!$section instanceof DocumentSection) {
            $section = DocumentSection::where('slug', $section)->firstOrFail();
        }
        \Log::info('Resolved section', ['id' => $section->id, 'slug' => $section->slug]);

        // جميع الأقسام لاستخدامها في القائمة المنسدلة
        $allSections = DocumentSection::active()->orderBy('sort_order')
            ->withCount(['documents as published_docs_count' => function ($q) {
                $q->where('is_published', true);
            }])->get();

        // قراءة اختيار المستخدم من القائمة: قسم آخر أو كل الأقسام
        $selectedSectionParam = $request->get('section_select');
        $scopeAllSections = ($selectedSectionParam === 'all');

        // إذا اختار المستخدم قسماً آخر، أعد توجيهه إلى صفحة ذلك القسم مع الحفاظ على معلمات البحث
        if ($selectedSectionParam && $selectedSectionParam !== 'all' && (int)$selectedSectionParam !== (int)$section->id) {
            $targetSection = DocumentSection::find((int)$selectedSectionParam);
            if ($targetSection) {
                $qs = $request->except(['page', 'section_select']);
                $url = route('frontend.documents.section', [$locale, $targetSection->slug]);
                return redirect()->to($url . (count($qs) ? ('?' . http_build_query($qs)) : ''));
            }
        }

        // تهيئة الاستعلام بحسب النطاق (القسم الحالي أو كل الأقسام)
        if ($scopeAllSections) {
            $query = Document::with(['section', 'user'])->published();
        } else {
            $query = Document::with(['section', 'user'])
                ->published()
                ->where('section_id', $section->id);
        }

        // التقاط مصطلح البحث لاستخدامه في التصنيف الثلاثي
        $searchTerm = trim((string) $request->get('search', ''));

        // الحقول المخصصة
        $customFields = $section->customFields()->active()->orderBy('sort_order')->get();

        // تطبيق فلاتر الحقول المخصصة
        [$query, $appliedFilters] = $this->applyCustomFieldFilters($query, $customFields, $request);

        // إذا وُجد نص بحث: نفّذ التصنيف الثلاثي
        if ($searchTerm !== '') {
            $normalizedPhrase = $this->normalizeArabic($searchTerm);
            $tokens = $this->tokenizeArabic($normalizedPhrase);
            $filteredTokens = array_values(array_filter($tokens, function($t) {
                return mb_strlen($t) >= 2 && !in_array($t, $this->arabicStopWords(), true);
            }));

            $sort = $request->get('sort', 'latest');
            $applySort = function($q) use ($sort) {
                switch ($sort) {
                    case 'oldest':
                        return $q->oldest('published_at');
                    case 'title':
                        return $q->orderBy('title');
                    case 'views':
                        return $q->orderBy('views_count', 'desc');
                    default:
                        return $q->latest('published_at');
                }
            };

            $perPage = (int) $request->get('per_page', 12);

            // أعمدة مطبّق عليها التطبيع
            $exprTitle = $this->normalizeSql('documents.title');
            $exprContent = $this->normalizeSql('documents.content');
            $exprExcerpt = $this->normalizeSql('documents.excerpt');
            $exprFieldHas = $this->normalizeSql('value');

            $baseQuery = (clone $query);

            // توليد بدائل للكلمات للتعامل مع "ال" في بداية الكلمة
            $variantsFor = function(string $t): array {
                $vars = [$t];
                if (preg_match('/^ال/u', $t)) {
                    $vars[] = mb_substr($t, 2);
                }
                // إزالة الفراغات الفارغة وتوحيد العناصر
                $vars = array_values(array_unique(array_filter($vars, function($v){ return $v !== null && $v !== ''; })));
                return $vars;
            };

            // المطابقة التامة لعبارة البحث
            $phrasePattern = '%' . $normalizedPhrase . '%';
            $phraseQuery = (clone $baseQuery)->where(function($q) use ($phrasePattern, $exprTitle, $exprContent, $exprExcerpt, $exprFieldHas) {
                $q->whereRaw("$exprTitle LIKE ?", [$phrasePattern])
                  ->orWhereRaw("$exprContent LIKE ?", [$phrasePattern])
                  ->orWhereRaw("$exprExcerpt LIKE ?", [$phrasePattern])
                  ->orWhereExists(function($sub) use ($phrasePattern, $exprFieldHas) {
                      $sub->select(\DB::raw(1))
                          ->from('document_field_values as dfv')
                          ->whereColumn('dfv.document_id', 'documents.id')
                          ->whereRaw("$exprFieldHas LIKE ?", [$phrasePattern]);
                  });
            });

            $phraseIds = (clone $phraseQuery)
                ->select('documents.id')
                ->distinct()
                ->pluck('documents.id')
                ->all();

            $phrasePaginated = $applySort($phraseQuery)
                ->paginate($perPage, ['*'], 'page_phrase')
                ->appends($request->except('page_phrase'));

            // جميع الكلمات (AND)
            $allWordsQuery = (clone $baseQuery)->where(function($q) use ($filteredTokens, $exprTitle, $exprContent, $exprExcerpt, $exprFieldHas, $variantsFor) {
                foreach ($filteredTokens as $token) {
                    $variants = $variantsFor($token);
                    $q->where(function($qq) use ($variants, $exprTitle, $exprContent, $exprExcerpt, $exprFieldHas) {
                        foreach ($variants as $v) {
                            $like = "%$v%";
                            $qq->orWhere(function($qqq) use ($like, $exprTitle, $exprContent, $exprExcerpt, $exprFieldHas) {
                                $qqq->whereRaw("$exprTitle LIKE ?", [$like])
                                   ->orWhereRaw("$exprContent LIKE ?", [$like])
                                   ->orWhereRaw("$exprExcerpt LIKE ?", [$like])
                                   ->orWhereExists(function($sub) use ($like, $exprFieldHas) {
                                       $sub->select(\DB::raw(1))
                                           ->from('document_field_values as dfv')
                                           ->whereColumn('dfv.document_id', 'documents.id')
                                           ->whereRaw("$exprFieldHas LIKE ?", [$like]);
                                   });
                            });
                        }
                    });
                }
            });
            if (!empty($phraseIds)) {
                $allWordsQuery->whereNotIn('documents.id', $phraseIds);
            }
            $allIds = (clone $allWordsQuery)
                ->select('documents.id')
                ->distinct()
                ->pluck('documents.id')
                ->all();

            $allWordsPaginated = $applySort($allWordsQuery)
                ->paginate($perPage, ['*'], 'page_all')
                ->appends($request->except('page_all'));

            // نتائجة حسب كل كلمة بمفردها
            $perWord = [];
            foreach ($filteredTokens as $idx => $word) {
                $variants = $variantsFor($word);
                $wordQuery = (clone $baseQuery)
                    ->where(function($q) use ($variants, $exprTitle, $exprContent, $exprExcerpt, $exprFieldHas) {
                        foreach ($variants as $v) {
                            $like = "%$v%";
                            $q->orWhere(function($qq) use ($like, $exprTitle, $exprContent, $exprExcerpt, $exprFieldHas) {
                                $qq->whereRaw("$exprTitle LIKE ?", [$like])
                                   ->orWhereRaw("$exprContent LIKE ?", [$like])
                                   ->orWhereRaw("$exprExcerpt LIKE ?", [$like])
                                   ->orWhereExists(function($sub) use ($like, $exprFieldHas) {
                                       $sub->select(\DB::raw(1))
                                           ->from('document_field_values as dfv')
                                           ->whereColumn('dfv.document_id', 'documents.id')
                                           ->whereRaw("$exprFieldHas LIKE ?", [$like]);
                                   });
                            });
                        }
                    });
                // تجنب التكرار بين الأقسام
                if (!empty($phraseIds)) {
                    $wordQuery->whereNotIn('documents.id', $phraseIds);
                }
                if (!empty($allIds)) {
                    $wordQuery->whereNotIn('documents.id', $allIds);
                }

                $perWord["$word"] = $applySort($wordQuery)
                    ->paginate($perPage, ['*'], 'page_w' . $idx)
                    ->appends($request->except('page_w' . $idx));
            }

            // الحقول المخصصة مع خياراتها (تُحمّل فقط داخل قسم محدد)
            if (!$scopeAllSections) {
                foreach ($customFields as $field) {
                    if (in_array($field->type, ['select', 'multiselect', 'radio'])) {
                        $field->options = $this->getFieldOptions($field, $section);
                    }
                }
            }
            $fieldCounts = !$scopeAllSections ? $this->computeFieldCounts($section, $customFields, $request) : [];

            // إحصائيات عامة/خاصة بالقسم
            if ($scopeAllSections) {
                $totalDocuments = Document::published()->count();
                $totalViews = (int) Document::published()->sum('views_count');
            } else {
                $totalDocuments = $section->documents()->published()->count();
                $totalViews = $section->documents()->published()->sum('views_count');
            }

            $categorizedResults = [
                'phrase' => $phrasePaginated,
                'all' => $allWordsPaginated,
                'per_word' => $perWord,
                'tokens' => $filteredTokens,
                'raw' => $searchTerm,
            ];

            return view('frontend.documents.section', compact(
                'section', 'customFields', 'appliedFilters', 'totalDocuments', 'totalViews', 'fieldCounts', 'categorizedResults', 'searchTerm', 'allSections'
            ));
        }

        // الترتيب
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest('published_at');
                break;
            case 'title':
                $query->orderBy('title');
                break;
            case 'views':
                $query->orderBy('views_count', 'desc');
                break;
            default:
                $query->latest('published_at');
        }

        $perPage = (int) $request->get('per_page', 12);
        $documents = $query->paginate($perPage);

        // إحصائيات القسم/العامة
        if ($scopeAllSections) {
            $totalDocuments = Document::published()->count();
            $totalViews = (int) Document::published()->sum('views_count');
        } else {
            $totalDocuments = $section->documents()->published()->count();
            $totalViews = $section->documents()->published()->sum('views_count');
        }

        // الحقول المخصصة مع خياراتها
        foreach ($customFields as $field) {
            if (in_array($field->type, ['select', 'multiselect', 'radio'])) {
                $field->options = $this->getFieldOptions($field, $section);
            }
        }

        // حساب الأعداد لكل القوائم المنسدلة ولكل مكونات التاريخ (يوم/شهر/سنة)
        $fieldCounts = !$scopeAllSections ? $this->computeFieldCounts($section, $customFields, $request) : [];

        return view('frontend.documents.section', compact(
            'section', 'documents', 'customFields', 'appliedFilters',
            'totalDocuments', 'totalViews', 'fieldCounts', 'searchTerm', 'allSections'
        ));
    }

    public function getFilterCounts($locale, Request $request, $section)
    {
        if (!$section instanceof DocumentSection) {
            $section = DocumentSection::where('slug', $section)->firstOrFail();
        }
        $customFields = $section->customFields()->active()->orderBy('sort_order')->get();
        $counts = $this->computeFieldCounts($section, $customFields, $request);
        return response()->json(['counts' => $counts]);
    }

    public function unifiedShow($locale, $sectionSlug, $id)
    {
        // أولاً: تحقق من نوع القسم - هل هو فئة مقالات أم قسم وثائق؟
        $category = Category::where('name_en', $sectionSlug)
            ->orWhere('slug', $sectionSlug)
            ->first();
        
        $documentSection = DocumentSection::where('slug', $sectionSlug)
            ->orWhere('name_en', $sectionSlug)
            ->first();

        // إذا كان القسم فئة مقالات، ابحث في المقالات أولاً
        if ($category) {
            $post = Post::published()
                ->with('category')
                ->where('id', $id)
                ->where('category_id', $category->id)
                ->first();

            if ($post) {
                // إذا كانت اللغة الحالية إنجليزية والمقال بلا ترجمة إنجليزية
                if (app()->getLocale() === 'en' && !$post->hasEnglishVersion()) {
                    return view('frontend.no-translation');
                }

                // توجيه إلى المسار القانوني إذا كان السِّـلَج في الرابط لا يطابق الاسم الإنجليزي لفئة المقال
                $expectedCategorySlug = optional($post->category)->name_en ?: optional($post->category)->slug;
                if ($expectedCategorySlug && $expectedCategorySlug !== $sectionSlug) {
                    return redirect()->route('content.show', [$locale, $expectedCategorySlug, $post->id], 301);
                }

                // زيادة عدد المشاهدات
                $post->incrementViews();

                // تقارير أخرى بحسب اللغة الحالية
                $currentLocale = app()->getLocale();
                if ($currentLocale === 'ar') {
                    $otherReportsPosts = Post::published()
                        ->forOtherReportsAr()
                        ->where('id', '!=', $post->id)
                        ->orderBy('sort_order')
                        ->orderBy('published_at', 'desc')
                        ->limit(6)
                        ->get();
                } else {
                    $otherReportsPosts = Post::published()
                        ->forOtherReportsEn()
                        ->where('id', '!=', $post->id)
                        ->orderBy('sort_order')
                        ->orderBy('published_at', 'desc')
                        ->limit(6)
                        ->get();
                }
                
                // Get IDs of other reports posts to exclude from related posts
                // Include posts that show in other reports for ANY language to prevent duplication
                $otherReportsIds = $otherReportsPosts->pluck('id')->toArray();
                $allOtherReportsIds = Post::published()
                    ->where(function($query) {
                        $query->where('show_in_other_reports_ar', true)
                              ->orWhere('show_in_other_reports_en', true);
                    })
                    ->where('id', '!=', $post->id)
                    ->pluck('id')
                    ->toArray();
                $otherReportsIds = array_unique(array_merge($otherReportsIds, $allOtherReportsIds));

                // مقالات ذات صلة (استبعاد المقالات الموجودة في تقارير أخرى)
                $relatedPostsQuery = Post::where('category_id', $post->category_id)
                    ->where('id', '!=', $post->id)
                    ->whereNotIn('id', $otherReportsIds)
                    ->published();
                
                // Ensure English locale only shows posts with English translation
                if (app()->getLocale() === 'en') {
                    $relatedPostsQuery->whereNotNull('title_en')->where('title_en', '!=', '')
                                     ->whereNotNull('content_en')->where('content_en', '!=', '');
                }
                
                $relatedPosts = $relatedPostsQuery->orderBy('published_at', 'desc')
                    ->limit(6)
                    ->get();

                // جميع الفئات للملاحة
                $categories = Category::where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();

                return view('frontend.posts.show', compact('post', 'relatedPosts', 'otherReportsPosts', 'categories'));
            }
        }

        // إذا كان القسم قسم وثائق، ابحث في الوثائق
        if ($documentSection) {
            $doc = Document::with(['section', 'user', 'fieldValues.field'])
                ->published()
                ->where('id', $id)
                ->where('section_id', $documentSection->id)
                ->first();
        } else {
            $doc = null;
        }

        if ($doc) {
            // منع عرض الوثائق في الواجهة الإنجليزية لعدم توفر ترجمة
            if (app()->getLocale() === 'en') {
                return view('frontend.no-translation');
            }

            // توجيه إلى المسار القانوني إذا كان السِّـلَج الوارد غير مطابق، وذلك لكل من الوثائق والمقالات، مع الحفاظ على سلوك الزيادة في المشاهدات وجلب العناصر ذات الصلة.
            $expectedSectionSlug = optional($doc->section)->name_en ?: optional($doc->section)->slug;
            if ($expectedSectionSlug && $expectedSectionSlug !== $sectionSlug) {
                return redirect()->route('content.show', [$locale, $expectedSectionSlug, $doc->id], 301);
            }

            // زيادة عدد المشاهدات
            $doc->increment('views_count');

            // السابق والتالي داخل نفس القسم
            $previousDocument = Document::published()
                ->where('section_id', $doc->section_id)
                ->where(function($q) use ($doc) {
                    $q->where('published_at', '<', $doc->published_at)
                      ->orWhere(function($qq) use ($doc) {
                          $qq->where('published_at', $doc->published_at)
                             ->where('id', '<', $doc->id);
                      });
                })
                ->orderBy('published_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $nextDocument = Document::published()
                ->where('section_id', $doc->section_id)
                ->where(function($q) use ($doc) {
                    $q->where('published_at', '>', $doc->published_at)
                      ->orWhere(function($qq) use ($doc) {
                          $qq->where('published_at', $doc->published_at)
                             ->where('id', '>', $doc->id);
                      });
                })
                ->orderBy('published_at', 'asc')
                ->orderBy('id', 'asc')
                ->first();

            $relatedDocuments = Document::published()
                ->where('section_id', $doc->section_id)
                ->where('id', '!=', $doc->id)
                ->latest('published_at')
                ->take(6)
                ->get();

            $sectionStats = [
                'total_documents' => Document::published()->where('section_id', $doc->section_id)->count(),
                'total_views' => (int) Document::published()->where('section_id', $doc->section_id)->sum('views_count'),
            ];

            return view('frontend.documents.show', [
                'document' => $doc,
                'relatedDocuments' => $relatedDocuments,
                'previousDocument' => $previousDocument,
                'nextDocument' => $nextDocument,
                'sectionStats' => $sectionStats,
            ]);
        }

        // إذا لم يُعثر على وثيقة ولا مقال بهذا المعرّف
        abort(404);
    }
}