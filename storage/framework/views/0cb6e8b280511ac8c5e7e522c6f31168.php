<?php $__env->startSection('title', $nashra->title_ar); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row">
        <!-- المحتوى الرئيسي -->
        <div class="col-lg-8">
            <!-- معلومات النشرة -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <!-- العنوان والتاريخ -->
                    <div class="mb-4">
                        <h1 class="display-5 text-primary mb-2"><?php echo e($nashra->title_ar); ?></h1>
                        <?php if($nashra->subtitle_ar): ?>
                            <h2 class="h4 text-muted mb-3"><?php echo e($nashra->subtitle_ar); ?></h2>
                        <?php endif; ?>
                        
                        <div class="d-flex flex-wrap align-items-center text-muted mb-3">
                            <span class="me-3">
                                <i class="fas fa-calendar"></i>
                                تاريخ النشر: <?php echo e($nashra->formatted_published_date); ?>

                            </span>
                            <span class="me-3">
                                <i class="fas fa-clock"></i>
                                آخر تحديث: <?php echo e($nashra->updated_at->diffForHumans()); ?>

                            </span>
                        </div>
                        
                        <!-- الشارات -->
                        <div class="mb-3">
                            <?php if($nashra->pdf_file): ?>
                                <span class="badge bg-danger me-2">
                                    <i class="fas fa-file-pdf"></i> ملف PDF متاح
                                </span>
                            <?php endif; ?>
                            <?php if($nashra->google_drive_url): ?>
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-table"></i> جدول Excel متاح
                                </span>
                            <?php endif; ?>
                            <?php if($nashra->featured_image): ?>
                                <span class="badge bg-info">
                                    <i class="fas fa-image"></i> يحتوي على صور
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- الصورة المميزة -->
                    <?php if($nashra->featured_image): ?>
                        <div class="text-center mb-4">
                            <img src="<?php echo e($nashra->featured_image_url); ?>" 
                                 alt="<?php echo e($nashra->title_ar); ?>" 
                                 class="img-fluid rounded shadow" 
                                 style="max-height: 400px;">
                        </div>
                    <?php endif; ?>
                    
                    <!-- المحتوى -->
                    <?php if($nashra->content_ar): ?>
                        <div class="content-section mb-4">
                            <h3 class="h5 text-primary mb-3">
                                <i class="fas fa-align-left"></i> المحتوى
                            </h3>
                            <div class="content-text">
                                <?php echo nl2br(e($nashra->content_ar)); ?>

                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Google Sheets -->
            <?php if($nashra->google_drive_url): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-table"></i> بيانات Excel
                            </h3>
                            <div>
                                <button type="button" class="btn btn-light btn-sm me-2" id="refresh-data-btn">
                                    <i class="fas fa-sync"></i> تحديث البيانات
                                </button>
                                <a href="<?php echo e($nashra->google_drive_url); ?>" 
                                   target="_blank" 
                                   class="btn btn-light btn-sm">
                                    <i class="fas fa-external-link-alt"></i> فتح في Google Sheets
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div id="sheets-data-container" class="w-100">
                            <div class="text-center py-5">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">جاري التحميل...</span>
                                </div>
                                <p class="mt-3 text-muted">جاري تحميل البيانات من Google Sheets...</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- أزرار المشاركة -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-share-alt"></i> مشاركة النشرة
                    </h5>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" 
                                class="btn btn-outline-primary" 
                                onclick="shareOnFacebook()">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </button>
                        <button type="button" 
                                class="btn btn-outline-info" 
                                onclick="shareOnTwitter()">
                            <i class="fab fa-twitter"></i> Twitter
                        </button>
                        <button type="button" 
                                class="btn btn-outline-success" 
                                onclick="shareOnWhatsApp()">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </button>
                        <button type="button" 
                                class="btn btn-outline-secondary" 
                                onclick="copyLink()">
                            <i class="fas fa-link"></i> نسخ الرابط
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- الشريط الجانبي -->
        <div class="col-lg-4">
            <!-- أزرار التحميل -->
            <?php if($nashra->pdf_file || $nashra->google_drive_url): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-download"></i> التحميلات
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if($nashra->pdf_file): ?>
                            <div class="d-grid gap-2 mb-3">
                                <a href="<?php echo e(route('frontend.nashras.download', ['locale' => app()->getLocale(), 'nashra' => $nashra->id])); ?>" 
                                   class="btn btn-danger">
                                    <i class="fas fa-file-pdf"></i> تحميل ملف PDF
                                </a>
                                <a href="<?php echo e($nashra->pdf_file_url); ?>" 
                                   target="_blank" 
                                   class="btn btn-outline-danger">
                                    <i class="fas fa-eye"></i> عرض ملف PDF
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($nashra->google_drive_url): ?>
                            <div class="d-grid gap-2">
                                <a href="<?php echo e($nashra->google_drive_url); ?>" 
                                   target="_blank" 
                                   class="btn btn-success">
                                    <i class="fas fa-table"></i> فتح جدول Excel
                                </a>
                                <button type="button" 
                                        class="btn btn-outline-success" 
                                        id="export-excel-btn">
                                    <i class="fas fa-file-excel"></i> تصدير كـ Excel
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- معلومات إضافية -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> معلومات إضافية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6"><strong>رقم النشرة:</strong></div>
                        <div class="col-6">#<?php echo e($nashra->id); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>تاريخ الإنشاء:</strong></div>
                        <div class="col-6"><?php echo e($nashra->created_at->format('Y-m-d')); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>آخر تحديث:</strong></div>
                        <div class="col-6"><?php echo e($nashra->updated_at->format('Y-m-d')); ?></div>
                    </div>
                    <?php if($nashra->google_sheet_id): ?>
                        <div class="row">
                            <div class="col-6"><strong>معرف الجدول:</strong></div>
                            <div class="col-6"><small><code><?php echo e(Str::limit($nashra->google_sheet_id, 15)); ?></code></small></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- النشرات ذات الصلة -->
            <?php if($relatedNashras->count() > 0): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-newspaper"></i> نشرات ذات صلة
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php $__currentLoopData = $relatedNashras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="d-flex mb-3 <?php echo e(!$loop->last ? 'border-bottom pb-3' : ''); ?>">
                                <?php if($related->featured_image): ?>
                                    <img src="<?php echo e($related->featured_image_url); ?>" 
                                         alt="<?php echo e($related->title_ar); ?>" 
                                         class="rounded me-3" 
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-file-alt text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="<?php echo e(route('frontend.nashras.show', ['locale' => app()->getLocale(), 'nashra' => $related->id])); ?>" 
                                           class="text-decoration-none">
                                            <?php echo e(Str::limit($related->title_ar, 50)); ?>

                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <?php echo e($related->formatted_published_date); ?>

                                    </small>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- العودة للقائمة -->
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <a href="<?php echo e(route('frontend.nashras.index', ['locale' => app()->getLocale()])); ?>" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-arrow-right"></i> العودة لقائمة النشرات
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.content-text {
    font-size: 1.1rem;
    line-height: 1.8;
    text-align: justify;
}

.content-section {
    border-right: 4px solid #007bff;
    padding-right: 20px;
}

.share-buttons .btn {
    min-width: 120px;
}

@media (max-width: 768px) {
    .content-section {
        border-right: none;
        border-top: 4px solid #007bff;
        padding-right: 0;
        padding-top: 20px;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // تحميل بيانات Google Sheets
    function loadSheetsData() {
        const container = $('#sheets-data-container');
        const refreshBtn = $('#refresh-data-btn');
        const loadBtn = $('#load-data-btn');
        
        // تعطيل الأزرار وإظهار التحميل
        refreshBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...');
        loadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري التحميل...');
        
        container.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <p class="mt-3 text-muted">جاري جلب البيانات من Google Sheets...</p>
            </div>
        `);
        
        $.ajax({
            url: '<?php echo e(route("frontend.nashras.api.sheets", ['locale' => app()->getLocale(), 'nashra' => $nashra->id])); ?>',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    displaySheetsData(response.data);
                } else {
                    showError(response.message || 'لم يتم العثور على بيانات');
                }
            },
            error: function(xhr) {
                let message = 'حدث خطأ في جلب البيانات';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showError(message);
            },
            complete: function() {
                refreshBtn.prop('disabled', false).html('<i class="fas fa-sync"></i> تحديث البيانات');
                loadBtn.prop('disabled', false).html('<i class="fas fa-download"></i> تحميل البيانات');
            }
        });
    }
    
    function displaySheetsData(data) {
        const container = $('#sheets-data-container');
        let html = '';
        
        if (Object.keys(data).length > 0) {
            // شريط التنقل الأفقي
            html += '<div class="p-3"><nav><div class="nav nav-tabs" id="nav-tab" role="tablist">';
            
            Object.keys(data).forEach((sheetName, index) => {
                const sheetData = data[sheetName];
                html += `
                    <button class="nav-link ${index === 0 ? 'active' : ''}" 
                            id="nav-${index}-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#nav-${index}" 
                            type="button" 
                            role="tab">
                        <i class="fas fa-table me-2"></i>
                        ${sheetName} 
                        <span class="badge bg-primary ms-2">${sheetData.length} صف</span>
                    </button>`;
            });
            
            html += '</div></nav>';
            
            // محتوى التبويبات
            html += '<div class="tab-content" id="nav-tabContent">';
            
            Object.keys(data).forEach((sheetName, index) => {
                const sheetData = data[sheetName];
                html += `
                    <div class="tab-pane fade ${index === 0 ? 'show active' : ''}" 
                         id="nav-${index}" 
                         role="tabpanel">
                        <div class="mt-3">
                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-sm table-bordered table-striped table-hover mb-0">`;
                
                // عرض البيانات مع فلترة الصفوف الفارغة
                sheetData.forEach((row, rowIndex) => {
                    if (rowIndex === 0) {
                        // عرض رأس الجدول دائماً
                        html += `<thead class="table-dark"><tr>`;
                        if (row) {
                            row.forEach(cell => {
                                html += `<th>${cell || ''}</th>`;
                            });
                        }
                        html += '</tr></thead><tbody>';
                    } else {
                        // فلترة الصفوف الفارغة والنقط
                        if (row && Array.isArray(row)) {
                            // فحص إذا كان الصف يحتوي على بيانات حقيقية
                            const hasRealData = row.some(cell => {
                                if (!cell) return false;
                                const cellStr = String(cell).trim();
                                // تجاهل الخلايا الفارغة أو التي تحتوي على نقط فقط أو مسافات
                                return cellStr !== '' && cellStr !== '.' && !cellStr.match(/^[.\s]+$/);
                            });
                            
                            if (hasRealData) {
                                html += '<tr>';
                                row.forEach(cell => {
                                    html += `<td>${cell || ''}</td>`;
                                });
                                html += '</tr>';
                            }
                        } else if (typeof row === 'string') {
                            // التعامل مع الصفوف النصية
                            const rowStr = row.trim();
                            if (rowStr !== '' && rowStr !== '.' && !rowStr.match(/^[.\s]+$/)) {
                                html += `<tr><td colspan="8">${row}</td></tr>`;
                            }
                        }
                    }
                });
                
                html += `
                                </tbody></table>
                            </div>
                            <div class="mt-3 text-center">
                                <button class="btn btn-sm btn-outline-success" 
                                        onclick="exportSheetToExcel('${sheetName}', ${index})">
                                    <i class="fas fa-file-excel"></i> تصدير هذا الجدول
                                </button>
                            </div>
                        </div>
                    </div>`;
            });
            
            html += '</div>';
            
            // إضافة زر تصدير جميع الجداول
            html += `
                <div class="text-center mt-3">
                    <button class="btn btn-success" onclick="exportAllSheetsToExcel()">
                        <i class="fas fa-file-excel"></i> تصدير جميع الجداول
                    </button>
                </div></div>`;
        } else {
            html = '<div class="alert alert-warning m-3"><i class="fas fa-exclamation-triangle"></i> لا توجد بيانات في الجدول</div>';
        }
        
        container.html(html);
    }
    
    function showError(message) {
        $('#sheets-data-container').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> ${message}
            </div>
        `);
    }
    
    // تحميل البيانات تلقائياً عند فتح الصفحة
    <?php if($nashra->google_drive_url): ?>
        loadSheetsData();
    <?php endif; ?>
    
    // ربط الأحداث
    $(document).on('click', '#refresh-data-btn, #load-data-btn', loadSheetsData);
    $(document).on('click', '#export-excel-btn', loadSheetsData);
});

// وظائف المشاركة
function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?php echo e($nashra->title_ar); ?>');
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?php echo e($nashra->title_ar); ?>');
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
}

function shareOnWhatsApp() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?php echo e($nashra->title_ar); ?>');
    window.open(`https://wa.me/?text=${title}%20${url}`, '_blank');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        // إظهار رسالة نجاح
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check"></i> تم نسخ الرابط بنجاح!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // إزالة العنصر بعد إخفاؤه
        toast.addEventListener('hidden.bs.toast', function() {
            document.body.removeChild(toast);
        });
    });
}

// وظائف التصدير
function exportSheetToExcel(sheetName, index) {
    const table = document.querySelector(`#collapse${index} table`);
    if (table) {
        const wb = XLSX.utils.table_to_book(table, {sheet: sheetName});
        XLSX.writeFile(wb, `${sheetName}.xlsx`);
    }
}

function exportAllSheetsToExcel() {
    const tables = document.querySelectorAll('#sheetsAccordion table');
    if (tables.length > 0) {
        const wb = XLSX.utils.book_new();
        
        tables.forEach((table, index) => {
            const sheetName = `Sheet${index + 1}`;
            const ws = XLSX.utils.table_to_sheet(table);
            XLSX.utils.book_append_sheet(wb, ws, sheetName);
        });
        
        XLSX.writeFile(wb, '<?php echo e($nashra->title_ar); ?>.xlsx');
    }
}
</script>

<!-- مكتبة XLSX للتصدير -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/frontend/nashras/show.blade.php ENDPATH**/ ?>