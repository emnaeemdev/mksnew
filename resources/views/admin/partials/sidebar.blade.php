<div class="sidebar">
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-home me-2"></i>
                    الرئيسية
                </a>
            </li>

            <li class="sidebar-divider" aria-hidden="true"></li>

            <li class="nav-item sidebar-section">
                <span class="sidebar-heading">تقارير أوراق أدلة هل تعلم</span>
            </li>

            @if(isset($activePostCategories) && $activePostCategories->count() > 0)
                @foreach($activePostCategories as $category)
                    @php
                        $currentPost = request()->route('post');
                        $isCategoryActive = request()->routeIs('admin.posts.*') && (
                            (string) request()->get('category') === (string) $category->id
                            || (is_object($currentPost) && (string) $currentPost->category_id === (string) $category->id)
                        );
                    @endphp
                    <li class="nav-item">
                        <div class="sidebar-doc-row">
                            <a class="nav-link {{ $isCategoryActive ? 'active' : '' }}"
                               href="{{ route('admin.posts.index', ['category' => $category->id]) }}">
                                <span class="text-truncate">{{ $category->name_ar }}</span>
                                @if($category->posts_count > 0)
                                    <span class="badge bg-info">{{ $category->posts_count }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.posts.create', ['category' => $category->id]) }}"
                               class="btn btn-sm btn-add-doc"
                               title="إضافة موضوع جديد في {{ $category->name_ar }}">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </li>
                @endforeach
            @else
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}" href="{{ route('admin.posts.index') }}">
                        <i class="fas fa-newspaper me-2"></i>
                        كل المواضيع
                    </a>
                </li>
            @endif
<hr>
            <li class="nav-item">
                <div class="sidebar-doc-row">
                    <a class="nav-link {{ request()->routeIs('admin.nashras.*') ? 'active' : '' }}"
                       href="{{ route('admin.nashras.index') }}">
                        <i class="fas fa-chart-bar me-2"></i>
                        <span class="text-truncate">النشرة الشهرية</span>
                        @if(($nashrasCount ?? 0) > 0)
                            <span class="badge bg-info">{{ $nashrasCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.nashras.create') }}"
                       class="btn btn-sm btn-add-doc"
                       title="إضافة نشرة شهرية جديدة">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            </li>
<hr>
            <li class="nav-item">
                <div class="sidebar-doc-row">
                    <a class="nav-link {{ request()->routeIs('admin.podcasts.*') ? 'active' : '' }}"
                       href="{{ route('admin.podcasts.index') }}">
                        <i class="fas fa-podcast me-2"></i>
                        <span class="text-truncate">البودكاست</span>
                        @if(($podcastsCount ?? 0) > 0)
                            <span class="badge bg-info">{{ $podcastsCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.podcasts.create') }}"
                       class="btn btn-sm btn-add-doc"
                       title="إضافة بودكاست جديد">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            </li>
<hr>


            <li class="nav-item sidebar-section">
                <span class="sidebar-heading">إضافة أو تعديل وثائق</span>
            </li>

            @if(isset($activeSections) && $activeSections->count() > 0)
                @foreach($activeSections as $section)
                    @php
                        $currentDocument = request()->route('document');
                        $isSectionActive = request()->routeIs('admin.documents.*') && (
                            (string) request()->get('section_id') === (string) $section->id
                            || (string) request()->get('section') === (string) $section->id
                            || (is_object($currentDocument) && (string) $currentDocument->section_id === (string) $section->id)
                        );
                    @endphp
                    <li class="nav-item">
                        <div class="sidebar-doc-row">
                            <a class="nav-link {{ $isSectionActive ? 'active' : '' }}"
                               href="{{ route('admin.documents.index', ['section_id' => $section->id]) }}">
                                <span class="text-truncate">{{ $section->name }}</span>
                                @if($section->documents_count > 0)
                                    <span class="badge bg-info">{{ $section->documents_count }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.documents.create', ['section' => $section->id]) }}"
                               class="btn btn-sm btn-add-doc"
                               title="إضافة وثيقة جديدة في {{ $section->name }}">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </li>
                @endforeach
            @endif

            <li class="sidebar-divider" aria-hidden="true"></li>

            <li class="nav-item">
                <a href="{{ route('admin.newsletter-subscriptions.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('admin.newsletter-subscriptions.*') ? 'active' : '' }}">
                    <i class="fa fa-envelope-open-text me-2"></i>
                    اشتراكات النشرة
                    @if(($newsletterSubscriptionsCount ?? 0) > 0)
                        <span class="badge bg-secondary ms-auto">{{ $newsletterSubscriptionsCount }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.inquiries.*') ? 'active' : '' }}" href="{{ route('admin.inquiries.index') }}">
                    <i class="fas fa-envelope me-2"></i>
                    التواصل
                    @if(isset($unreadInquiriesCount) && $unreadInquiriesCount > 0)
                        <span class="badge bg-danger ms-auto">{{ $unreadInquiriesCount }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}" href="{{ route('admin.media.index') }}">
                    <i class="fas fa-images me-2"></i>
                    رفع الملفات
                </a>
            </li>

            <li class="sidebar-divider" aria-hidden="true"></li>

            <li class="nav-item sidebar-section">
                <span class="sidebar-heading">الإعدادات</span>
            </li>

            <li class="nav-item">
                <span class="sidebar-subheading">أقسام الوثائق والفلاتر</span>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.document-sections.*') ? 'active' : '' }}" href="{{ route('admin.document-sections.index') }}">
                    <i class="fas fa-folder me-2"></i>
                    تعديل أقسام الوثائق
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.document-custom-fields.*') ? 'active' : '' }}" href="{{ route('admin.document-custom-fields.index') }}">
                    <i class="fas fa-list-alt me-2"></i>
                    تعديل فلاتر الوثائق
                </a>
            </li>
            
            <hr>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                    <i class="fas fa-tags me-2"></i>
                    أقسام المقالات
                </a>
            </li>

            @if(auth()->user()?->isAdmin())
            <hr>

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
            @endif
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
