<?php $__env->startSection('title', 'عرض البودكاست'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>عرض البودكاست</h4>
        <div>
            <a href="<?php echo e(route('admin.podcasts.edit', $podcast)); ?>" class="btn btn-warning">تعديل</a>
            <a href="<?php echo e(route('admin.podcasts.index')); ?>" class="btn btn-secondary">عودة للقائمة</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-2"><?php echo e($podcast->title); ?></h5>
                    <p class="text-muted mb-2">
                        <i class="fa-regular fa-calendar"></i>
                        <?php echo e(optional($podcast->published_at)->format('Y-m-d')); ?>

                        <span class="mx-2">|</span>
                        <span class="badge bg-<?php echo e($podcast->is_published ? 'success' : 'secondary'); ?>"><?php echo e($podcast->is_published ? 'منشور' : 'مسودة'); ?></span>
                    </p>

                    <?php if($podcast->content): ?>
                        <div class="mb-3">
                            <?php echo $podcast->content; ?>

                        </div>
                    <?php endif; ?>

                    <?php if($podcast->audio_path): ?>
                        <audio controls class="w-100">
                            <source src="<?php echo e(asset('storage/' . $podcast->audio_path)); ?>" type="audio/mpeg">
                            متصفحك لا يدعم تشغيل الصوت.
                        </audio>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">الغلاف</div>
                <div class="card-body">
                    <?php if($podcast->cover_image_path): ?>
                        <img src="<?php echo e(asset('storage/' . $podcast->cover_image_path)); ?>" class="img-fluid rounded" alt="cover">
                    <?php else: ?>
                        <div class="text-muted">لا يوجد غلاف</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card">
                <div class="card-header">روابط المنصات</div>
                <div class="card-body">
                    <ul class="list-unstyled m-0">
                        <?php if($podcast->spotify_url): ?>
                            <li class="mb-2"><i class="fa-brands fa-spotify text-success"></i> <a href="<?php echo e($podcast->spotify_url); ?>" target="_blank">Spotify</a></li>
                        <?php endif; ?>
                        <?php if($podcast->apple_podcasts_url): ?>
                            <li class="mb-2"><i class="fa-brands fa-apple text-dark"></i> <a href="<?php echo e($podcast->apple_podcasts_url); ?>" target="_blank">Apple Podcasts</a></li>
                        <?php endif; ?>
                        <?php if($podcast->soundcloud_url): ?>
                            <li class="mb-2"><i class="fa-brands fa-soundcloud text-warning"></i> <a href="<?php echo e($podcast->soundcloud_url); ?>" target="_blank">SoundCloud</a></li>
                        <?php endif; ?>
                        <?php if($podcast->youtube_url): ?>
                            <li class="mb-2"><i class="fa-brands fa-youtube text-danger"></i> <a href="<?php echo e($podcast->youtube_url); ?>" target="_blank">YouTube</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/admin/podcasts/show.blade.php ENDPATH**/ ?>