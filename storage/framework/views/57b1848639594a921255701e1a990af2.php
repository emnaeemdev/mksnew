

<?php $__env->startSection('title', (isset($category) ? $category->name : 'جميع المواضيع') . ' - MK Snow'); ?>

<?php $__env->startSection('content'); ?>
    
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">

            
 <nav aria-label="breadcrumb">
    <ol class="breadcrumb" dir="<?php echo e(app()->getLocale() == 'ar' ? 'rtl' : 'ltr'); ?>">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('home')); ?>">
                <?php if(app()->getLocale() == 'ar'): ?>
                    <?php echo e('الرئيسية'); ?>

                    <i class="fas fa-home ms-1"></i>
                <?php else: ?>
                    <i class="fas fa-home me-1"></i>
                    <?php echo e('Home'); ?>

                <?php endif; ?>
            </a>
        </li>
        <li class="breadcrumb-item active"><?php echo e(isset($category) ? $category->name : 'جميع المواضيع'); ?></li>
    </ol>
</nav>
        </div>
    </section>
    <br>
    <div class="container">
            <!-- Filter Section
            <div class="filter-section">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>
                            <?php echo e(app()->getLocale() == 'ar' ? 'تصفية النتائج' : 'Filter Results'); ?>

                        </h5>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="<?php echo e(route('posts.index', isset($category) ? ['category' => $category->name_en ?: $category->slug] : [])); ?>">
                            <div class="input-group">
                                <input type="text" 
                                    class="form-control" 
                                    name="search" 
                                    value="<?php echo e(request('search')); ?>" 
                                    placeholder="<?php echo e(app()->getLocale() == 'ar' ? 'البحث في المواضيع...' : 'Search topics...'); ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if(request('search')): ?>
                <div class="mt-3">
                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-search me-2"></i>
                            <?php echo e(app()->getLocale() == 'ar' ? 'نتائج البحث عن:' : 'Search results for:'); ?> 
                            <strong>"<?php echo e(request('search')); ?>"</strong>
                        </span>
                        <a href="<?php echo e(route('posts.index', isset($category) ? ['category' => $category->name_en ?: $category->slug] : [])); ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            <?php echo e(app()->getLocale() == 'ar' ? 'إلغاء البحث' : 'Clear Search'); ?>

                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div> -->
        
        <!-- Posts Grid -->
    <?php if($posts->count() > 0): ?>
    <div class="row">
        <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="post-card">
                <a href="<?php echo e(route('content.show', [app()->getLocale(), $post->category->name_en ?: $post->category->slug, $post->id])); ?>" class="text-decoration-none">
                    <?php if($post->featured_image): ?>
                    <img src="<?php echo e(asset('storage/' . $post->featured_image)); ?>" class="post-image post-image-hover" alt="<?php echo e($post->title); ?>">
                    <?php else: ?>
                    <img src="<?php echo e(asset('images/placeholder.jpg')); ?>" class="post-image post-image-hover" alt="<?php echo e($post->title); ?>">
                    <?php endif; ?>
                    <div class="post-title-overlay">
                        <?php echo e($post->title); ?>

                    </div>
                </a>

                <div class="card-body">
                    <h6 class="post-title-arcive text-center">
                        <a href="<?php echo e(route('content.show', [app()->getLocale(), $post->category->name_en ?: $post->category->slug, $post->id])); ?>" class="text-decoration-none text-dark">
                            <?php echo e($post->title); ?>

                        </a>
                    </h6>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>


        
        <!-- Pagination -->
        <?php if($posts->hasPages()): ?>
        <div class="d-flex justify-content-center">
            <?php echo e($posts->appends(request()->query())->links()); ?>

        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <!-- No Posts Found -->
        <div class="no-posts">
            <i class="fas fa-search"></i>
            <h4><?php echo e(app()->getLocale() == 'ar' ? 'لا توجد مواضيع' : 'No Topics Found'); ?></h4>
            <p class="text-muted">
                <?php if(request('search')): ?>
                    <?php echo e(app()->getLocale() == 'ar' ? 'لم يتم العثور على مواضيع تطابق بحثك.' : 'No topics found matching your search.'); ?>

                <?php else: ?>
                    <?php echo e(app()->getLocale() == 'ar' ? 'لا توجد مواضيع في هذا القسم حالياً.' : 'No topics available in this category at the moment.'); ?>

                <?php endif; ?>
            </p>
            
            <?php if(request('search')): ?>
            <a href="<?php echo e(route('posts.index', isset($category) ? ['category' => $category->name_en ?: $category->slug] : [])); ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>
                <?php echo e(app()->getLocale() == 'ar' ? 'عرض جميع المواضيع' : 'View All Topics'); ?>

            </a>
            <?php else: ?>
            <a href="<?php echo e(route('home')); ?>" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>
                <?php echo e(app()->getLocale() == 'ar' ? 'العودة للرئيسية' : 'Back to Home'); ?>

            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        

    </div>
    
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/frontend/posts/index.blade.php ENDPATH**/ ?>