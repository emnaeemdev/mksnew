<?php
    $raw = $categorizedResults['raw'] ?? '';
    $activeTab = $categorizedResults['active_tab'] ?? request('tab');
    $perWordItems = $categorizedResults['per_word'] ?? [];
    if (!$activeTab) {
        $activeTab = (($categorizedResults['phrase'] ?? null) && $categorizedResults['phrase']->total() > 0) ? 'phrase'
            : ((($categorizedResults['all'] ?? null) && $categorizedResults['all']->total() > 0) ? 'all' : 'phrase');
        foreach ($perWordItems as $wi => $item) {
            $page = is_array($item) ? ($item['page'] ?? null) : $item;
            if ($activeTab === 'phrase' && !($categorizedResults['phrase'] ?? null)?->total() && !($categorizedResults['all'] ?? null)?->total() && $page && $page->total() > 0) {
                $activeTab = 'word-' . $wi;
            }
        }
    }
    $pageParamKeys = ['page', 'page_p', 'page_a', 'page_any', 'page_phrase', 'page_all', 'page_w0', 'page_w1', 'page_w2', 'page_w3'];
    $baseQuery = request()->except($pageParamKeys);
    $phraseTotal = ($categorizedResults['phrase'] ?? null) ? $categorizedResults['phrase']->total() : 0;
    $allTotal = ($categorizedResults['all'] ?? null) ? $categorizedResults['all']->total() : 0;
    $tokensCount = count($perWordItems);
?>

<div id="categorized-search-app" data-ajax-search="1">
    <div class="row mt-3" id="results-tabs">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button type="button"
                            id="tab-link-phrase"
                            class="nav-link <?php echo e($activeTab === 'phrase' ? 'active' : ''); ?>"
                            data-search-tab="phrase"
                            aria-controls="tab-phrase">
                        مطابقة تامة
                        <span class="badge rounded-pill bg-secondary ms-1"><?php echo e($phraseTotal); ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button"
                            id="tab-link-all"
                            class="nav-link <?php echo e($activeTab === 'all' ? 'active' : ''); ?>"
                            data-search-tab="all"
                            aria-controls="tab-all">
                        جميع الكلمات
                        <span class="badge rounded-pill bg-secondary ms-1"><?php echo e($allTotal); ?></span>
                    </button>
                </li>
                <?php if(!empty($perWordItems) && $tokensCount > 1): ?>
                    <?php $__currentLoopData = $perWordItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $word = $item['word'] ?? '';
                            $page = $item['page'] ?? null;
                        ?>
                        <li class="nav-item">
                            <button type="button"
                                    id="tab-link-word-<?php echo e($idx); ?>"
                                    class="nav-link <?php echo e($activeTab === 'word-'.$idx ? 'active' : ''); ?>"
                                    data-search-tab="word-<?php echo e($idx); ?>"
                                    aria-controls="tab-word-<?php echo e($idx); ?>">
                                نتائج بها كلمة <b style="color: rgb(124, 190, 86);">"<?php echo e($word); ?>"</b>
                                <span class="badge rounded-pill bg-secondary ms-1"><?php echo e($page ? $page->total() : 0); ?></span>
                            </button>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div id="search-tab-content-wrapper">
        <?php echo $__env->make('frontend.documents.partials.categorized-search-tab-content', [
            'categorizedResults' => $categorizedResults,
            'activeTab' => $activeTab,
            'raw' => $raw,
            'baseQuery' => $baseQuery,
            'tokensCount' => $tokensCount,
            'perWordItems' => $perWordItems,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('34e6bfbc-5c34-4b27-8901-cabbeae37ee7')): $__env->markAsRenderedOnce('34e6bfbc-5c34-4b27-8901-cabbeae37ee7'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script src="<?php echo e(asset('js/document-search-ajax.js')); ?>?v=2"></script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\mksnew\resources\views/frontend/documents/partials/categorized-search-results.blade.php ENDPATH**/ ?>