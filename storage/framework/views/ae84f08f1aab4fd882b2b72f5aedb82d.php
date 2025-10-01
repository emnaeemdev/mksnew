<div class="sidebar bg-dark">
    <div class="sidebar-header p-3">
        <h5 class="text-white mb-0">
            <i class="fas fa-tachometer-alt me-2"></i>
            لوحة التحكم
        </h5>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('admin.dashboard')); ?>">
                    <i class="fas fa-home me-2"></i>
                    الرئيسية
                </a>
            </li>
            <hr>
                        <li class="nav-item mt-3">
                <h6 class="sidebar-heading text-muted px-3 mb-2">
                    المقالات
                </h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.posts.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.posts.index')); ?>">
                    <i class="fas fa-newspaper me-2"></i>
                    المقالات
                    <!-- <?php if(isset($postsCount) && $postsCount > 0): ?>
                        <span class="badge bg-primary ms-auto"><?php echo e($postsCount); ?></span>
                    <?php endif; ?> -->
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.categories.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.categories.index')); ?>">
                    <i class="fas fa-tags me-2"></i>
                    التصنيفات
                    <!-- <?php if(isset($categoriesCount) && $categoriesCount > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo e($categoriesCount); ?></span>
                    <?php endif; ?> -->
                </a>
            </li>
            <hr>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.nashras.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.nashras.index')); ?>">
                    <i class="fas fa-chart-bar me-2"></i>
                    النشرة الشهرية
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.podcasts.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.podcasts.index')); ?>">
                    <i class="fas fa-podcast me-2"></i>
                    البودكاست
                </a>
            </li>
            

            
            <!-- <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.comments.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.comments.index')); ?>">
                    <i class="fas fa-comments me-2"></i>
                    التعليقات
                    <?php if(isset($commentsCount) && $commentsCount > 0): ?>
                        <span class="badge bg-warning ms-auto"><?php echo e($commentsCount); ?></span>
                    <?php endif; ?>
                </a>
            </li> -->
            

                   <hr>
                        <li class="nav-item mt-3">
                <h6 class="sidebar-heading text-muted px-3 mb-2">
                    الوثائق القانونية
                </h6>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.document-sections.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.document-sections.index')); ?>">
                    <i class="fas fa-folder me-2"></i>
                    أقسام الوثائق
                    <!-- <?php if(isset($documentSectionsCount) && $documentSectionsCount > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo e($documentSectionsCount); ?></span>
                    <?php endif; ?> -->
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.document-custom-fields.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.document-custom-fields.index')); ?>">
                    <i class="fas fa-list-alt me-2"></i>
                    حقول الوثائق المخصصة
                    <!-- <?php if(isset($documentCustomFieldsCount) && $documentCustomFieldsCount > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo e($documentCustomFieldsCount); ?></span>
                    <?php endif; ?> -->
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.documents.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.documents.index')); ?>">
                    <i class="fas fa-file-alt me-2"></i>
                    الوثائق
                    <?php if(isset($documentsCount) && $documentsCount > 0): ?>
                        <span class="badge bg-success ms-auto"><?php echo e($documentsCount); ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- أقسام الوثائق النشطة -->
            <?php if(isset($activeSections) && $activeSections->count() > 0): ?>
                <li class="nav-item mt-2">
                    <h6 class="sidebar-heading text-muted px-3 mb-2">
                        <i class="fas fa-folder-open me-1"></i>
                        أقسام الوثائق
                    </h6>
                </li>
                <?php $__currentLoopData = $activeSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item">
                        <div class="d-flex align-items-center px-3">
                            <a class="nav-link flex-grow-1 <?php echo e(request()->get('section_id') == $section->id ? 'active' : ''); ?>" 
                               href="<?php echo e(route('admin.documents.index', ['section_id' => $section->id])); ?>">
                                <i class="fas fa-folder me-2"></i>
                                <?php echo e($section->name); ?>

                                <?php if($section->documents_count > 0): ?>
                                    <span class="badge bg-info ms-auto"><?php echo e($section->documents_count); ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="<?php echo e(route('admin.documents.create', ['section' => $section->id])); ?>" 
                               class="btn btn-sm btn-outline-light ms-2" 
                               title="إضافة وثيقة جديدة في <?php echo e($section->name); ?>">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
            <hr>
                        <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.inquiries.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.inquiries.index')); ?>">
                    <i class="fas fa-envelope me-2"></i>
                    التواصل
                    <?php if(isset($unreadInquiriesCount) && $unreadInquiriesCount > 0): ?>
                        <span class="badge bg-danger ms-auto"><?php echo e($unreadInquiriesCount); ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <hr>
                                    <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.media.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.media.index')); ?>">
                    <i class="fas fa-images me-2"></i>
                     رفع الملفات
                </a>
            </li>
<hr>
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading text-muted px-3 mb-2">
                    الإعدادات
                </h6>
            </li>


                        <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.users.index')); ?>">
                    <i class="fas fa-users me-2"></i>
                    المستخدمين
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.settings.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.settings.index')); ?>">
                    <i class="fas fa-cog me-2"></i>
                    إعدادات الموقع
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo e(route('admin.newsletter-subscriptions.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.newsletter-subscriptions.*') ? 'active' : ''); ?>">
                    <i class="fa fa-envelope-open-text me-2"></i>
                    اشتراكات النشرة
                    <?php
                        $count = \App\Models\NewsletterSubscription::count();
                    ?>
                    <span class="badge bg-secondary ms-auto"><?php echo e($count); ?></span>
                </a>
            </li>
            

        </ul>
    </nav>
    
    <div class="sidebar-footer p-3 mt-auto">
        <div class="text-center">
            <small class="text-muted">
                <i class="fas fa-clock me-1"></i>
                آخر تحديث: <?php echo e(now()->format('H:i')); ?>

            </small>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/admin/partials/sidebar.blade.php ENDPATH**/ ?>