<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>إدارة البودكاست</h4>
        <a href="<?php echo e(route('admin.podcasts.create')); ?>" class="btn btn-primary">إضافة حلقة</a>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="ابحث بعنوان أو محتوى">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="published">
                <option value="">كل الحالات</option>
                <option value="1" <?php if(request('published')==='1'): echo 'selected'; endif; ?>>منشور</option>
                <option value="0" <?php if(request('published')==='0'): echo 'selected'; endif; ?>>غير منشور</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-secondary">تصفية</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العنوان</th>
                    <th>الترتيب</th>
                    <th>تاريخ النشر</th>
                    <th>منشور؟</th>
                    <th>خيارات</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $podcasts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $podcast): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($podcast->id); ?></td>
                        <td><?php echo e($podcast->title); ?></td>
                        <td><?php echo e($podcast->sort_order); ?></td>
                        <td><?php echo e(optional($podcast->published_at)->format('Y-m-d')); ?></td>
                        <td>
                            <span class="badge bg-<?php echo e($podcast->is_published ? 'success' : 'secondary'); ?>"><?php echo e($podcast->is_published ? 'منشور' : 'مسودة'); ?></span>
                        </td>
                        <td>
                            <a href="<?php echo e(route('admin.podcasts.edit', $podcast)); ?>" class="btn btn-sm btn-warning">تعديل</a>
                            <a href="<?php echo e(route('admin.podcasts.show', $podcast)); ?>" class="btn btn-sm btn-info">عرض</a>
                            <form action="<?php echo e(route('admin.podcasts.destroy', $podcast)); ?>" method="post" class="d-inline" onsubmit="return confirm('تأكيد الحذف؟');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="text-center">لا توجد عناصر</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php echo e($podcasts->links()); ?>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/admin/podcasts/index.blade.php ENDPATH**/ ?>