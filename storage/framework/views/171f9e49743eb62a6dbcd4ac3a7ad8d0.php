<?php
    $activeTab = $activeTab ?? ($categorizedResults['active_tab'] ?? request('tab', 'phrase'));
    $raw = $raw ?? ($categorizedResults['raw'] ?? '');
    $pageParamKeys = ['page', 'page_p', 'page_a', 'page_any', 'page_phrase', 'page_all', 'page_w0', 'page_w1', 'page_w2', 'page_w3'];
    $baseQuery = $baseQuery ?? request()->except($pageParamKeys);
    $perWordItems = $perWordItems ?? ($categorizedResults['per_word'] ?? []);
    $tokensCount = $tokensCount ?? count($perWordItems);
?>

<div class="tab-content" id="search-tab-content">
    <div id="tab-phrase" class="tab-pane fade row mt-2 <?php echo e($activeTab === 'phrase' ? 'show active' : ''); ?>" role="tabpanel" aria-labelledby="tab-link-phrase">
        <div class="col-12">
            <h5 class="mb-3"><i class="fas fa-quote-left ms-1"></i> وثائق مطابقة تمامًا لجملة البحث</h5>
            <?php if($activeTab === 'phrase' && ($categorizedResults['phrase'] ?? null) && $categorizedResults['phrase']->count()): ?>
                <div class="row g-3 mb-3 search-results-grid">
                    <?php $__currentLoopData = $categorizedResults['phrase']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'exact', 'rawSearch' => $raw], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="d-flex justify-content-center mb-4 search-tab-pagination">
                    <?php echo e($categorizedResults['phrase']->appends(array_merge($baseQuery, ['tab' => 'phrase']))->links()); ?>

                </div>
            <?php elseif($activeTab === 'phrase'): ?>
                <div class="alert alert-light">لا توجد نتائج مطابقة تمامًا لعبارة "<?php echo e($raw); ?>"</div>
            <?php endif; ?>
        </div>
    </div>

    <div id="tab-all" class="tab-pane fade row mt-2 <?php echo e($activeTab === 'all' ? 'show active' : ''); ?>" role="tabpanel" aria-labelledby="tab-link-all">
        <div class="col-12">
            <h5 class="mb-3"><i class="fas fa-check-double ms-1"></i> وثائق بها جميع كلمات البحث</h5>
            <?php if($activeTab === 'all' && ($categorizedResults['all'] ?? null) && $categorizedResults['all']->count()): ?>
                <div class="row g-3 mb-3 search-results-grid">
                    <?php $__currentLoopData = $categorizedResults['all']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'all', 'tokens' => ($categorizedResults['tokens'] ?? [])], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="d-flex justify-content-center mb-4 search-tab-pagination">
                    <?php echo e($categorizedResults['all']->appends(array_merge($baseQuery, ['tab' => 'all']))->links()); ?>

                </div>
            <?php elseif($activeTab === 'all'): ?>
                <div class="alert alert-light">لا توجد نتائج تحتوي كل كلمات البحث.</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!empty($perWordItems) && $tokensCount > 1): ?>
        <?php $__currentLoopData = $perWordItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $word = $item['word'] ?? '';
                $page = $item['page'] ?? null;
            ?>
            <div id="tab-word-<?php echo e($idx); ?>" class="tab-pane fade row mt-2 <?php echo e($activeTab === 'word-'.$idx ? 'show active' : ''); ?>" role="tabpanel" aria-labelledby="tab-link-word-<?php echo e($idx); ?>">
                <div class="col-12">
                    <h5 class="mb-3"><i class="fas fa-check ms-1"></i> نتائج بها كلمة "<?php echo e($word); ?>"</h5>
                    <?php if($activeTab === 'word-'.$idx && $page && $page->count()): ?>
                        <div class="row g-3 mb-3 search-results-grid">
                            <?php $__currentLoopData = $page; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo $__env->make('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'any', 'word' => $word], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="d-flex justify-content-center mb-4 search-tab-pagination">
                            <?php echo e($page->appends(array_merge($baseQuery, ['tab' => 'word-'.$idx]))->links()); ?>

                        </div>
                    <?php elseif($activeTab === 'word-'.$idx): ?>
                        <div class="alert alert-light">لا توجد نتائج تحتوي كلمة "<?php echo e($word); ?>".</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\mksnew\resources\views/frontend/documents/partials/categorized-search-tab-content.blade.php ENDPATH**/ ?>