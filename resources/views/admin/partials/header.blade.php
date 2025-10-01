<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="btn btn-outline-light sidebar-toggle me-3" type="button">
            <i class="fas fa-bars"></i>
        </button>
        
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-snowflake me-2"></i>
            {{ config('app.name', 'MK Snow') }}
        </a>
        
        <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user me-1"></i>
                    {{ Auth::user()->name ?? 'المدير' }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                            <i class="fas fa-user-edit me-2"></i>
                            الملف الشخصي
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.settings') }}">
                            <i class="fas fa-cog me-2"></i>
                            الإعدادات
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('home') }}" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>
                            عرض الموقع
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                تسجيل الخروج
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>