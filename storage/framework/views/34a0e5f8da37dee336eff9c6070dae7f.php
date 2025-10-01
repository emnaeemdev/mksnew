<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <?php if(!empty($footerHtml)): ?>
            <div class="mb-4">
                <?php echo $footerHtml; ?>

            </div>
            <hr class="my-4">
        <?php endif; ?>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-snowflake text-primary me-2"></i>
                    <?php echo e($siteName ?? config('app.name', 'MK Snow')); ?>

                </h5>
                <p class="text-muted">
                    موقع متخصص في تقديم أحدث المقالات والأخبار في مختلف المجالات. نسعى لتقديم محتوى عالي الجودة ومفيد لجميع القراء.
                </p>
                <div class="social-links">
                    <a href="#" class="text-white me-3" title="فيسبوك">
                        <i class="fab fa-facebook fa-lg"></i>
                    </a>
                    <a href="#" class="text-white me-3" title="تويتر">
                        <i class="fab fa-twitter fa-lg"></i>
                    </a>
                    <a href="#" class="text-white me-3" title="إنستغرام">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="#" class="text-white me-3" title="يوتيوب">
                        <i class="fab fa-youtube fa-lg"></i>
                    </a>
                    <a href="#" class="text-white" title="لينكد إن">
                        <i class="fab fa-linkedin fa-lg"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">روابط سريعة</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?php echo e(route('home', [app()->getLocale()])); ?>" class="text-muted text-decoration-none">
                            <i class="fas fa-home me-2"></i>الرئيسية
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo e(route('posts.index', [app()->getLocale()])); ?>" class="text-muted text-decoration-none">
                            <i class="fas fa-newspaper me-2"></i>المقالات
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo e(route('about', [app()->getLocale()])); ?>" class="text-muted text-decoration-none">
                            <i class="fas fa-info-circle me-2"></i><?php echo e(__('messages.about')); ?>

                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo e(route('contact', [app()->getLocale()])); ?>" class="text-muted text-decoration-none">
                            <i class="fas fa-envelope me-2"></i><?php echo e(__('messages.contact')); ?>

                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="fw-bold mb-3"><?php echo e(__('messages.newsletter_heading')); ?></h6>
                <p class="text-muted small mb-3">
                    <?php echo e(__('messages.newsletter_description')); ?>

                </p>

                <?php if(session('success')): ?>
                    <div class="alert alert-success py-2 px-3 small" role="alert">
                        <i class="fas fa-check-circle me-1"></i> <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="alert alert-danger py-2 px-3 small" role="alert">
                        <i class="fas fa-exclamation-triangle me-1"></i> <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>

                <form class="newsletter-form" method="POST" action="<?php echo e(route('newsletter.subscribe', [app()->getLocale()])); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-2">
                        <input type="text" class="form-control" name="name" value="<?php echo e(old('name')); ?>" placeholder="<?php echo e(__('messages.newsletter_name')); ?>">
                    </div>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" name="email" value="<?php echo e(old('email')); ?>" placeholder="<?php echo e(__('messages.newsletter_email')); ?>" required>
                        <button class="btn btn-primary" type="submit" aria-label="<?php echo e(__('messages.newsletter_subscribe')); ?>">
                            <i class="fas fa-paper-plane me-1"></i>
                            <span class="d-none d-sm-inline"><?php echo e(__('messages.newsletter_subscribe')); ?></span>
                        </button>
                    </div>
                </form>
                
                <div class="contact-info">
                    <p class="text-muted small mb-1">
                        <i class="fas fa-envelope me-2"></i>
                        info@mksnow.com
                    </p>
                    <p class="text-muted small mb-1">
                        <i class="fas fa-phone me-2"></i>
                        +966 50 123 4567
                    </p>
                    <p class="text-muted small">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        الرياض، المملكة العربية السعودية
                    </p>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted small mb-0">
                    &copy; <?php echo e(date('Y')); ?> <?php echo e($siteName ?? config('app.name', 'MK Snow')); ?>. محتوى الموقع منشور برخصة المشاع الإبداعي

نَسب المُصنَّف 4.0
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <a href="<?php echo e(route('privacy', [app()->getLocale()])); ?>" class="text-muted text-decoration-none small">
                            سياسة الخصوصية
                        </a>
                    </li>
                    <li class="list-inline-item">|</li>
                    <li class="list-inline-item">
                        <a href="<?php echo e(route('terms', [app()->getLocale()])); ?>" class="text-muted text-decoration-none small">
                            شروط الاستخدام
                        </a>
                    </li>
                    <li class="list-inline-item">|</li>
                    <li class="list-inline-item">
                        <a href="<?php echo e(route('sitemap', [app()->getLocale()])); ?>" class="text-muted text-decoration-none small">
                            خريطة الموقع
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<?php echo $__env->yieldContent('scripts'); ?><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/frontend/partials/footer.blade.php ENDPATH**/ ?>