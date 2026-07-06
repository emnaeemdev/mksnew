

<?php $__env->startSection('title', 'تصفح الوثائق'); ?>
<?php $__env->startSection('description', 'تصفح مجموعة شاملة من الوثائق والمستندات'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <!-- التنقل -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" dir="rtl">
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('home')); ?>">
                            الرئيسية
                            <i class="fas fa-home ms-1"></i>
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('frontend.documents.index')); ?>">
                            الوثائق
                        </a>
                    </li>
                    <li class="breadcrumb-item active">تصفح الوثائق</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- رأس الصفحة -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold text-primary mb-3">
                <i class="fas fa-folder-open me-3"></i>
                تصفح الوثائق
            </h1>
            <p class="lead text-muted mb-4">اكتشف مجموعة شاملة من الوثائق مصنفة حسب الأقسام</p>
        </div>
    </div>
    
    <!-- نموذج البحث العام -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="<?php echo e(route('frontend.documents.search', app()->getLocale())); ?>" method="GET" id="generalSearchForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-6">
                                <label for="search" class="form-label">كلمة/عبارة البحث</label>
                                <input type="text" id="search" name="search" class="form-control" value="<?php echo e(old('search', $searchTerm ?? request('search'))); ?>" placeholder="اكتب عبارة أو كلمات...">
                            </div>
                            <div class="col-lg-4">
                                <label for="section" class="form-label">القسم</label>
                                <select class="form-select" id="section" name="section">
                                    <option value="">كل الأقسام</option>
                                    <?php if(isset($sections) && $sections->count()): ?>
                                        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($sec->id); ?>" <?php echo e((string)request('section') === (string)$sec->id ? 'selected' : ''); ?>>
                                                <?php echo e(app()->getLocale() === 'ar' ? $sec->name : $sec->name_en); ?> (<?php echo e($sec->published_documents_count); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-lg-2 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> بحث</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(isset($categorizedResults) && $categorizedResults): ?>
        <?php echo $__env->make('frontend.documents.partials.categorized-search-results', ['categorizedResults' => $categorizedResults], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <!-- تنبيه إيقاف البحث -->

<hr>
<br>
    <!-- أزرار التنقل -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="<?php echo e(route('frontend.documents.index')); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-list me-2"></i>
                    جميع الوثائق
                </a>
                <a href="<?php echo e(route('home')); ?>" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-home me-2"></i>
                    الصفحة الرئيسية
                </a>
            </div>
        </div>
    </div>

    <!-- الأقسام المتاحة -->
    <?php if(isset($sections) && $sections->count() > 0): ?>
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mb-4">الأقسام المتاحة</h3>
                <div class="row g-4">
                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm hover-card">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-folder-open text-primary" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5 class="card-title"><?php echo e(app()->getLocale() === 'ar' ? $section->name : $section->name_en); ?></h5>
                                    <?php if($section->description): ?>
                                        <p class="card-text text-muted"><?php echo e(Str::limit($section->description, 100)); ?></p>
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <span class="badge bg-primary"><?php echo e($section->documents_count ?? 0); ?> وثيقة</span>
                                    </div>
                                    <?php if($section->slug): ?>
                                        <a href="<?php echo e(route('frontend.documents.section', [app()->getLocale(), $section->slug])); ?>" 
                                           class="btn btn-outline-primary">
                                            تصفح القسم
                                            <i class="fas fa-arrow-left ms-1"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
    margin-bottom: 1rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "/";
    color: #6c757d;
}

.display-5 {
    font-size: 2.5rem;
    line-height: 1.2;
}

@media (max-width: 768px) {
    .display-5 {
        font-size: 2rem;
    }
}

.alert-info {
    border-right: 4px solid #0dcaf0;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnew\resources\views/frontend/documents/search.blade.php ENDPATH**/ ?>