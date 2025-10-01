<?php $__env->startSection('title', 'النشرات'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- عنوان الصفحة -->
            <div class="text-center mb-5">
                <h1 class="display-4 text-primary mb-3">النشرات الإحصائية</h1>
                <p class="lead text-muted">تصفح أحدث النشرات والتقارير الإحصائية</p>
            </div>
            
            <!-- شريط البحث والفلاتر -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('frontend.nashras.index')); ?>" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">البحث</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       value="<?php echo e(request('search')); ?>" 
                                       placeholder="ابحث في العناوين والمحتوى...">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="year" class="form-label">السنة</label>
                            <select class="form-select" id="year" name="year">
                                <option value="">جميع السنوات</option>
                                <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year); ?>" <?php echo e(request('year') == $year ? 'selected' : ''); ?>>
                                        <?php echo e($year); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="sort" class="form-label">ترتيب حسب</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="latest" <?php echo e(request('sort') == 'latest' ? 'selected' : ''); ?>>الأحدث</option>
                                <option value="oldest" <?php echo e(request('sort') == 'oldest' ? 'selected' : ''); ?>>الأقدم</option>
                                <option value="title" <?php echo e(request('sort') == 'title' ? 'selected' : ''); ?>>العنوان</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> تطبيق
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- النتائج -->
            <?php if($nashras->count() > 0): ?>
                <!-- إحصائيات سريعة -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-file-alt fa-2x mb-2"></i>
                                <h4><?php echo e($nashras->total()); ?></h4>
                                <p class="mb-0">إجمالي النشرات</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar fa-2x mb-2"></i>
                                <h4><?php echo e($years->count()); ?></h4>
                                <p class="mb-0">سنوات النشر</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-download fa-2x mb-2"></i>
                                <h4><?php echo e($nashras->where('pdf_file', '!=', null)->count()); ?></h4>
                                <p class="mb-0">ملفات PDF</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-table fa-2x mb-2"></i>
                                <h4><?php echo e($nashras->where('google_drive_url', '!=', null)->count()); ?></h4>
                                <p class="mb-0">جداول Excel</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- التبويبات -->
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <ul class="nav nav-tabs card-header-tabs" id="nashrasTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" 
                                        id="grid-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#grid-view" 
                                        type="button" 
                                        role="tab">
                                    <i class="fas fa-th-large"></i> عرض الشبكة
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" 
                                        id="table-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#table-view" 
                                        type="button" 
                                        role="tab">
                                    <i class="fas fa-table"></i> عرض الجدول
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" 
                                        id="timeline-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#timeline-view" 
                                        type="button" 
                                        role="tab">
                                    <i class="fas fa-clock"></i> الخط الزمني
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-body">
                        <div class="tab-content" id="nashrasTabContent">
                            <!-- عرض الشبكة -->
                            <div class="tab-pane fade show active" id="grid-view" role="tabpanel">
                                <div class="row">
                                    <?php $__currentLoopData = $nashras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nashra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="card h-100 shadow-sm hover-shadow">
                                                <?php if($nashra->featured_image): ?>
                                                    <img src="<?php echo e($nashra->featured_image_url); ?>" 
                                                         class="card-img-top" 
                                                         alt="<?php echo e($nashra->title_ar); ?>" 
                                                         style="height: 200px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                                         style="height: 200px;">
                                                        <i class="fas fa-file-alt fa-3x text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="card-body d-flex flex-column">
                                                    <h5 class="card-title text-primary"><?php echo e($nashra->title_ar); ?></h5>
                                                    
                                                    <?php if($nashra->subtitle_ar): ?>
                                                        <p class="card-text text-muted small"><?php echo e($nashra->subtitle_ar); ?></p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($nashra->content_ar): ?>
                                                        <p class="card-text flex-grow-1">
                                                            <?php echo e(Str::limit(strip_tags($nashra->content_ar), 100)); ?>

                                                        </p>
                                                    <?php endif; ?>
                                                    
                                                    <div class="mt-auto">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar"></i>
                                                                <?php echo e($nashra->formatted_published_date); ?>

                                                            </small>
                                                            <div>
                                                                <?php if($nashra->pdf_file): ?>
                                                                    <span class="badge bg-danger me-1">
                                                                        <i class="fas fa-file-pdf"></i> PDF
                                                                    </span>
                                                                <?php endif; ?>
                                                                <?php if($nashra->google_drive_url): ?>
                                                                    <span class="badge bg-success">
                                                                        <i class="fas fa-table"></i> Excel
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="d-grid gap-2">
                                                            <a href="<?php echo e(route('frontend.nashras.show', ['locale' => app()->getLocale(), 'nashra' => $nashra->id])); ?>" 
                                                               class="btn btn-primary btn-sm">
                                                                <i class="fas fa-eye"></i> عرض التفاصيل
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                            
                            <!-- عرض الجدول -->
                            <div class="tab-pane fade" id="table-view" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>الصورة</th>
                                                <th>العنوان</th>
                                                <th>العنوان الفرعي</th>
                                                <th>تاريخ النشر</th>
                                                <th>الملفات</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $nashras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nashra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td>
                                                        <?php if($nashra->featured_image): ?>
                                                            <img src="<?php echo e($nashra->featured_image_url); ?>" 
                                                                 alt="<?php echo e($nashra->title_ar); ?>" 
                                                                 class="rounded" 
                                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                                 style="width: 50px; height: 50px;">
                                                                <i class="fas fa-file-alt text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo e($nashra->title_ar); ?></strong>
                                                    </td>
                                                    <td><?php echo e($nashra->subtitle_ar ?? '-'); ?></td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo e($nashra->formatted_published_date); ?>

                                                        </small>
                                                    </td>
                                                    <td>
                                                        <?php if($nashra->pdf_file): ?>
                                                            <a href="<?php echo e(route('frontend.nashras.download-pdf', [app()->getLocale(), $nashra->id])); ?>" 
                                                               class="btn btn-sm btn-outline-danger me-1" 
                                                               title="تحميل PDF">
                                                                <i class="fas fa-file-pdf"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if($nashra->google_drive_url): ?>
                                                            <a href="<?php echo e($nashra->google_drive_url); ?>" 
                                                               target="_blank" 
                                                               class="btn btn-sm btn-outline-success" 
                                                               title="فتح Excel">
                                                                <i class="fas fa-table"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo e(route('frontend.nashras.show', ['locale' => app()->getLocale(), 'nashra' => $nashra->id])); ?>" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye"></i> عرض
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- الخط الزمني -->
                            <div class="tab-pane fade" id="timeline-view" role="tabpanel">
                                <div class="timeline">
                                    <?php $__currentLoopData = $nashras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $nashra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="timeline-item <?php echo e($index % 2 == 0 ? 'timeline-item-left' : 'timeline-item-right'); ?>">
                                            <div class="timeline-marker">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="card-title text-primary mb-0"><?php echo e($nashra->title_ar); ?></h6>
                                                            <small class="text-muted"><?php echo e($nashra->formatted_published_date); ?></small>
                                                        </div>
                                                        
                                                        <?php if($nashra->subtitle_ar): ?>
                                                            <p class="card-text small text-muted mb-2"><?php echo e($nashra->subtitle_ar); ?></p>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($nashra->content_ar): ?>
                                                            <p class="card-text"><?php echo e(Str::limit(strip_tags($nashra->content_ar), 80)); ?></p>
                                                        <?php endif; ?>
                                                        
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <?php if($nashra->pdf_file): ?>
                                                                    <span class="badge bg-danger me-1">
                                                                        <i class="fas fa-file-pdf"></i> PDF
                                                                    </span>
                                                                <?php endif; ?>
                                                                <?php if($nashra->google_drive_url): ?>
                                                                    <span class="badge bg-success">
                                                                        <i class="fas fa-table"></i> Excel
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <a href="<?php echo e(route('frontend.nashras.show', ['locale' => app()->getLocale(), 'nashra' => $nashra->id])); ?>" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i> عرض
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- الترقيم -->
                <div class="d-flex justify-content-center mt-4">
                    <?php echo e($nashras->appends(request()->query())->links()); ?>

                </div>
            <?php else: ?>
                <!-- لا توجد نتائج -->
                <div class="text-center py-5">
                    <div class="card shadow-sm">
                        <div class="card-body py-5">
                            <i class="fas fa-search fa-4x text-muted mb-4"></i>
                            <h3 class="text-muted">لا توجد نشرات</h3>
                            <p class="text-muted">لم يتم العثور على أي نشرات تطابق معايير البحث الخاصة بك.</p>
                            <a href="<?php echo e(route('frontend.nashras.index')); ?>" class="btn btn-primary">
                                <i class="fas fa-refresh"></i> عرض جميع النشرات
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.hover-shadow {
    transition: box-shadow 0.3s ease;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 2px;
    height: 100%;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    width: 50%;
}

.timeline-item-left {
    left: 0;
    padding-right: 30px;
}

.timeline-item-right {
    left: 50%;
    padding-left: 30px;
}

.timeline-marker {
    position: absolute;
    top: 20px;
    width: 40px;
    height: 40px;
    background: #007bff;
    border: 3px solid #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.timeline-item-left .timeline-marker {
    right: -20px;
}

.timeline-item-right .timeline-marker {
    left: -20px;
}

.timeline-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .timeline::before {
        left: 20px;
    }
    
    .timeline-item {
        width: 100%;
        left: 0 !important;
        padding-left: 50px !important;
        padding-right: 0 !important;
    }
    
    .timeline-marker {
        left: 0 !important;
        right: auto !important;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // تفعيل التبويبات
    var triggerTabList = [].slice.call(document.querySelectorAll('#nashrasTab button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })
    
    // حفظ التبويب المحدد في localStorage
    $('#nashrasTab button').on('shown.bs.tab', function (e) {
        localStorage.setItem('activeNashrasTab', e.target.id);
    });
    
    // استرجاع التبويب المحدد من localStorage
    var activeTab = localStorage.getItem('activeNashrasTab');
    if (activeTab) {
        var tabElement = document.getElementById(activeTab);
        if (tabElement) {
            var tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }
    
    // تحديث الصفحة عند تغيير الفلاتر
    $('#year, #sort').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/frontend/nashras/index.blade.php ENDPATH**/ ?>