<div class="col-lg-4 col-md-6">
    @php
        $locale = app()->getLocale();
        $sectionSlug = optional($document->section)->slug;
        $documentShowUrl = $sectionSlug
            ? route('content.show', [$locale, $sectionSlug, $document->id])
            : route('content.show', [$locale, 'documents', $document->id]);
    @endphp
    <a href="{{ $documentShowUrl }}" 
       class="text-decoration-none">
        <div class="card border-0 shadow-sm h-100 document-card @if($matchType === 'exact') border-success @elseif($matchType === 'all') border-primary @elseif($matchType === 'any') border-info @endif">
            <div class="card-body p-3">
                <div class="row">
                    <!-- أيقونة الملف -->
                    
                    <!-- المحتوى -->
                    <div class="col-md-9 col-lg-10">
                        <!-- العلامات والحالة -->
                        <div class="mb-2">
                            <span class="badge bg-primary me-2">{{ app()->getLocale() === 'ar' ? (optional($document->section)->name ?? 'غير مصنّف') : (optional($document->section)->name_en ?? 'Uncategorized') }}</span>
                            @if($document->is_featured)
                                <span class="badge bg-warning text-dark me-2">مميز</span>
                            @endif
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-eye me-1"></i>
                                {{ number_format($document->views_count) }}
                            </span>
                            
                            <!-- نوع المطابقة -->
                            @if($matchType === 'exact')
                                {{-- <span class="badge bg-success me-2">
                                    <i class="fas fa-quote-left me-1"></i>
                                    مطابقة تامة
                                </span> --}}
                            @elseif($matchType === 'all')
                                {{-- <span class="badge bg-primary me-2">
                                    <i class="fas fa-check-double me-1"></i>
                                    جميع الكلمات
                                </span> --}}
                            @elseif($matchType === 'any')
                                {{-- <span class="badge bg-info me-2"> --}}
                                     {{-- الكلمات الموجودة في الوثيقة من البحث --}}
                                    {{-- <i class="fas fa-check me-1"></i> --}}
                                    @if(request('search'))
                                        @php
                                            $searchWords = explode(' ', trim(request('search')));
                                            $matchedWords = [];
                                            foreach($searchWords as $searchWord) {
                                                if(stripos($document->title, $searchWord) !== false || 
                                                   stripos($document->content, $searchWord) !== false || 
                                                   stripos($document->excerpt, $searchWord) !== false) {
                                                    $matchedWords[] = $searchWord;
                                                }
                                            }
                                        @endphp
                                        @if(count($matchedWords) > 0)
                                            {{-- <small class="d-block mt-1"> --}}
                                                {{-- ({{ implode(', ', $matchedWords) }}) --}}
                                            {{-- </small> --}}
                                        @endif
                                    @endif
                                {{-- </span> --}}
                            @endif
                        </div>
                        
                        <!-- العنوان -->
                        <h5 class="card-title mb-2">
                            {{ $document->title }}
                        </h5>
                        
                        <!-- المقتطف -->
                        @if($document->excerpt)
                            @php
                                $excerptPlain = strip_tags($document->excerpt);
                                $excerptShort = Str::limit($excerptPlain, 150);
                            @endphp
                            <p class="text-muted mb-3">
                                @if($matchType === 'exact' && !empty($rawSearch))
                                    @php
                                        $pattern = '/' . preg_quote($rawSearch, '/') . '/iu';
                                        $excerptMarked = preg_replace($pattern, '<mark>$0</mark>', $excerptShort);
                                    @endphp
                                    {!! $excerptMarked !!}
                                @elseif($matchType === 'all')
                                    @php
                                        $words = [];
                                        if(isset($tokens) && is_array($tokens)) { $words = $tokens; }
                                        else {
                                            $words = preg_split('/\s+/u', trim(request('search', '')), -1, PREG_SPLIT_NO_EMPTY);
                                        }
                                        $uniqueWords = [];
                                        foreach($words as $w){ $w = trim($w); if($w !== '' && !in_array($w, $uniqueWords, true)) $uniqueWords[] = $w; }
                                        // Arabic-aware pattern builder for excerpt highlighting (tolerates variants and diacritics/tatweel)
                                        $makePatternInline = function($word) {
                                            $chars = preg_split('//u', (string)$word, -1, PREG_SPLIT_NO_EMPTY);
                                            $map = [
                                                'ا' => '[اأإآٱ]',
                                                'أ' => '[اأإآٱ]',
                                                'إ' => '[اأإآٱ]',
                                                'آ' => '[اأإآٱ]',
                                                'ٱ' => '[اأإآٱ]',
                                                'ه' => '[هة]',
                                                'ة' => '[هة]',
                                                'ي' => '[يىئ]',
                                                'ى' => '[يىئ]',
                                                'ؤ' => '[وؤ]',
                                                'و' => '[وؤ]',
                                                'ئ' => '[يىئ]',
                                            ];
                                            $pat = '';
                                            foreach($chars as $ch){
                                                $pat .= ($map[$ch] ?? preg_quote($ch, '/')) . '[\x{064B}-\x{0652}\x{0640}]*';
                                            }
                                            return '/(' . $pat . ')/iu';
                                        };
                                        $excerptMarked = $excerptShort;
                                        foreach($uniqueWords as $w){
                                            $excerptMarked = preg_replace($makePatternInline($w), '<mark>$0</mark>', $excerptMarked);
                                        }
                                    @endphp
                                    {!! $excerptMarked !!}
                                @elseif($matchType === 'any' && !empty($word))
                                    @php
                                        // Use Arabic-aware regex for highlighting single word in excerpt
                                        $makePatternInline = function($word) {
                                            $chars = preg_split('//u', (string)$word, -1, PREG_SPLIT_NO_EMPTY);
                                            $map = [
                                                'ا' => '[اأإآٱ]',
                                                'أ' => '[اأإآٱ]',
                                                'إ' => '[اأإآٱ]',
                                                'آ' => '[اأإآٱ]',
                                                'ٱ' => '[اأإآٱ]',
                                                'ه' => '[هة]',
                                                'ة' => '[هة]',
                                                'ي' => '[يىئ]',
                                                'ى' => '[يىئ]',
                                                'ؤ' => '[وؤ]',
                                                'و' => '[وؤ]',
                                                'ئ' => '[يىئ]',
                                            ];
                                            $pat = '';
                                            foreach($chars as $ch){
                                                $pat .= ($map[$ch] ?? preg_quote($ch, '/')) . '[\x{064B}-\x{0652}\x{0640}]*';
                                            }
                                            return '/(' . $pat . ')/iu';
                                        };
                                        $excerptMarked = preg_replace($makePatternInline($word), '<mark>$0</mark>', $excerptShort);
                                    @endphp
                                    {!! $excerptMarked !!}
                                @else
                                    {{ $excerptShort }}
                                @endif
                            </p>
                        @endif
                        
                        <!-- معلومات إضافية -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="text-muted small">
                                @if($document->fieldValues->count() > 0)
                                    <div class="mb-3">
                                        @foreach($document->fieldValues->take(5) as $fieldValue)
                                            @if($fieldValue->field->type == 'select' || $fieldValue->field->type == 'date' || $fieldValue->field->type == 'text')
                                                <div class="small text-muted mb-1">
                                                    <strong>{{ $fieldValue->field->label }}:</strong>
                                                    @if($fieldValue->field->type == 'date')
                                                        {{ \Carbon\Carbon::parse($fieldValue->value)->format('Y-m-d') }}
                                                    @else
                                                        {{ $fieldValue->value }}
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>


                        </div>
                            @php
                                $sourceText = $document->content ?: ($document->excerpt ?: $document->title);
                                $sourceText = is_string($sourceText) ? $sourceText : '';
                                $clean = strip_tags($sourceText ?? '');
                                $clean = html_entity_decode($clean, ENT_QUOTES, 'UTF-8');
                            @endphp

                            <div class="mt-2 border-top pt-2">
                                <div class="small text-muted" style="min-height: 80px; max-height: 140px; overflow: hidden; direction: rtl;">
                                    @php
                                        // Arabic normalization and pattern utilities for robust highlighting
                                        $normalizeArabic = function($s) {
                                            $s = (string)$s;
                                            // remove tatweel
                                            $s = preg_replace('/\x{0640}/u', '', $s);
                                            // remove diacritics
                                            $s = preg_replace('/[\x{064B}-\x{0652}]/u', '', $s);
                                            // normalize common letter variants
                                            $s = str_replace(['أ','إ','آ','ٱ','ة','ى','ؤ','ئ'], ['ا','ا','ا','ا','ه','ي','و','ي'], $s);
                                            return $s;
                                        };
                                        $makePattern = function($word) {
                                            $chars = preg_split('//u', (string)$word, -1, PREG_SPLIT_NO_EMPTY);
                                            $map = [
                                                'ا' => '[اأإآٱ]',
                                                'أ' => '[اأإآٱ]',
                                                'إ' => '[اأإآٱ]',
                                                'آ' => '[اأإآٱ]',
                                                'ٱ' => '[اأإآٱ]',
                                                'ه' => '[هة]',
                                                'ة' => '[هة]',
                                                'ي' => '[يىئ]',
                                                'ى' => '[يىئ]',
                                                'ؤ' => '[وؤ]',
                                                'و' => '[وؤ]',
                                                'ئ' => '[يىئ]',
                                            ];
                                            $pat = '';
                                            foreach($chars as $ch){
                                                $pat .= $map[$ch] ?? preg_quote($ch, '/');
                                            }
                                            return '/(' . $pat . ')/iu';
                                        };
                                    @endphp
                                    @if($matchType === 'exact' && !empty($rawSearch))
                                        @php
                                            $needle = trim($rawSearch);
                                            $sn = null;
                                            if ($needle !== '') {
                                                $sources = [];
                                                $cTitle = html_entity_decode(strip_tags($document->title ?? ''), ENT_QUOTES, 'UTF-8');
                                                $cExcerpt = html_entity_decode(strip_tags($document->excerpt ?? ''), ENT_QUOTES, 'UTF-8');
                                                $cContent = html_entity_decode(strip_tags($document->content ?? ''), ENT_QUOTES, 'UTF-8');
                                                foreach ([$cContent, $cExcerpt, $cTitle] as $cand) {
                                                    if (is_string($cand) && trim($cand) !== '') { $sources[] = $cand; }
                                                }
                                                $source = '';
                                                $pos = false;
                                                foreach($sources as $cand) {
                                                    $p = mb_stripos($cand, $needle);
                                                    if ($p !== false) { $source = $cand; $pos = $p; break; }
                                                }
                                                if ($pos !== false) {
                                                    $len = mb_strlen($needle);
                                                    $beforeText = mb_substr($source, 0, $pos);
                                                    $afterText = mb_substr($source, $pos + $len);
                                                    $beforeTokens = preg_split('/[\s\x{00A0}]+/u', trim($beforeText), -1, PREG_SPLIT_NO_EMPTY);
                                                    $afterTokens = preg_split('/[\s\x{00A0}]+/u', trim($afterText), -1, PREG_SPLIT_NO_EMPTY);
                                                    $beforeStr = implode(' ', array_slice($beforeTokens, max(count($beforeTokens) - 8, 0)));
                                                    $matchStr = mb_substr($source, $pos, $len);
                                                    $afterStr = implode(' ', array_slice($afterTokens, 0, 8));
                                                    $sn = [$beforeStr, $matchStr, $afterStr];
                                                }
                                            }
                                        @endphp
                                        @if($sn)
                                            <div>
                                                كلمات البحث:
                                                {!! e($sn[0]) !!}<mark>{!! e($sn[1]) !!}</mark>{!! e($sn[2]) !!}
                                            </div>
                                        @else
                                            @php
                                                $fallbackSource = '';
                                                foreach([$cExcerpt, $cContent, $cTitle] as $cand){
                                                    if(is_string($cand) && trim($cand) !== ''){
                                                        if (mb_stripos($cand, $needle) !== false){ $fallbackSource = $cand; break; }
                                                    }
                                                }
                                                if ($fallbackSource === '') { $fallbackSource = $sources[0] ?? ''; }
                                                $fallback = Str::limit($fallbackSource, 180);
                                                $fallbackMarked = preg_replace('/(' . preg_quote($needle, '/') . ')/iu', '<mark>$1</mark>', $fallback);
                                            @endphp
                                            {!! $fallbackMarked !!}
                                        @endif
                                    @elseif($matchType === 'all')
                                        @php
                                            $words = [];
                                            if(isset($tokens) && is_array($tokens)) { $words = $tokens; }
                                            else {
                                                $words = preg_split('/\s+/u', trim(request('search', '')), -1, PREG_SPLIT_NO_EMPTY);
                                            }
                                            $uniqueWords = [];
                                            foreach($words as $w) {
                                                $w = trim($w);
                                                if($w !== '' && !in_array($w, $uniqueWords, true)) $uniqueWords[] = $w;
                                            }
                                        @endphp
                                        @foreach($uniqueWords as $w)
                                            @php
                                                $needle = $w;
                                                $sn = null;
                                                if ($needle !== '') {
                                                    $sources = [];
                                                    $cTitle = html_entity_decode(strip_tags($document->title ?? ''), ENT_QUOTES, 'UTF-8');
                                                    $cExcerpt = html_entity_decode(strip_tags($document->excerpt ?? ''), ENT_QUOTES, 'UTF-8');
                                                    $cContent = html_entity_decode(strip_tags($document->content ?? ''), ENT_QUOTES, 'UTF-8');
                                                    foreach ([$cContent, $cExcerpt, $cTitle] as $cand) {
                                                        if (is_string($cand) && trim($cand) !== '') { $sources[] = $cand; }
                                                    }
                                                    $source = '';
                                                    $pos = false;
                                                    foreach($sources as $cand) {
                                                        $p = mb_stripos($cand, $needle);
                                                        if ($p === false) {
                                                            // Try regex-based Arabic-aware match to get the actual matched substring
                                                            if (preg_match($makePattern($needle), $cand, $m)) {
                                                                $source = $cand;
                                                                $pos = mb_stripos($cand, $m[1] ?? $m[0]);
                                                                $matchedLen = mb_strlen($m[1] ?? $m[0]);
                                                                break;
                                                            }
                                                        } else { $source = $cand; $pos = $p; $matchedLen = mb_strlen($needle); break; }
                                                    }
                                                    if ($pos !== false) {
                                                        $len = $matchedLen ?? mb_strlen($needle);
                                                        $beforeText = mb_substr($source, 0, $pos);
                                                        $afterText = mb_substr($source, $pos + $len);
                                                        $beforeTokens = preg_split('/[\s\x{00A0}]+/u', trim($beforeText), -1, PREG_SPLIT_NO_EMPTY);
                                                        $afterTokens = preg_split('/[\s\x{00A0}]+/u', trim($afterText), -1, PREG_SPLIT_NO_EMPTY);
                                                        $beforeStr = implode(' ', array_slice($beforeTokens, max(count($beforeTokens) - 8, 0)));
                                                        $matchStr = mb_substr($source, $pos, $len);
                                                        $afterStr = implode(' ', array_slice($afterTokens, 0, 8));
                                                        $sn = [$beforeStr, $matchStr, $afterStr];
                                                    }
                                                }
                                            @endphp
                                            @if($sn)
                                                <div>
                                                    {{-- كلمة البحث --}}
                                                     {!! e($sn[0]) !!}<mark>{!! e($sn[1]) !!}</mark>{!! e($sn[2]) !!}
                                                </div>
                                            @else
                                                @php
                                                    $fallbackSource = '';
                                                    foreach([$cExcerpt, $cContent, $cTitle] as $cand){
                                                        if(is_string($cand) && trim($cand) !== ''){
                                                            if (preg_match($makePattern($needle), $cand)) { $fallbackSource = $cand; break; }
                                                        }
                                                    }
                                                    if ($fallbackSource === '') { $fallbackSource = $sources[0] ?? ''; }
                                                    $fallback = Illuminate\Support\Str::limit($fallbackSource, 180);
                                                    $fallbackMarked = preg_replace($makePattern($needle), '<mark>$0</mark>', $fallback);
                                                @endphp
                                                <div>
                                                    {{-- كلمة البحث "{{ $w }}": --}}
                                                     {!! $fallbackMarked !!}
                                                </div>
                                            @endif
                                        @endforeach
                                    @elseif($matchType === 'any' && !empty($word))
                                        @php
                                            $needle = $word;
                                            $sn = null;
                                            if ($needle !== '') {
                                                $sources = [];
                                                $cTitle = html_entity_decode(strip_tags($document->title ?? ''), ENT_QUOTES, 'UTF-8');
                                                $cExcerpt = html_entity_decode(strip_tags($document->excerpt ?? ''), ENT_QUOTES, 'UTF-8');
                                                $cContent = html_entity_decode(strip_tags($document->content ?? ''), ENT_QUOTES, 'UTF-8');
                                                foreach ([$cContent, $cExcerpt, $cTitle] as $cand) {
                                                    if (is_string($cand) && trim($cand) !== '') { $sources[] = $cand; }
                                                }
                                                $source = '';
                                                $pos = false;
                                                foreach($sources as $cand) {
                                                    $p = mb_stripos($cand, $needle);
                                                    if ($p === false) {
                                                        // Try regex-based Arabic-aware match to get the actual matched substring
                                                        if (preg_match($makePattern($needle), $cand, $m)) {
                                                            $source = $cand;
                                                            $pos = mb_stripos($cand, $m[1] ?? $m[0]);
                                                            $matchedLen = mb_strlen($m[1] ?? $m[0]);
                                                            break;
                                                        }
                                                    } else { $source = $cand; $pos = $p; $matchedLen = mb_strlen($needle); break; }
                                                }
                                                if ($pos !== false) {
                                                    $len = $matchedLen ?? mb_strlen($needle);
                                                    $beforeText = mb_substr($source, 0, $pos);
                                                    $afterText = mb_substr($source, $pos + $len);
                                                    $beforeTokens = preg_split('/[\s\x{00A0}]+/u', trim($beforeText), -1, PREG_SPLIT_NO_EMPTY);
                                                    $afterTokens = preg_split('/[\s\x{00A0}]+/u', trim($afterText), -1, PREG_SPLIT_NO_EMPTY);
                                                    $beforeStr = implode(' ', array_slice($beforeTokens, max(count($beforeTokens) - 8, 0)));
                                                    $matchStr = mb_substr($source, $pos, $len);
                                                    $afterStr = implode(' ', array_slice($afterTokens, 0, 8));
                                                    $sn = [$beforeStr, $matchStr, $afterStr];
                                                }
                                            }
                                        @endphp
                                        @if($sn)
                                            <div>
                                                {{-- كلمة البحث "{{ $word }}": --}}
                                                 {!! e($sn[0]) !!}<mark>{!! e($sn[1]) !!}</mark>{!! e($sn[2]) !!}
                                            </div>
                                        @else
                                            @php
                                                $fallbackSource = '';
                                                foreach([$cExcerpt, $cContent, $cTitle] as $cand){
                                                    if(is_string($cand) && trim($cand) !== ''){
                                                        if (preg_match($makePattern($needle), $cand)) { $fallbackSource = $cand; break; }
                                                    }
                                                }
                                                if ($fallbackSource === '') { $fallbackSource = $sources[0] ?? ''; }
                                                $fallback = Illuminate\Support\Str::limit($fallbackSource, 180);
                                                $fallbackMarked = preg_replace($makePattern($needle), '<mark>$0</mark>', $fallback);
                                            @endphp
                                            <div>
                                                {{-- كلمة البحث "{{ $word }}":  --}}
                                                {!! $fallbackMarked !!}
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            </div> <!-- closes .card -->
         </a>
     </div> <!-- closes .col-lg-4 .col-md-6 -->
