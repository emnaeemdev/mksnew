

<?php $__env->startSection('title', $document->title); ?>
<?php $__env->startSection('description', $document->excerpt ?: Str::limit(strip_tags($document->content), 160)); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <!-- التنقل -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb <?php echo e(app()->getLocale() == 'ar' ?  : ''); ?>" dir="<?php echo e(app()->getLocale() == 'ar' ? 'rtl' : 'ltr'); ?>">
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('home', ['locale' => app()->getLocale()])); ?>">
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
                    <?php if($document->section && $document->section->slug): ?>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('frontend.documents.section', [app()->getLocale(), $document->section->slug])); ?>"><?php echo e(app()->getLocale() === 'ar' ? $document->section->name : $document->section->name_en); ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('frontend.documents.index')); ?>"><?php echo e(app()->getLocale() == 'ar' ? 'الوثائق' : 'Documents'); ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active"><?php echo e(Str::limit($document->title, 50)); ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <!-- المحتوى الرئيسي -->
        <div class="col-lg-8">
            <!-- رأس الوثيقة -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <!-- العلامات والحالة -->
                    <div class="mb-3">
                        <?php if($document->section && $document->section->name): ?>
                            <span class="badge bg-primary me-2"><?php echo e(app()->getLocale() === 'ar' ? $document->section->name : $document->section->name_en); ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary me-2"><?php echo e(app()->getLocale() == 'ar' ? 'قسم غير محدد' : 'Undefined Section'); ?></span>
                        <?php endif; ?>
                        <?php if($document->is_featured): ?>
                            <span class="badge bg-warning text-dark me-2">مميز</span>
                        <?php endif; ?>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-eye me-1"></i>
                            <?php echo e(number_format($document->views_count)); ?> مشاهدة
                        </span>
                    </div>
                    
                    <!-- العنوان -->
                    <h1 class="display-6 fw-bold text-dark mb-3"><?php echo e($document->title); ?></h1>
                    
                    <!-- الملخص -->
                    <?php if($document->excerpt): ?>
                        <div class="alert alert-info border-0">
                            <h6 class="alert-heading mb-2">
                                <i class="fas fa-info-circle me-2"></i>
                                ملخص الوثيقة
                            </h6>
                            <p class="mb-0 lead"><?php echo e($document->excerpt); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- معلومات النشر -->
                    <div class="d-flex flex-wrap align-items-center text-muted mb-4">
                        <div class="me-4 mb-2">
                            <i class="fas fa-calendar me-1"></i>
                            <span><?php echo e($document->created_at->format('Y-m-d')); ?></span>
                        </div>
                        <div class="me-4 mb-2">
                            <i class="fas fa-clock me-1"></i>
                            <span><?php echo e($document->created_at->diffForHumans()); ?></span>
                        </div>
                        <?php if($document->updated_at != $document->created_at): ?>
                            <div class="me-4 mb-2">
                                <i class="fas fa-edit me-1"></i>
                                <span>آخر تحديث: <?php echo e($document->updated_at->diffForHumans()); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- أزرار المشاركة -->
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button class="btn btn-outline-primary btn-sm" onclick="shareDocument('facebook')">
                            <i class="fab fa-facebook-f me-1"></i> فيسبوك
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="shareDocument('twitter')">
                            <i class="fab fa-twitter me-1"></i> تويتر
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="shareDocument('whatsapp')">
                            <i class="fab fa-whatsapp me-1"></i> واتساب
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyDocumentLink()">
                            <i class="fas fa-link me-1"></i> نسخ الرابط
                        </button>
                        <button class="btn btn-outline-dark btn-sm" onclick="printDocument()">
                            <i class="fas fa-print me-1"></i> طباعة
                        </button>
                    </div>
                </div>
            </div>
            
            
            
            <!-- محتوى الوثيقة -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="document-content">
                        <?php echo $document->content; ?>

                    </div>
                </div>
            </div>
            
            <!-- تمت إزالة "معلومات إضافية" من العمود الرئيسي وسيجري عرضها في الشريط الجانبي -->
            
            <!-- التنقل بين الوثائق -->
            <?php if($previousDocument || $nextDocument): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row">
                            <?php if($previousDocument): ?>
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-arrow-right text-primary" style="font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">الوثيقة السابقة</small>
                                            <?php if($previousDocument->section && $previousDocument->section->slug): ?>
                                                <a href="<?php echo e(route('content.show', [app()->getLocale(), $previousDocument->section->name_en ?: $previousDocument->section->slug, $previousDocument->id])); ?>" 
                                                   class="text-decoration-none fw-bold">
                                                    <?php echo e(Str::limit($previousDocument->title, 40)); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted fw-bold"><?php echo e(Str::limit($previousDocument->title, 40)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($nextDocument): ?>
                                <div class="col-md-6 text-md-end">
                                    <div class="d-flex align-items-center <?php echo e($previousDocument ? 'justify-content-md-end' : ''); ?>">
                                        <div class="me-3 order-md-2">
                                            <i class="fas fa-arrow-left text-primary" style="font-size: 1.5rem;"></i>
                                        </div>
                                        <div class="order-md-1">
                                            <small class="text-muted d-block">الوثيقة التالية</small>
                                            <?php if($nextDocument->section && $nextDocument->section->slug): ?>
                                                <a href="<?php echo e(route('content.show', [app()->getLocale(), $nextDocument->section->name_en ?: $nextDocument->section->slug, $nextDocument->id])); ?>" 
                                                   class="text-decoration-none fw-bold">
                                                    <?php echo e(Str::limit($nextDocument->title, 40)); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted fw-bold"><?php echo e(Str::limit($nextDocument->title, 40)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- الشريط الجانبي -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 120px;">
                <?php if($document->fieldValues->count() > 0): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-list-ul me-2"></i>
                                معلومات إضافية
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <?php $__currentLoopData = $document->fieldValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-12">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="text-primary mb-2">
                                                <i class="fas fa-tag me-1"></i>
                                                <?php echo e($fieldValue->field->label); ?>

                                            </h6>
                                            
                                            <?php switch($fieldValue->field->type):
                                                case ('date'): ?>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-calendar text-success me-2"></i>
                                                        <span class="fw-bold"><?php echo e(\Carbon\Carbon::parse($fieldValue->value)->format('Y-m-d')); ?></span>
                                                    </div>
                                                    <?php break; ?>
                                                
                                                <?php case ('number'): ?>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-hashtag text-info me-2"></i>
                                                        <span class="fw-bold fs-5"><?php echo e(number_format($fieldValue->value)); ?></span>
                                                    </div>
                                                    <?php break; ?>
                                                
                                                <?php case ('select'): ?>
                                                    <span class="badge bg-primary fs-6 px-3 py-2"><?php echo e($fieldValue->value); ?></span>
                                                    <?php break; ?>
                                                
                                                <?php case ('multiselect'): ?>
                                                    <?php
                                                        $values = is_array($fieldValue->value) ? $fieldValue->value : (json_decode($fieldValue->value, true) ?: []);
                                                    ?>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php $__currentLoopData = $values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="badge bg-secondary"><?php echo e($value); ?></span>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                    <?php break; ?>
                                                
                                                <?php case ('file'): ?>
                                                    <a href="<?php echo e(asset('storage/' . $fieldValue->value)); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-download me-2"></i>
                                                        تحميل الملف
                                                    </a>
                                                    <?php break; ?>
                                                
                                                <?php case ('textarea'): ?>
                                                    <div class="text-break" style="white-space: pre-wrap; line-height: 1.6;"><?php echo e($fieldValue->value); ?></div>
                                                    <?php break; ?>
                                                
                                                <?php default: ?>
                                                    <p class="mb-0 text-break fw-bold"><?php echo e($fieldValue->value); ?></p>
                                            <?php endswitch; ?>
                                            
                                            <?php if($fieldValue->field->help_text): ?>
                                                <small class="text-muted d-block mt-2">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    <?php echo e($fieldValue->field->help_text); ?>

                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- معلومات سريعة -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            معلومات الوثيقة
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">القسم</small>
                            <?php if($document->section && $document->section->slug): ?>
                                <a href="<?php echo e(route('frontend.documents.section', [app()->getLocale(), $document->section->slug])); ?>" 
                                   class="text-decoration-none">
                            <?php else: ?>
                                <span class="text-muted">
                            <?php endif; ?>
                                <span class="badge bg-primary"><?php echo e(app()->getLocale() === 'ar' ? (optional($document->section)->name ?? 'غير مصنّف') : (optional($document->section)->name_en ?? 'Uncategorized')); ?></span>
                            <?php if($document->section && $document->section->slug): ?>
                                </a>
                            <?php else: ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">تاريخ النشر</small>
                            <span class="fw-bold"><?php echo e($document->created_at->format('Y-m-d')); ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">عدد المشاهدات</small>
                            <span class="fw-bold text-primary"><?php echo e(number_format($document->views_count)); ?></span>
                        </div>
                        
                        <?php if($document->fieldValues->count() > 0): ?>
                            <div class="mb-3">
                                <small class="text-muted d-block">الحقول المخصصة</small>
                                <span class="fw-bold"><?php echo e($document->fieldValues->count()); ?> حقل</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">وقت القراءة المتوقع</small>
                            <span class="fw-bold">
                                <?php echo e(ceil(str_word_count(strip_tags($document->content)) / 200)); ?> دقيقة
                            </span>
                        </div>
                        
                        <div class="d-grid">
                            <?php if($document->section && $document->section->slug): ?>
                                <a href="<?php echo e(route('frontend.documents.section', [app()->getLocale(), $document->section->slug])); ?>" 
                                   class="btn btn-outline-primary">
                            <?php else: ?>
                                <span class="btn btn-outline-secondary disabled">
                            <?php endif; ?>
                                <i class="fas fa-list me-2"></i>
                                المزيد من <?php echo e(app()->getLocale() === 'ar' ? (optional($document->section)->name ?? 'قسم غير محدد') : (optional($document->section)->name_en ?? 'Undefined Section')); ?>

                            <?php if($document->section && $document->section->slug): ?>
                                </a>
                            <?php else: ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- وثائق ذات صلة -->
            <?php if($relatedDocuments->count() > 0): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            وثائق ذات صلة
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <?php $__currentLoopData = $relatedDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-3 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                                <div class="d-flex">
                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-file-alt text-muted"></i>
                                    </div>
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <?php if($related->section && $related->section->slug): ?>
                                                <a href="<?php echo e(route('content.show', [app()->getLocale(), $related->section->name_en ?: $related->section->slug, $related->id])); ?>" class="text-dark text-decoration-none">
                                                    <?php echo e(Str::limit($related->title, 50)); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted"><?php echo e(Str::limit($related->title, 50)); ?></span>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="me-2">
                                                <i class="fas fa-eye me-1"></i>
                                                <?php echo e(number_format($related->views_count)); ?>

                                            </span>
                                            <span><?php echo e($related->created_at->diffForHumans()); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- إحصائيات القسم -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        إحصائيات <?php echo e(app()->getLocale() === 'ar' ? (optional($document->section)->name ?? 'قسم غير محدد') : (optional($document->section)->name_en ?? 'Undefined Section')); ?>

                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 text-primary mb-1"><?php echo e($sectionStats['total_documents']); ?></div>
                            <small class="text-muted">وثيقة</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 text-success mb-1"><?php echo e(number_format($sectionStats['total_views'])); ?></div>
                            <small class="text-muted">مشاهدة</small>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <?php if($document->section && $document->section->slug): ?>
                            <a href="<?php echo e(route('frontend.documents.section', [app()->getLocale(), $document->section->slug])); ?>" 
                               class="btn btn-sm btn-outline-primary">
                                استكشاف القسم
                            </a>
                        <?php else: ?>
                            <span class="btn btn-sm btn-outline-secondary disabled">
                                القسم غير متاح
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// مشاركة الوثيقة
function shareDocument(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?php echo e($document->title); ?>');
    const description = encodeURIComponent('<?php echo e($document->excerpt ?: Str::limit(strip_tags($document->content), 100)); ?>');
    
    let shareUrl = '';
    
    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${title}%20${url}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

// نسخ رابط الوثيقة
function copyDocumentLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        // إظهار رسالة نجاح
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check text-success me-1"></i> تم النسخ';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    }).catch(function() {
        alert('فشل في نسخ الرابط');
    });
}

// طباعة الوثيقة
function printDocument() {
    window.print();
}

$(document).ready(function() {
    // تحسين تجربة القراءة
    $('.document-content img').addClass('img-fluid rounded');
    
    // إضافة تأثيرات للروابط
    $('.document-content a').addClass('text-decoration-none').hover(
        function() {
            $(this).addClass('text-decoration-underline');
        },
        function() {
            $(this).removeClass('text-decoration-underline');
        }
    );
    
    // تحسين الجداول
    $('.document-content table').addClass('table table-striped table-responsive');
    
    // إضافة smooth scrolling للروابط الداخلية
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.document-content {
    line-height: 1.8;
    font-size: 1.1rem;
    color: #333;
}
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/frontend/documents/show.blade.php ENDPATH**/ ?>