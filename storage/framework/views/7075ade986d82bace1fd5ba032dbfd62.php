


<?php $__env->startSection('title', (app()->getLocale() === 'ar' ? $section->name : $section->name_en) . ' - الوثائق'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <!-- رأس القسم -->
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" dir="<?php echo e(app()->getLocale() == 'ar' ? 'rtl' : 'ltr'); ?>">
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('home', app()->getLocale())); ?>">
                            <?php if(app()->getLocale() == 'ar'): ?>
                                الرئيسية
                                <i class="fas fa-home ms-1"></i>
                            <?php else: ?>
                                <i class="fas fa-home me-1"></i>
                                Home
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('frontend.documents.index')); ?>">
                            <?php echo e(app()->getLocale() == 'ar' ? 'الوثائق' : 'Documents'); ?>

                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?php echo e(app()->getLocale() === 'ar' ? $section->name : $section->name_en); ?></li>
                </ol>
            </nav>
            <br>
            <div class="text-center">
                <div class="mb-1">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 30px; height: 30px;">
                        <i class="fas fa-folder-open text-primary" style="font-size: 1rem;"></i>
                    </div>
                </div>
                <h5 class="fw-bold text-primary mb-3"><?php echo e(app()->getLocale() === 'ar' ? $section->name : $section->name_en); ?></h5>
                <?php if($section->description): ?>
                    <p class="lead text-muted mb-4"><?php echo e($section->description); ?></p>
                <?php endif; ?>
                
                <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
                    <?php
                        $docsCountBadge = isset($documents)
                            ? $documents->total()
                            : ((isset($categorizedResults) && $categorizedResults && isset($categorizedResults['phrase'], $categorizedResults['all']))
                                ? ($categorizedResults['phrase']->total() + $categorizedResults['all']->total())
                                : 0);
                    ?>
                    <span class="badge bg-primary fs-6"><?php echo e($docsCountBadge); ?> وثيقة</span>
                   
                </div>
            </div>
        </div>
    </div>
    
    <!-- الفلترة والبحث -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php if($section && $section->slug): ?>
                        <form action="<?php echo e(route('frontend.documents.section', [app()->getLocale(), $section->slug])); ?>" method="GET" id="filterForm">
                    <?php else: ?>
                        <form action="<?php echo e(route('frontend.documents.index')); ?>" method="GET" id="filterForm">
                    <?php endif; ?>
                        <div class="row g-4 align-items-end">
                            <!-- مربع البحث -->
                            <div class="col-lg-4">
                                <label for="search" class="form-label">
                                    <?php if(request('section_select') === 'all'): ?>
                                        البحث في جميع الأقسام
                                    <?php else: ?>
                                        البحث في <?php echo e(app()->getLocale() === 'ar' ? $section->name : $section->name_en); ?>

                                    <?php endif; ?>
                                </label>
                                <input type="text" id="search" name="search" class="form-control" value="<?php echo e(old('search', $searchTerm ?? request('search'))); ?>" placeholder="اكتب عبارة أو كلمات...">
                            </div>

    
                            
                            <!-- الترتيب -->
                            <div class="col-lg-2">
                                <label for="sort" class="form-label">الترتيب</label>
                                <select class="form-select" id="sort" name="sort" onchange="document.getElementById('filterForm').submit()">
                                    <option value="latest" <?php echo e(request('sort') == 'latest' ? 'selected' : ''); ?>>الأحدث</option>
                                    <option value="oldest" <?php echo e(request('sort') == 'oldest' ? 'selected' : ''); ?>>الأقدم</option>
                                    <option value="title" <?php echo e(request('sort') == 'title' ? 'selected' : ''); ?>>العنوان</option>
                                    <option value="views" <?php echo e(request('sort') == 'views' ? 'selected' : ''); ?>>الأكثر مشاهدة</option>
                                </select>
                            </div>

                            
                            <!-- عدد النتائج -->
                            <div class="col-lg-2">
                                <label for="per_page" class="form-label">عدد النتائج</label>
                                <select class="form-select" id="per_page" name="per_page" onchange="document.getElementById('filterForm').submit()">
                                    <option value="12" <?php echo e(request('per_page') == '12' ? 'selected' : ''); ?>>12</option>
                                    <option value="24" <?php echo e(request('per_page') == '24' ? 'selected' : ''); ?>>24</option>
                                    <option value="48" <?php echo e(request('per_page') == '48' ? 'selected' : ''); ?>>48</option>
                                </select>
                            </div>
                                                    <!-- اختيار القسم -->
                            <div class="col-4">
                                <label for="section_select" class="form-label">القسم</label>
                                <select class="form-select" id="section_select" name="section_select">
                                    <option value="all" <?php echo e(request('section_select') === 'all' ? 'selected' : ''); ?>>كل الأقسام</option>
                                    <?php if(isset($allSections) && $allSections->count()): ?>
                                        <?php $selectedSection = request('section_select'); ?>
                                        <?php $__currentLoopData = $allSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($sec->id); ?>" <?php echo e(($selectedSection !== null ? (string)$selectedSection === (string)$sec->id : (isset($section) && $section->id === $sec->id)) ? 'selected' : ''); ?>>
                                                <?php echo e($sec->name); ?> (<?php echo e($sec->published_docs_count ?? ($sec->published_documents_count ?? $sec->publishedDocumentsCount ?? $sec->published_documents_count ?? 0)); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- أزرار الإجراءات -->
                            <!-- <div class="col-lg-2">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> بحث</button>
                                    <?php if(request()->hasAny(['search', 'sort', 'per_page']) || request()->except(['page'])): ?>
                                        <?php if($section && $section->slug): ?>
                                            <a href="<?php echo e(route('frontend.documents.section', [app()->getLocale(), $section->slug])); ?>" class="btn btn-outline-danger">
                                                <i class="fas "fa-times"></i> مسح الفلاتر
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('frontend.documents.index')); ?>" class="btn btn-outline-danger">
                                                <i class="fas fa-times"></i> مسح الفلاتر
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div> -->
                        </div>
                        
                        <!-- فلترة الحقول المخصصة -->
                        <?php if($customFields->count() > 0): ?>
                            <div class="mt-4">
                                <div class="border-top pt-4">
                                    <h6 class="mb-3">فلترة متقدمة</h6>
                                    <div class="row g-3">
                                        <?php $__currentLoopData = $customFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="col-lg-4 col-md-6">
                                                <label for="field_<?php echo e($field->id); ?>" class="form-label"><?php echo e($field->label); ?></label>
                                                
                                                <?php switch($field->type):
                                                    case ('select'): ?>
                                                        <select class="form-select" id="field_<?php echo e($field->id); ?>" name="fields[<?php echo e($field->id); ?>]" onchange="document.getElementById('filterForm').submit()">
                                                            <option value="">جميع القيم</option>
                                                            <?php
                                                                $selectedValue = request("fields.{$field->id}");
                                                                $hasActiveFilters = !empty(request('fields'));
                                                            ?>
                                                            <?php if($field->options): ?>
                                                                <?php $__currentLoopData = $field->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <?php
                                                                        $count = $fieldCounts[$field->id][$option] ?? null;
                                                                        $showOption = !$hasActiveFilters || (!is_null($count) && (int)$count > 0) || ($selectedValue === $option);
                                                                    ?>
                                                                    <?php if($showOption): ?>
                                                                        <option value="<?php echo e($option); ?>" 
                                                                                <?php echo e($selectedValue === $option ? 'selected' : ''); ?>>
                                                                            <?php echo e($option); ?>

                                                                            <?php if(!is_null($count)): ?>
                                                                                (<?php echo e($count); ?>)
                                                                            <?php endif; ?>
                                                                        </option>
                                                                    <?php endif; ?>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            <?php endif; ?>
                                                        </select>
                                                        <?php break; ?>
                                                        
                                                    <?php case ('date'): ?>
                                                        <div class="row g-2">
                                                            <div class="col-4">
                                                                <select class="form-select form-select-sm" name="fields[<?php echo e($field->id); ?>][day]" onchange="document.getElementById('filterForm').submit()">
                                                                    <option value="">اليوم</option>
                                                                    <?php if(isset($fieldCounts[$field->id]['day'])): ?>
                                                                        <?php $__currentLoopData = $fieldCounts[$field->id]['day']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <option value="<?php echo e(sprintf('%02d', $day)); ?>" 
                                                                                    <?php echo e(request("fields.{$field->id}.day") == sprintf('%02d', $day) ? 'selected' : ''); ?>>
                                                                                <?php echo e($day); ?> (<?php echo e($count); ?>)
                                                                            </option>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                    <?php endif; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-4">
                                                                <select class="form-select form-select-sm" name="fields[<?php echo e($field->id); ?>][month]" onchange="document.getElementById('filterForm').submit()">
                                                                    <option value="">الشهر</option>
                                                                    <?php
                                                                        $months = [
                                                                            '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس', '04' => 'أبريل',
                                                                            '05' => 'مايو', '06' => 'يونيو', '07' => 'يوليو', '08' => 'أغسطس',
                                                                            '09' => 'سبتمبر', '10' => 'أكتوبر', '11' => 'نوفمبر', '12' => 'ديسمبر'
                                                                        ];
                                                                    ?>
                                                                    <?php if(isset($fieldCounts[$field->id]['month'])): ?>
                                                                        <?php $__currentLoopData = $fieldCounts[$field->id]['month']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthNum => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <option value="<?php echo e($monthNum); ?>" 
                                                                                    <?php echo e(request("fields.{$field->id}.month") == $monthNum ? 'selected' : ''); ?>>
                                                                                <?php echo e($months[$monthNum] ?? $monthNum); ?> (<?php echo e($count); ?>)
                                                                            </option>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                    <?php endif; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-4">
                                                                <select class="form-select form-select-sm" name="fields[<?php echo e($field->id); ?>][year]" onchange="document.getElementById('filterForm').submit()">
                                                                    <option value="">السنة</option>
                                                                    <?php if(isset($fieldCounts[$field->id]['year'])): ?>
                                                                        <?php $__currentLoopData = $fieldCounts[$field->id]['year']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <option value="<?php echo e($year); ?>" 
                                                                                    <?php echo e(request("fields.{$field->id}.year") == $year ? 'selected' : ''); ?>>
                                                                                <?php echo e($year); ?> (<?php echo e($count); ?>)
                                                                            </option>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                    <?php endif; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <?php break; ?>
                                                        
                                                    <?php case ('number'): ?>
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <input type="number" 
                                                                       class="form-control form-control-sm" 
                                                                       name="fields[<?php echo e($field->id); ?>][min]" 
                                                                       value="<?php echo e(request("fields.{$field->id}.min")); ?>"
                                                                       placeholder="الحد الأدنى">
                                                            </div>
                                                            <div class="col-6">
                                                                <input type="number" 
                                                                       class="form-control form-control-sm" 
                                                                       name="fields[<?php echo e($field->id); ?>][max]" 
                                                                       value="<?php echo e(request("fields.{$field->id}.max")); ?>"
                                                                       placeholder="الحد الأقصى">
                                                            </div>
                                                        </div>
                                                        <?php break; ?>
                                                        
                                                    <?php default: ?>
                                                        <input type="text" 
                                                               class="form-control" 
                                                               id="field_<?php echo e($field->id); ?>" 
                                                               name="fields[<?php echo e($field->id); ?>]" 
                                                               value="<?php echo e(request("fields.{$field->id}")); ?>"
                                                               placeholder="ابحث في <?php echo e($field->label); ?>...">
                                                <?php endswitch; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    
                                    <div class="mt-3 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> تطبيق الفلاتر
                                        </button>
                                        <?php if($section && $section->slug): ?>
                                            <a href="<?php echo e(route('frontend.documents.section', [app()->getLocale(), $section->slug])); ?>" 
                                               class="btn btn-outline-secondary">
                                                <i class="fas fa-times"></i> مسح الفلاتر
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('frontend.documents.index')); ?>" 
                                               class="btn btn-outline-secondary">
                                                <i class="fas fa-times"></i> مسح الفلاتر
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- عرض إحصائيات النتائج عند استخدام الفلاتر فقط -->
    <?php if(request('fields')): ?>
        <div class="row mb-4 search-results">
            <div class="col-12">
                <div class="alert alert-info d-flex align-items-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                        <?php
                            $countForFilters = 0;
                            if (isset($categorizedResults) && $categorizedResults) {
                                $countForFilters += isset($categorizedResults['phrase']) ? $categorizedResults['phrase']->total() : 0;
                                $countForFilters += isset($categorizedResults['all']) ? $categorizedResults['all']->total() : 0;
                                if (!empty($categorizedResults['per_word']) && is_array($categorizedResults['per_word'])) {
                                    foreach ($categorizedResults['per_word'] as $pg) {
                                        $countForFilters += $pg->total();
                                    }
                                }
                            } else {
                                $countForFilters = isset($documents) ? $documents->total() : 0;
                            }
                        ?>
                        تم العثور على <strong><?php echo e($countForFilters); ?></strong> وثيقة
                        مع الفلاتر المحددة
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(isset($categorizedResults) && $categorizedResults): ?>
        <?php $raw = $categorizedResults['raw'] ?? ''; ?>

        <div class="row mt-4">
            <div class="col-12">
                <h5 class="mb-3"><i class="fas fa-quote-left ms-1"></i> وثائق مطابقة  تمامًا لجملة البحث</h5>
                <?php if(($categorizedResults['phrase'] ?? null) && $categorizedResults['phrase']->count()): ?>
                    <div class="row g-3 mb-3">
                        <?php $__currentLoopData = $categorizedResults['phrase']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'exact'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="d-flex justify-content-center mb-4">
                        <?php echo e($categorizedResults['phrase']->appends(request()->except('page_p'))->links()); ?>

                    </div>
                <?php else: ?>
                    <div class="alert alert-light">لا توجد نتائج مطابقة تمامًا لعبارة "<?php echo e($raw); ?>"</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12">
                <h5 class="mb-3"><i class="fas fa-check-double ms-1"></i>وثائق بها جميع كلمات البحث</h5>
                <?php if(($categorizedResults['all'] ?? null) && $categorizedResults['all']->count()): ?>
                    <div class="row g-3 mb-3">
                        <?php $__currentLoopData = $categorizedResults['all']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'all'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="d-flex justify-content-center mb-4">
                        <?php echo e($categorizedResults['all']->appends(request()->except('page_a'))->links()); ?>

                    </div>
                <?php else: ?>
                    <div class="alert alert-light">لا توجد نتائج تحتوي كل كلمات البحث.</div>
                <?php endif; ?>
            </div>
        </div>

<?php if(!empty($categorizedResults['per_word'])): ?>
    <div class="row mt-2">
        <div class="col-12">
            <h5 class="mb-3"><i class="fas fa-check ms-1"></i>وثائق بها كلمة واحدة من البحث</h5>
            
            <!-- متغير لفحص إذا كان فيه نتائج لأي كلمة -->
            <?php $noResults = true; ?>
            
            <?php $__currentLoopData = $categorizedResults['per_word']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $word => $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($page->count()): ?> 
                    <?php $noResults = false; ?> <!-- إذا كانت فيه نتائج، خلي المتغير false -->
                    <div class="mb-2"><span class="badge bg-info"><?php echo e($word); ?></span></div>
                    <div class="row g-3 mb-3">
                        <?php $__currentLoopData = $page; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'any'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="d-flex justify-content-center mb-4">
                        <?php echo e($page->appends(request()->except('page_w'.$loop->index))->links()); ?>

                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
            <!-- عرض الرسالة فقط لو مفيش أي نتائج -->
            <?php if($noResults): ?>
                <div class="alert alert-light">لا توجد نتائج تحتوي كلمة واحدة من جملة البحث.</div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

        
    <?php endif; ?>

    <!-- قائمة الوثائق -->
    <?php if(isset($documents)): ?>
    <div class="documents-container">
    <?php if($documents->count() > 0): ?>
        <!-- العرض التقليدي -->
        <div class="row g-4 mb-5">
    <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-lg-4 col-md-6">
            <a href="<?php echo e(route('content.show', [app()->getLocale(), $section->name_en ?: $section->slug, $document->id])); ?>" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <!-- <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                         style="height: 200px;">
                        <i class="fas fa-file-alt text-muted" style="font-size: 3rem;"></i>
                    </div> -->

                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <?php if($document->is_featured): ?>
                                <span class="badge bg-warning text-dark me-1">مميز</span>
                            <?php endif; ?>
                            <span class="badge bg-primary"><?php echo e(app()->getLocale() === 'ar' ? $section->name : $section->name_en); ?></span>
                        </div>

                        <h5 class="card-title">
                            <?php echo e(Str::limit($document->title, 60)); ?>

                        </h5>

                        <?php if($document->excerpt): ?>
                            <p class="card-text text-muted flex-grow-1">
                                <?php echo e(Str::limit($document->excerpt, 100)); ?>

                            </p>
                        <?php endif; ?>

                        <!-- الحقول المخصصة المهمة -->
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

                        <!-- <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo e(number_format($document->views_count)); ?>

                                </small>
                                <small class="text-muted">
                                    <?php echo e($document->created_at->diffForHumans()); ?>

                                </small>
                            </div>
                        </div> -->
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

        
        <!-- التصفح -->
        <?php if($documents->hasPages()): ?>
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        <?php echo e($documents->appends(request()->query())->links()); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- لا توجد وثائق -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="text-muted mb-3">لا توجد وثائق</h3>
                    <?php if(request('fields')): ?>
                        <p class="text-muted mb-4">لم يتم العثور على وثائق تطابق معايير الفلترة المحددة.</p>
                        <?php if($section && $section->slug): ?>
                            <a href="<?php echo e(route('frontend.documents.section', [app()->getLocale(), $section->slug])); ?>" 
                               class="btn btn-primary">
                        <?php else: ?>
                            <a href="<?php echo e(route('frontend.documents.index')); ?>" 
                               class="btn btn-primary">
                        <?php endif; ?>
                            <i class="fas fa-times me-2"></i>
                            مسح الفلاتر
                        </a>
                    <?php else: ?>
                        <p class="text-muted mb-4">لا توجد وثائق في هذا القسم حالياً.</p>
                        <a href="<?php echo e(route('frontend.documents.index')); ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-arrow-right me-2"></i>
                            تصفح جميع الوثائق
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    </div> <!-- إغلاق documents-container -->
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// نظام الفلاتر بدون AJAX - تحديث الصفحة مباشرة
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    if (!form) return;
    
    // مستمع للتغييرات على القوائم المنسدلة - إرسال النموذج فوراً
    form.addEventListener('change', function(e) {
        const target = e.target;
        
        // التحقق من أن العنصر المتغير هو قائمة منسدلة للحقول المخصصة أو عناصر أخرى
        if (target.tagName === 'SELECT') {
            // إرسال النموذج مباشرة عند تغيير أي قائمة منسدلة
            form.submit();
        }
    });
    
    // زر مسح الفلاتر
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // مسح جميع قيم النموذج
            Array.from(form.elements).forEach(element => {
                if (element.name && element.name.startsWith('fields[')) {
                    element.value = '';
                }
            });
            
            // مسح قيمة البحث أيضاً
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // إعادة توجيه إلى الصفحة بدون فلاتر
            window.location.href = window.location.pathname;
        });
    }
    
    // دالة لتهيئة تأثيرات hover
    function initHoverEffects() {
        const hoverCards = document.querySelectorAll('.hover-card');
        hoverCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('shadow-lg');
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-lg');
                this.style.transform = 'translateY(0)';
            });
        });
    }
    
    // تهيئة تأثيرات hover عند تحميل الصفحة
    initHoverEffects();
});

// تأثيرات hover للبطاقات
$(document).ready(function() {
    $('.hover-card').hover(
        function() {
            $(this).addClass('shadow-lg').css('transform', 'translateY(-5px)');
        },
        function() {
            $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
        }
    );
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-card:hover {
    transform: translateY(-5px);
}

.card-img-top {
    transition: transform 0.3s ease;
}

.hover-card:hover .card-img-top {
    transform: scale(1.05);
}

.badge {
    font-size: 0.75em;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}



.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.border-top {
    border-top: 1px solid #dee2e6 !important;
}

.fs-6 {
    font-size: 1rem !important;
}

.form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert-info {
    border-right: 4px solid #0dcaf0;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // جعل القوائم المنسدلة تحدث الصفحة فوراً
    const dropdowns = document.querySelectorAll('#sort, #per_page, select[name^="fields["][name$="]"]');
    
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // جعل قوائم التاريخ المنسدلة تحدث الصفحة فوراً
    const dateDropdowns = document.querySelectorAll('select[name*="[day]"], select[name*="[month]"], select[name*="[year]"]');
    
    dateDropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>

<style>
.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
<?php $__env->stopPush(); ?>



<?php $__env->startPush('scripts'); ?>
<script>
// نظام الفلاتر بدون AJAX - تحديث الصفحة مباشرة
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    if (!form) return;
    
    // مستمع للتغييرات على القوائم المنسدلة - إرسال النموذج فوراً
    form.addEventListener('change', function(e) {
        const target = e.target;
        
        // التحقق من أن العنصر المتغير هو قائمة منسدلة للحقول المخصصة أو عناصر أخرى
        if (target.tagName === 'SELECT') {
            // إرسال النموذج مباشرة عند تغيير أي قائمة منسدلة
            form.submit();
        }
    });
    
    // زر مسح الفلاتر
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // مسح جميع قيم النموذج
            Array.from(form.elements).forEach(element => {
                if (element.name && element.name.startsWith('fields[')) {
                    element.value = '';
                }
            });
            
            // مسح قيمة البحث أيضاً
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // إعادة توجيه إلى الصفحة بدون فلاتر
            window.location.href = window.location.pathname;
        });
    }
    
    // دالة لتهيئة تأثيرات hover
    function initHoverEffects() {
        const hoverCards = document.querySelectorAll('.hover-card');
        hoverCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('shadow-lg');
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-lg');
                this.style.transform = 'translateY(0)';
            });
        });
    }
    
    // تهيئة تأثيرات hover عند تحميل الصفحة
    initHoverEffects();
});

// تأثيرات hover للبطاقات
$(document).ready(function() {
    $('.hover-card').hover(
        function() {
            $(this).addClass('shadow-lg').css('transform', 'translateY(-5px)');
        },
        function() {
            $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
        }
    );
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-card:hover {
    transform: translateY(-5px);
}

.card-img-top {
    transition: transform 0.3s ease;
}

.hover-card:hover .card-img-top {
    transform: scale(1.05);
}

.badge {
    font-size: 0.75em;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}



.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.border-top {
    border-top: 1px solid #dee2e6 !important;
}

.fs-6 {
    font-size: 1rem !important;
}

.form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert-info {
    border-right: 4px solid #0dcaf0;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // جعل القوائم المنسدلة تحدث الصفحة فوراً
    const dropdowns = document.querySelectorAll('#sort, #per_page, select[name^="fields["][name$="]"]');
    
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // جعل قوائم التاريخ المنسدلة تحدث الصفحة فوراً
    const dateDropdowns = document.querySelectorAll('select[name*="[day]"], select[name*="[month]"], select[name*="[year]"]');
    
    dateDropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>

<style>
.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
<?php $__env->stopPush(); ?>



<?php $__env->startPush('scripts'); ?>
<script>
// نظام الفلاتر بدون AJAX - تحديث الصفحة مباشرة
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    if (!form) return;
    
    // مستمع للتغييرات على القوائم المنسدلة - إرسال النموذج فوراً
    form.addEventListener('change', function(e) {
        const target = e.target;
        
        // التحقق من أن العنصر المتغير هو قائمة منسدلة للحقول المخصصة أو عناصر أخرى
        if (target.tagName === 'SELECT') {
            // إرسال النموذج مباشرة عند تغيير أي قائمة منسدلة
            form.submit();
        }
    });
    
    // زر مسح الفلاتر
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // مسح جميع قيم النموذج
            Array.from(form.elements).forEach(element => {
                if (element.name && element.name.startsWith('fields[')) {
                    element.value = '';
                }
            });
            
            // مسح قيمة البحث أيضاً
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // إعادة توجيه إلى الصفحة بدون فلاتر
            window.location.href = window.location.pathname;
        });
    }
    
    // دالة لتهيئة تأثيرات hover
    function initHoverEffects() {
        const hoverCards = document.querySelectorAll('.hover-card');
        hoverCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('shadow-lg');
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-lg');
                this.style.transform = 'translateY(0)';
            });
        });
    }
    
    // تهيئة تأثيرات hover عند تحميل الصفحة
    initHoverEffects();
});

// تأثيرات hover للبطاقات
$(document).ready(function() {
    $('.hover-card').hover(
        function() {
            $(this).addClass('shadow-lg').css('transform', 'translateY(-5px)');
        },
        function() {
            $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
        }
    );
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-card:hover {
    transform: translateY(-5px);
}

.card-img-top {
    transition: transform 0.3s ease;
}

.hover-card:hover .card-img-top {
    transform: scale(1.05);
}

.badge {
    font-size: 0.75em;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}



.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.border-top {
    border-top: 1px solid #dee2e6 !important;
}

.fs-6 {
    font-size: 1rem !important;
}

.form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert-info {
    border-right: 4px solid #0dcaf0;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // جعل القوائم المنسدلة تحدث الصفحة فوراً
    const dropdowns = document.querySelectorAll('#sort, #per_page, select[name^="fields["][name$="]"]');
    
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // جعل قوائم التاريخ المنسدلة تحدث الصفحة فوراً
    const dateDropdowns = document.querySelectorAll('select[name*="[day]"], select[name*="[month]"], select[name*="[year]"]');
    
    dateDropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>

<style>
.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
<?php $__env->stopPush(); ?>



<?php $__env->startPush('scripts'); ?>
<script>
// نظام الفلاتر بدون AJAX - تحديث الصفحة مباشرة
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    if (!form) return;
    
    // مستمع للتغييرات على القوائم المنسدلة - إرسال النموذج فوراً
    form.addEventListener('change', function(e) {
        const target = e.target;
        
        // التحقق من أن العنصر المتغير هو قائمة منسدلة للحقول المخصصة أو عناصر أخرى
        if (target.tagName === 'SELECT') {
            // إرسال النموذج مباشرة عند تغيير أي قائمة منسدلة
            form.submit();
        }
    });
    
    // زر مسح الفلاتر
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // مسح جميع قيم النموذج
            Array.from(form.elements).forEach(element => {
                if (element.name && element.name.startsWith('fields[')) {
                    element.value = '';
                }
            });
            
            // مسح قيمة البحث أيضاً
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // إعادة توجيه إلى الصفحة بدون فلاتر
            window.location.href = window.location.pathname;
        });
    }
    
    // دالة لتهيئة تأثيرات hover
    function initHoverEffects() {
        const hoverCards = document.querySelectorAll('.hover-card');
        hoverCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('shadow-lg');
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-lg');
                this.style.transform = 'translateY(0)';
            });
        });
    }
    
    // تهيئة تأثيرات hover عند تحميل الصفحة
    initHoverEffects();
});

// تأثيرات hover للبطاقات
$(document).ready(function() {
    $('.hover-card').hover(
        function() {
            $(this).addClass('shadow-lg').css('transform', 'translateY(-5px)');
        },
        function() {
            $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
        }
    );
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-card:hover {
    transform: translateY(-5px);
}

.card-img-top {
    transition: transform 0.3s ease;
}

.hover-card:hover .card-img-top {
    transform: scale(1.05);
}

.badge {
    font-size: 0.75em;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}



.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.border-top {
    border-top: 1px solid #dee2e6 !important;
}

.fs-6 {
    font-size: 1rem !important;
}

.form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert-info {
    border-right: 4px solid #0dcaf0;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // جعل القوائم المنسدلة تحدث الصفحة فوراً
    const dropdowns = document.querySelectorAll('#sort, #per_page, select[name^="fields["][name$="]"]');
    
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // جعل قوائم التاريخ المنسدلة تحدث الصفحة فوراً
    const dateDropdowns = document.querySelectorAll('select[name*="[day]"], select[name*="[month]"], select[name*="[year]"]');
    
    dateDropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>

<style>
.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/frontend/documents/section.blade.php ENDPATH**/ ?>