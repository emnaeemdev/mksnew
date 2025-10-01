

<?php $__env->startSection('title', app()->isLocale('ar') ? (($siteName ?? config('app.name', 'MK Snow')) . ' - الصفحة الرئيسية') : (($siteName ?? config('app.name', 'MK Snow')) . ' - Home')); ?>


<?php $__env->startSection('content'); ?>
<div class="blog-section" style="box-shadow: 0 1px 6px #e9640a73;">
    <div class="layout-container">
        <div class="main-content">
            <?php if(isset($sliderPosts) && $sliderPosts->count() > 0): ?>
                <?php $mainPost = $sliderPosts->first(); ?>

                <!-- الصورة الرئيسية -->
                <a href="<?php echo e(route('content.show', [app()->getLocale(), $mainPost->category->name_en ?: $mainPost->category->slug, $mainPost->id])); ?>">
                     <div class="main-image">
                         <div class="main-blog-title">
                             <img class="main-img" src="<?php echo e($mainPost->background_image ? asset('storage/' . $mainPost->background_image) : ($mainPost->featured_image ? asset('storage/' . $mainPost->featured_image) : 'https://via.placeholder.com/800x400?text=No+Image')); ?>" alt="صورة رئيسية">
                             <div class="overlay-title">
                                <div><span class="btn btn-light btn-sm"><?php echo e(optional($mainPost->category)->name_en ?? (app()->isLocale('ar') ? 'بدون تصنيف' : 'Uncategorized')); ?></span> | <?php echo e($mainPost->title); ?></div>
                             </div>
                         </div>
                     </div>
                 </a>

                <!-- الصور الثلاثة الفرعية -->
                 <div class="small-images-container">
                     <?php $__currentLoopData = $sliderPosts->skip(1)->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                         <div class="small-image">
                            <a href="<?php echo e(route('content.show', [app()->getLocale(), $post->category->name_en ?: $post->category->slug, $post->id])); ?>" class="text-dark text-decoration-none">
                                 <div class="main-blog-title">
                                     <img class="small-img" src="<?php echo e($post->featured_image ? asset('storage/' . $post->featured_image) : ($post->background_image ? asset('storage/' . $post->background_image) : 'https://via.placeholder.com/400x200?text=No+Image')); ?>" alt="صورة فرعية">
                                     <div class="overlay-title">
                                         <div><?php echo e($post->title); ?></div>
                                     </div>
                                 </div>
                             </a>
                         </div>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                 </div>
            <?php endif; ?>
        </div>

        <!-- العمود الأيسر -->
        <div class="sidebar text-white">
            <a href="#" class="text-white text-decoration-none">
                <div class="sidebar-item">
                    <i class="fas fa-window-restore fa-4x mb-4"></i>
                    <h6 class="law-title">مجلة نوافذ</h6>
                </div>
            </a>

            <a href="<?php echo e(route('frontend.podcasts.index', [app()->getLocale()])); ?>" class="text-white text-decoration-none">
                <div class="sidebar-item">
                    <i class="fas fa-podcast fa-4x mb-4"></i>
                    <h6 class="law-title">بودكاست محكمة</h6>
                </div>
            </a>

            <a href="<?php echo e(route('frontend.nashras.index', [app()->getLocale()])); ?>" class="text-white text-decoration-none">
                <div class="sidebar-item">
                    <i class="fas fa-chart-bar fa-4x mb-4"></i>
                    <h6 class="law-title">النشرات الإحصائية</h6>
                </div>
            </a>

            <a href="#" class="text-white text-decoration-none">
                <div class="sidebar-item">
                    <i class="fas fa-lightbulb fa-4x mb-4"></i>
                    <h6 class="law-title">هل تعلم</h6>
                </div>
            </a>
        </div>

    </div>

    <br>
</div>

<br>

<div class="blog-section">
    <div class="container">
        <h2>
            إصدرات أخرى
            <div></div>
        </h2>
        <?php if(isset($releasePosts) && $releasePosts->count() > 0): ?>
            <div class="row">
                <?php $__currentLoopData = $releasePosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-3 mb-4">
                    <a href="<?php echo e(route('content.show', [app()->getLocale(), $post->category->name_en ?: $post->category->slug, $post->id])); ?>">
                             <div class="main-blog-title-t">
                                 <div class="custom-blog">
                                     <div class="custom-img-container mb-3">
                                         <?php if($post->featured_image): ?>
                                             <img src="<?php echo e(asset('storage/' . $post->featured_image)); ?>" class="img-fluid img-thumbnail effect-img" />
                                         <?php endif; ?>
                                     </div>
                                     <div><?php echo e($post->title); ?></div>
                                 </div>
                             </div>
                         </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if(app()->isLocale('ar')): ?>
<div class="fsec-section" style="box-shadow: 0 1px 6px #e9640a73;">
    <br>
    <br>
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-5">
                <a href="#" class="card-link text-decoration-none">
                    <div class="cardhome text-center p-4">
                        <i class="fas fa-balance-scale fa-3x mb-3"></i>
                        <h6 class="card-title">قوانين</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-5">
                <a href="#" class="card-link text-decoration-none">
                    <div class="cardhome text-center p-4">
                        <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                        <h6 class="card-title">قرارات</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-5">
                <a href="#" class="card-link text-decoration-none">
                    <div class="cardhome text-center p-4">
                        <i class="fas fa-gavel fa-3x mb-3"></i>
                        <h6 class="card-title">المحكمة الدستورية</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-5">
                <a href="#" class="card-link text-decoration-none">
                    <div class="cardhome text-center p-4">
                        <i class="fas fa-building fa-3x mb-3"></i>
                        <h6 class="card-title">محكمة النقض</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-5">
                <a href="#" class="card-link text-decoration-none">
                    <div class="cardhome text-center p-4">
                        <i class="fas fa-institution fa-3x mb-3"></i>
                        <h6 class="card-title">المحكمة الادارية العليا</h6>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <br>
    <br>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/frontend/home.blade.php ENDPATH**/ ?>