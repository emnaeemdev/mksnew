<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo e(route('home')); ?>">
            <img alt="Logo" src="https://mksegypt.org/landing-assets/images/logo-arabic-header_mks.png" class="logo-dark">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-2">
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('home') ? 'active' : ''); ?>" href="<?php echo e(route('home')); ?>">
                        <i class="fas fa-home me-1"></i>
                        <?php echo e(__('messages.home')); ?>

                    </a>
                </li>
                
                <!-- Post Categories with language-specific show_in_menu option -->
                <?php
                    $currentLang = app()->getLocale();
                    $showInMenuField = $currentLang == 'ar' ? 'show_in_menu_ar' : 'show_in_menu_en';
                    $menuOrderField = $currentLang == 'ar' ? 'menu_order_ar' : 'menu_order_en';
                    
                    $menuCategories = \App\Models\Category::where('is_active', true)
                        ->where($showInMenuField, true)
                        ->orderBy($menuOrderField, 'asc')
                        ->orderBy('menu_order', 'asc')
                        ->get();
                ?>
                
                <?php $__currentLoopData = $menuCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('posts.category') && request()->route('category') == $category->slug ? 'active' : ''); ?>" href="<?php echo e(route('posts.category', [app()->getLocale(), $category->slug])); ?>">
                            
                            <?php echo e($category->name); ?>

                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                <!-- Documents Dropdown with language-specific settings -->
                <?php
                    $docShowInMenuField = $currentLang == 'ar' ? 'show_in_menu_ar' : 'show_in_menu_en';
                    $docMenuOrderField = $currentLang == 'ar' ? 'menu_order_ar' : 'menu_order_en';
                    
                    $documentSections = \App\Models\DocumentSection::where('is_active', true)
                        ->where($docShowInMenuField, true)
                        ->orderBy($docMenuOrderField, 'asc')
                        ->orderBy('menu_order', 'asc')
                        ->get();
                    $hasDocumentDropdown = $documentSections->where('is_dropdown', true)->count() > 0;
                ?>
                
                <?php if($documentSections->count() > 0): ?>
                    <!-- Dropdown sections if any -->
                    <?php
                        $dropdownSections = $documentSections->where('is_dropdown', true);
                    ?>
                    
                    <?php if($dropdownSections->count() > 0): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="documentsDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo e($dropdownSections->first()->dropdown_title ?? 'الأحكام'); ?>

                            </a>
                            <ul class="dropdown-menu" aria-labelledby="documentsDropdown">
                                <?php $__currentLoopData = $dropdownSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo e(route('frontend.documents.section', [app()->getLocale(), $section->slug])); ?>">
                                            <?php echo e(app()->getLocale() === 'ar' ? $section->name : $section->name_en); ?>

                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    
                    <?php $__currentLoopData = $documentSections->where('is_dropdown', false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('frontend.documents.section') && request()->route('section') == $section->slug ? 'active' : ''); ?>" href="<?php echo e(route('frontend.documents.section', [app()->getLocale(), $section->slug])); ?>">
                                <?php echo e(app()->getLocale() === 'ar' ? $section->name : $section->name_en); ?>

                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                    <!-- General documents link if no sections are configured for menu -->
                <?php else: ?>
                    <!-- empty -->
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('about') ? 'active' : ''); ?>" href="<?php echo e(route('about', [app()->getLocale()])); ?>">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo e(__('messages.about')); ?>

                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('contact') ? 'active' : ''); ?>" href="<?php echo e(route('contact', [app()->getLocale()])); ?>">
                        <i class="fas fa-envelope me-1"></i>
                        <?php echo e(__('messages.contact')); ?>

                    </a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <!-- Language Switcher -->
                <div>
                    <?php if(app()->getLocale() == 'ar'): ?>
                        <a href="#" class="btn btn-outline-warning lang-switch" data-lang="en">
                            <i class="fas fa-globe me-1"></i>
                            English
                        </a>
                    <?php else: ?>
                        <a href="#" class="btn btn-outline-warning lang-switch" data-lang="ar">
                            <i class="fas fa-globe me-1"></i>
                            العربية
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Spacer for fixed navbar -->
<!-- <div style="height: 80px;"></div> --><?php /**PATH C:\xampp\htdocs\mksnow\resources\views/frontend/partials/header.blade.php ENDPATH**/ ?>