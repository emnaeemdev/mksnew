<?php $__env->startSection('title', 'اشتراكات النشرة البريدية'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">اشتراكات النشرة البريدية</h1>
    <div class="d-flex gap-2 align-items-center">
        <form method="get" action="<?php echo e(route('admin.newsletter-subscriptions.index')); ?>" class="d-flex" role="search">
            <input type="text" name="q" value="<?php echo e($search); ?>" class="form-control me-2" placeholder="بحث بالاسم أو البريد">
            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
        </form>
        <a class="btn btn-success" href="<?php echo e(route('admin.newsletter-subscriptions.export', request()->only('q'))); ?>">
            <i class="fa fa-download me-1"></i> تصدير CSV
        </a>
        <button type="button" id="copyAllEmailsBtn" class="btn btn-secondary">
            <i class="fa fa-copy me-1"></i> نسخ كل الإيميلات (الصفحة)
        </button>
    </div>
</div>

<div class="card">
    <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('admin.newsletter-subscriptions.bulk-delete')); ?>">
        <?php echo csrf_field(); ?>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width:36px"><input type="checkbox" id="selectAll"></th>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>تاريخ الاشتراك</th>
                            <th class="text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subscription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="<?php echo e($subscription->id); ?>" class="row-check"></td>
                                <td><?php echo e($subscription->id); ?></td>
                                <td><?php echo e($subscription->name); ?></td>
                                <td class="email-cell"><?php echo e($subscription->email); ?></td>
                                <td><?php echo e($subscription->created_at->format('Y-m-d H:i')); ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary copy-email-btn" data-email="<?php echo e($subscription->email); ?>" title="نسخ البريد">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                    <form action="<?php echo e(route('admin.newsletter-subscriptions.destroy', $subscription)); ?>" method="POST" onsubmit="return confirm('حذف هذا الاشتراك؟');" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center">لا توجد اشتراكات حتى الآن</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div>
                <button type="submit" class="btn btn-danger" onclick="return confirm('هل تريد حذف العناصر المحددة؟');">
                    <i class="fa fa-trash me-1"></i> حذف جماعي
                </button>
            </div>
            <?php if($subscriptions->hasPages()): ?>
                <div>
                    <?php echo e($subscriptions->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checks = document.querySelectorAll('.row-check');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checks.forEach(ch => ch.checked = selectAll.checked);
            });
        }
        // نسخ بريد واحد
        document.querySelectorAll('.copy-email-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const email = this.dataset.email || '';
                navigator.clipboard.writeText(email).then(() => {
                    this.classList.add('btn-success');
                    setTimeout(() => this.classList.remove('btn-success'), 800);
                });
            });
        });
        // نسخ كل الإيميلات الظاهرة في الصفحة
        const copyAllBtn = document.getElementById('copyAllEmailsBtn');
        if (copyAllBtn) {
            copyAllBtn.addEventListener('click', function() {
                const emails = Array.from(document.querySelectorAll('td.email-cell'))
                    .map(td => td.textContent.trim())
                    .filter(Boolean)
                    .join('\n');
                if (emails) {
                    navigator.clipboard.writeText(emails).then(() => {
                        copyAllBtn.classList.add('btn-success');
                        setTimeout(() => copyAllBtn.classList.remove('btn-success'), 800);
                    });
                }
            });
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/admin/newsletter-subscriptions/index.blade.php ENDPATH**/ ?>