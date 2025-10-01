<div class="col-12">
    <?php
        $locale = app()->getLocale();
        $sectionSlug = optional($document->section)->slug;
        $documentShowUrl = $sectionSlug
            ? route('content.show', [$locale, $sectionSlug, $document->id])
            : route('content.show', [$locale, 'documents', $document->id]);
    ?>
    <a href="<?php echo e($documentShowUrl); ?>" 
       class="text-decoration-none">
        <div class="card border-0 shadow-sm h-100 document-card <?php if($matchType === 'exact'): ?> border-success <?php elseif($matchType === 'all'): ?> border-primary <?php elseif($matchType === 'any'): ?> border-info <?php endif; ?>">
            <div class="card-body p-4">
                <div class="row">
                    <!-- أيقونة الملف -->
                    
                    <!-- المحتوى -->
                    <div class="col-md-9 col-lg-10">
                        <!-- العلامات والحالة -->
                        <div class="mb-2">
                            <span class="badge bg-primary me-2"><?php echo e(app()->getLocale() === 'ar' ? (optional($document->section)->name ?? 'غير مصنّف') : (optional($document->section)->name_en ?? 'Uncategorized')); ?></span>
                            <?php if($document->is_featured): ?>
                                <span class="badge bg-warning text-dark me-2">مميز</span>
                            <?php endif; ?>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo e(number_format($document->views_count)); ?>

                            </span>
                            
                            <!-- نوع المطابقة -->
                            <?php if($matchType === 'exact'): ?>
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-quote-left me-1"></i>
                                    مطابقة تامة
                                </span>
                            <?php elseif($matchType === 'all'): ?>
                                <span class="badge bg-primary me-2">
                                    <i class="fas fa-check-double me-1"></i>
                                    جميع الكلمات
                                </span>
                            <?php elseif($matchType === 'any'): ?>
                                <span class="badge bg-info me-2">
                                     الكلمات الموجودة في الوثيقة من البحث
                                    <i class="fas fa-check me-1"></i>
                                    <?php if(request('search')): ?>
                                        <?php
                                            $searchWords = explode(' ', trim(request('search')));
                                            $matchedWords = [];
                                            foreach($searchWords as $word) {
                                                if(stripos($document->title, $word) !== false || 
                                                   stripos($document->content, $word) !== false || 
                                                   stripos($document->excerpt, $word) !== false) {
                                                    $matchedWords[] = $word;
                                                }
                                            }
                                        ?>
                                        <?php if(count($matchedWords) > 0): ?>
                                            <small class="d-block mt-1">
                                                (<?php echo e(implode(', ', $matchedWords)); ?>)
                                            </small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- العنوان -->
                        <h5 class="card-title mb-2">
                            <?php if(request('search')): ?>
                                <?php echo str_ireplace(request('search'), '<mark>' . request('search') . '</mark>', $document->title); ?>

                            <?php else: ?>
                                <?php echo e($document->title); ?>

                            <?php endif; ?>
                        </h5>
                        
                        <!-- المقتطف -->
                        <?php if($document->excerpt): ?>
                            <p class="text-muted mb-3">
                                <?php if(request('search')): ?>
                                    <?php echo str_ireplace(request('search'), '<mark>' . request('search') . '</mark>', Str::limit($document->excerpt, 150)); ?>

                                <?php else: ?>
                                    <?php echo e(Str::limit($document->excerpt, 150)); ?>

                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                        
                        <!-- معلومات إضافية -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="text-muted small">
                                <?php if($document->fieldValues->count() > 0): ?>
                                    <div class="mb-3">
                                        <?php $__currentLoopData = $document->fieldValues->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($fieldValue->field->type == 'select' || $fieldValue->field->type == 'date' || $fieldValue->field->type == 'text'): ?>
                                                <div class="small text-muted mb-1">
                                                    <strong><?php echo e($fieldValue->field->label); ?>:</strong>
                                                    <?php if($fieldValue->field->type == 'date'): ?>
                                                        <?php echo e(\Carbon\Carbon::parse($fieldValue->value)->format('Y-m-d')); ?>

                                                    <?php else: ?>
                                                        <?php echo e($fieldValue->value); ?>

                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>


                        </div>
                                                    <div>
                                <a href="<?php echo e($documentShowUrl); ?>" class="text-dark">
                                    <i class="fas fa-eye me-1"></i> عرض
                                </a>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>
<?php /**PATH C:\xampp\htdocs\mksnow\resources\views/frontend/documents/partials/search-result-card.blade.php ENDPATH**/ ?>