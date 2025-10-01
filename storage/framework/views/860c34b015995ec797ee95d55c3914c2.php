<?php $__env->startSection('title', $title ?? (app()->isLocale('ar') ? 'بودكاست محكمة' : 'Court Podcast')); ?>
<?php $__env->startSection('meta'); ?>
    <meta name="description" content="<?php echo e($description ?? ''); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
.podcast-card{box-shadow:0 2px 10px rgba(0,0,0,.08);border-radius:12px;overflow:hidden;transition:transform .2s;background:#fff}
.podcast-card:hover{transform:translateY(-4px)}
.podcast-cover{width:100%;height:180px;object-fit:cover;background:#f7f7f7}
.podcast-body{padding:16px}
.podcast-title{font-weight:700;font-size:1.05rem;color:#222;margin-bottom:8px}
.podcast-meta{color:#777;font-size:.85rem}
.search-input{border-radius:10px}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 m-0"><?php echo e(app()->isLocale('ar') ? 'بودكاست محكمة' : 'Court Podcast'); ?></h1>
        <form method="get" class="d-flex" style="gap:8px;">
            <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="<?php echo e(app()->isLocale('ar') ? 'ابحث في الحلقات...' : 'Search episodes...'); ?>" class="form-control search-input">
            <button class="btn btn-primary"><?php echo e(app()->isLocale('ar') ? 'بحث' : 'Search'); ?></button>
        </form>
    </div>

    <?php if($podcasts->count() === 0): ?>
        <div class="alert alert-info"><?php echo e(app()->isLocale('ar') ? 'لا توجد حلقات حالياً.' : 'No episodes yet.'); ?></div>
    <?php else: ?>
        <div class="row g-4">
            <?php $__currentLoopData = $podcasts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="<?php echo e(route('frontend.podcasts.show', [app()->getLocale(), $item->id])); ?>" class="text-decoration-none">
                        <div class="podcast-card">
                            <img class="podcast-cover" src="<?php echo e($item->cover_image_path ? asset('storage/'.$item->cover_image_path) : 'https://via.placeholder.com/600x360?text=Podcast'); ?>" alt="<?php echo e($item->title); ?>">
                            <div class="podcast-body">
                                <div class="podcast-title"><?php echo e($item->title); ?></div>
                                <div class="podcast-meta">
                                    <i class="fa-solid fa-calendar"></i>
                                    <?php echo e(optional($item->published_at)->format('Y-m-d') ?? ''); ?>

                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="mt-4">
            <?php echo e($podcasts->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/frontend/podcasts/index.blade.php ENDPATH**/ ?>