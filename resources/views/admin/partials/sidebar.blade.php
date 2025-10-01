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
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
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
                <a class="nav-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}" href="{{ route('admin.posts.index') }}">
                    <i class="fas fa-newspaper me-2"></i>
                    المقالات
                    <!-- @if(isset($postsCount) && $postsCount > 0)
                        <span class="badge bg-primary ms-auto">{{ $postsCount }}</span>
                    @endif -->
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                    <i class="fas fa-tags me-2"></i>
                    التصنيفات
                    <!-- @if(isset($categoriesCount) && $categoriesCount > 0)
                        <span class="badge bg-secondary ms-auto">{{ $categoriesCount }}</span>
                    @endif -->
                </a>
            </li>
            <hr>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.nashras.*') ? 'active' : '' }}" href="{{ route('admin.nashras.index') }}">
                    <i class="fas fa-chart-bar me-2"></i>
                    النشرة الشهرية
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.podcasts.*') ? 'active' : '' }}" href="{{ route('admin.podcasts.index') }}">
                    <i class="fas fa-podcast me-2"></i>
                    البودكاست
                </a>
            </li>
            

            
            <!-- <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}" href="{{ route('admin.comments.index') }}">
                    <i class="fas fa-comments me-2"></i>
                    التعليقات
                    @if(isset($commentsCount) && $commentsCount > 0)
                        <span class="badge bg-warning ms-auto">{{ $commentsCount }}</span>
                    @endif
                </a>
            </li> -->
            

                   <hr>
                        <li class="nav-item mt-3">
                <h6 class="sidebar-heading text-muted px-3 mb-2">
                    الوثائق القانونية
                </h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.document-sections.*') ? 'active' : '' }}" href="{{ route('admin.document-sections.index') }}">
                    <i class="fas fa-folder me-2"></i>
                    أقسام الوثائق
                    <!-- @if(isset($documentSectionsCount) && $documentSectionsCount > 0)
                        <span class="badge bg-secondary ms-auto">{{ $documentSectionsCount }}</span>
                    @endif -->
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.document-custom-fields.*') ? 'active' : '' }}" href="{{ route('admin.document-custom-fields.index') }}">
                    <i class="fas fa-list-alt me-2"></i>
                    حقول الوثائق المخصصة
                    <!-- @if(isset($documentCustomFieldsCount) && $documentCustomFieldsCount > 0)
                        <span class="badge bg-secondary ms-auto">{{ $documentCustomFieldsCount }}</span>
                    @endif -->
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.documents.*') ? 'active' : '' }}" href="{{ route('admin.documents.index') }}">
                    <i class="fas fa-file-alt me-2"></i>
                    الوثائق
                    @if(isset($documentsCount) && $documentsCount > 0)
                        <span class="badge bg-success ms-auto">{{ $documentsCount }}</span>
                    @endif
                </a>
            </li>
            
            <!-- أقسام الوثائق النشطة -->
            @if(isset($activeSections) && $activeSections->count() > 0)
                <li class="nav-item mt-2">
                    <h6 class="sidebar-heading text-muted px-3 mb-2">
                        <i class="fas fa-folder-open me-1"></i>
                        أقسام الوثائق
                    </h6>
                </li>
                @foreach($activeSections as $section)
                    <li class="nav-item">
                        <div class="d-flex align-items-center px-3">
                            <a class="nav-link flex-grow-1 {{ request()->get('section_id') == $section->id ? 'active' : '' }}" 
                               href="{{ route('admin.documents.index', ['section_id' => $section->id]) }}">
                                <i class="fas fa-folder me-2"></i>
                                {{ $section->name }}
                                @if($section->documents_count > 0)
                                    <span class="badge bg-info ms-auto">{{ $section->documents_count }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.documents.create', ['section' => $section->id]) }}" 
                               class="btn btn-sm btn-outline-light ms-2" 
                               title="إضافة وثيقة جديدة في {{ $section->name }}">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </li>
                @endforeach
            @endif
            <hr>
                        <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.inquiries.*') ? 'active' : '' }}" href="{{ route('admin.inquiries.index') }}">
                    <i class="fas fa-envelope me-2"></i>
                    التواصل
                    @if(isset($unreadInquiriesCount) && $unreadInquiriesCount > 0)
                        <span class="badge bg-danger ms-auto">{{ $unreadInquiriesCount }}</span>
                    @endif
                </a>
            </li>
            <hr>
                                    <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}" href="{{ route('admin.media.index') }}">
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
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-users me-2"></i>
                    المستخدمين
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                    <i class="fas fa-cog me-2"></i>
                    إعدادات الموقع
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('admin.newsletter-subscriptions.index') }}" class="nav-link {{ request()->routeIs('admin.newsletter-subscriptions.*') ? 'active' : '' }}">
                    <i class="fa fa-envelope-open-text me-2"></i>
                    اشتراكات النشرة
                    @php
                        $count = \App\Models\NewsletterSubscription::count();
                    @endphp
                    <span class="badge bg-secondary ms-auto">{{ $count }}</span>
                </a>
            </li>
            

        </ul>
    </nav>
    
    <div class="sidebar-footer p-3 mt-auto">
        <div class="text-center">
            <small class="text-muted">
                <i class="fas fa-clock me-1"></i>
                آخر تحديث: {{ now()->format('H:i') }}
            </small>
        </div>
    </div>
</div>