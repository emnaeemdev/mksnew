

<?php $__env->startSection('title', $post->title . 'MKSEGYPT'); ?>
<?php $__env->startSection('description', Str::limit(strip_tags($post->content), 160)); ?>

<?php $__env->startPush('meta'); ?>
    <!-- Meta Tags -->
    <meta name="keywords" content="<?php echo e(optional($post->category)->name ? optional($post->category)->name . ', MK Snow' : 'MK Snow'); ?>">
    <meta name="author" content="MK Snow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo e($post->title); ?>">
    <meta property="og:description" content="<?php echo e(Str::limit(strip_tags($post->content), 160)); ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    
    <?php if($post->featured_image): ?>
    <meta property="og:image" content="<?php echo e(asset('storage/' . $post->featured_image)); ?>">
    <?php endif; ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    
    <!-- Page Header -->



                      <section class="page-header">
    <div class="container <?php echo e(app()->getLocale() == 'ar' ? 'text-end' : 'text-start'); ?>">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb <?php echo e(app()->getLocale() == 'ar' ?  : ''); ?>" dir="<?php echo e(app()->getLocale() == 'ar' ? 'rtl' : 'ltr'); ?>">
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
                <li class="breadcrumb-item">
                    <a href="<?php echo e(route('posts.index', ['category' => $post->category->name_en ?: $post->category->slug])); ?>">
                        <?php echo e($post->category->name); ?>

                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <?php echo e(Str::limit($post->title, 90)); ?>

                </li>
            </ol>
        </nav>
    </div>
</section>
    <!-- Post Hero Section (only if background image exists) -->
    <?php if($post->background_image): ?>
    <div class="post-hero" style="background-image: url('<?php echo e(asset('storage/' . $post->background_image)); ?>');">
        <div class="post-hero-overlay"></div>
        <div class="container">
            <div class="post-hero-content">
                <h1 class="post-hero-title"><?php echo e($post->title); ?></h1>
                <div class="post-hero-meta">
                    <a href="<?php echo e(route('posts.index', ['category' => $post->category->name_en ?: $post->category->slug])); ?>" class="post-category me-3">
                        <i class="fas fa-tag me-1"></i>
                        <?php echo e($post->category->name); ?>

                    </a>
                    <span class="me-3">
                        <i class="fas fa-calendar me-1"></i>
                        <?php echo e($post->published_at ? $post->published_at->format('Y-m-d') : $post->created_at->format('Y-m-d')); ?>

                    </span>
                    <span>
                        <i class="fas fa-eye me-1"></i>
                        <?php echo e($post->views_count); ?> <?php echo e(app()->getLocale() == 'ar' ? 'مشاهدة' : 'views'); ?>

                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                
                <!-- Post Content -->
                <article class="post-container">
                    
                    <!-- Post Title with Border (when no background image) -->
                    <?php if(!$post->background_image): ?>
                    <div class="post-title-with-border">
                        <h1 class="post-title"><?php echo e($post->title); ?></h1>
                        <div class="post-meta">
                            <div class="post-meta-item">
                                <a href="<?php echo e(route('posts.index', ['category' => $post->category->name_en ?: $post->category->slug])); ?>" class="post-category">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo e($post->category->name); ?>

                                </a>
                            </div>
                            <div class="post-meta-item">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo e($post->published_at ? $post->published_at->format('Y-m-d') : $post->created_at->format('Y-m-d')); ?>

                            </div>
                            <div class="post-meta-item">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo e($post->views_count); ?> <?php echo e(app()->getLocale() == 'ar' ? 'مشاهدة' : 'views'); ?>

                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    
                    <!-- Post Content -->
                        <div class="post-content">
                            <?php echo $post->content; ?>

                        </div>

                    
                    <!-- Download Section -->
                    <?php
                        $currentLocale = app()->getLocale();
                        $languageFiles = $post->files->where('language', $currentLocale);
                    ?>
                    <?php if($languageFiles && $languageFiles->count() > 0): ?>
                    <div class="download-section">
                        <div class="download-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <h4 class="mb-3"><?php echo e($currentLocale == 'ar' ? 'الملفات المرفقة' : 'Attached Files'); ?></h4>

                        <div class="files-list">
                            <?php $__currentLoopData = $languageFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="file-item mb-3 p-3 border rounded">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1"><?php echo e($file->display_name ?: $file->original_name); ?></h6>
                                        <small class="text-muted">
                                            <?php echo e($file->original_name); ?> • <?php echo e($file->formattedSize); ?>

                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="<?php echo e($file->fileUrl); ?>" 
                                           class="btn btn-primary btn-sm" download>
                                            <i class="fas fa-download me-1"></i>
                                            <?php echo e($currentLocale == 'ar' ? 'تحميل' : 'Download'); ?>

                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Post Actions -->
                    <div class="post-actions">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">
                                    <i class="fas fa-share-alt me-2"></i>
                                    <?php echo e(app()->getLocale() == 'ar' ? 'شارك هذا الموضوع' : 'Share this topic'); ?>

                                </h6>
                                <div class="share-buttons">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode(url()->current())); ?>" 
                                       target="_blank" class="share-btn share-facebook">
                                        <i class="fab fa-facebook-f me-1"></i>
                                        Facebook
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo e(urlencode(url()->current())); ?>&text=<?php echo e(urlencode($post->title)); ?>" 
                                       target="_blank" class="share-btn share-twitter">
                                        <i class="fab fa-twitter me-1"></i>
                                        Twitter
                                    </a>
                                    <a href="https://wa.me/?text=<?php echo e(urlencode($post->title . ' ' . url()->current())); ?>" 
                                       target="_blank" class="share-btn share-whatsapp">
                                        <i class="fab fa-whatsapp me-1"></i>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="mb-3">
                            
                                   
                                </h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="<?php echo e(route('posts.index', ['category' => $post->category->name_en ?: $post->category->slug])); ?>"
                   class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-list me-1"></i>
                                        <?php echo e(app()->getLocale() == 'ar' ? ($post->category ? 'المزيد من ' . $post->category->name : 'كل المقالات') : ($post->category ? 'More from ' . $post->category->name : 'All posts')); ?>

                                    </a>
                                    <a href="<?php echo e(route('home')); ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-home me-1"></i>
                                        <?php echo e(app()->getLocale() == 'ar' ? 'الرئيسية' : 'Home'); ?>

                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
            
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <!-- Other Reports -->
                    <?php if($otherReportsPosts->count() > 0): ?>
                    <div class="related-posts">
                    <h4 class="mb-4">
                        <i class="fas fa-file-alt me-2"></i>
                        <?php echo e(app()->getLocale() == 'ar' ? 'التقارير الأخرى' : 'Other Reports'); ?>

                    </h4>
                    
                    <?php $__currentLoopData = $otherReportsPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $otherPost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(($otherPost->category && $otherPost->category->name) ? route('content.show', [app()->getLocale(), $otherPost->category->name_en ?: $otherPost->category->slug, $otherPost->id]) : route('posts.index')); ?>" 
                       class="related-post-card">
                        <img class="related-thumb" src="<?php echo e($otherPost->featured_image ? asset('storage/' . $otherPost->featured_image) : 'https://via.placeholder.com/120x80?text=No+Image'); ?>" alt="<?php echo e($otherPost->title); ?>">
                        <div class="related-post-body">
                            <div class="related-post-title"><?php echo e(Str::limit($otherPost->title, 60)); ?></div>
                            <div class="related-post-meta">
                                <i class="fas fa-tag me-1"></i>
                                <?php echo e($otherPost->category->name); ?>

                                <span class="float-end">
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo e($otherPost->views_count); ?>

                                </span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                    <?php endif; ?>
                    
                    <!-- Related Posts -->
                    <?php if($relatedPosts->count() > 0): ?>
                    <div class="related-posts mt-4">
                    <h4 class="mb-4">
                        <i class="fas fa-newspaper me-2"></i>
                        <?php echo e(app()->getLocale() == 'ar' ? 'مواضيع ذات صلة' : 'Related Topics'); ?>

                    </h4>
                    
                    <?php $__currentLoopData = $relatedPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedPost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(($relatedPost->category && $relatedPost->category->name) ? route('content.show', [app()->getLocale(), $relatedPost->category->name_en ?: $relatedPost->category->slug, $relatedPost->id]) : route('posts.index')); ?>" class="related-post-card">
                        <img class="related-thumb" src="<?php echo e($relatedPost->featured_image ? asset('storage/' . $relatedPost->featured_image) : 'https://via.placeholder.com/120x80?text=No+Image'); ?>" alt="<?php echo e($relatedPost->title); ?>">
                        <div class="related-post-body">
                            <div class="related-post-title"><?php echo e(Str::limit($relatedPost->title, 60)); ?></div>
                            <div class="related-post-meta">
                                <i class="fas fa-tag me-1"></i>
                                <?php echo e($relatedPost->category->name); ?>

                                <span class="float-end">
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo e($relatedPost->views_count); ?>

                                </span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                    <div class="text-center mt-3">
                        <a href="<?php echo e(route('posts.index', ['category' => $post->category->slug])); ?>" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>
                            <?php echo e(app()->getLocale() == 'ar' ? 'عرض المزيد' : 'View More'); ?>

                        </a>
                    </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    

    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Track page view time for analytics
        let startTime = Date.now();
        
        window.addEventListener('beforeunload', function() {
            let timeSpent = Math.round((Date.now() - startTime) / 1000);
            
            // Send analytics data (you can implement this based on your needs)
            if (timeSpent > 5) { // Only track if user spent more than 5 seconds
                console.log('Time spent on page:', timeSpent, 'seconds');
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/frontend/posts/show.blade.php ENDPATH**/ ?>