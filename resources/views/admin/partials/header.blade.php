<nav class="navbar admin-topbar">
    <div class="container-fluid admin-topbar__inner">
        <div class="admin-topbar__start">
            <button class="btn btn-outline-secondary sidebar-toggle" type="button" aria-label="القائمة الجانبية">
                <i class="fas fa-bars"></i>
            </button>

            <a class="navbar-brand mb-0" href="{{ route('admin.dashboard') }}">
                {{ config('app.name', 'MKS Egypt') }}
            </a>
        </div>

        <div class="admin-user-menu" id="adminUserMenuWrap">
            <button
                class="btn btn-link admin-user-menu__toggle text-decoration-none"
                type="button"
                id="adminUserMenu"
                aria-expanded="false"
                aria-haspopup="true"
                aria-controls="adminUserMenuPanel"
            >
                <i class="fas fa-user me-1"></i>
                <span>{{ Auth::user()->name ?? 'المدير' }}</span>
                <i class="fas fa-chevron-down admin-user-menu__caret"></i>
            </button>

            <div class="admin-user-menu__panel" id="adminUserMenuPanel" hidden>
                <a class="admin-user-menu__item" href="{{ route('admin.profile') }}">
                    <i class="fas fa-user-edit me-2"></i>
                    الملف الشخصي
                </a>
                @if(auth()->user()?->isAdmin())
                    <a class="admin-user-menu__item" href="{{ route('admin.settings') }}">
                        <i class="fas fa-cog me-2"></i>
                        الإعدادات
                    </a>
                @endif
                <div class="admin-user-menu__divider"></div>
                <a class="admin-user-menu__item" href="{{ url('/' . app()->getLocale()) }}" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-external-link-alt me-2"></i>
                    عرض الموقع
                </a>
                <div class="admin-user-menu__divider"></div>
                <form method="POST" action="{{ route('admin.logout') }}" id="admin-logout-form" novalidate>
                    @csrf
                    <button type="submit" class="admin-user-menu__item admin-user-menu__item--danger">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
(function () {
    function initAdminUserMenu() {
        var wrap = document.getElementById('adminUserMenuWrap');
        var toggle = document.getElementById('adminUserMenu');
        var panel = document.getElementById('adminUserMenuPanel');
        if (!wrap || !toggle || !panel) return;

        function openMenu() {
            wrap.classList.add('is-open');
            panel.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
        }

        function closeMenu() {
            wrap.classList.remove('is-open');
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }

        function toggleMenu(e) {
            e.preventDefault();
            e.stopPropagation();
            if (wrap.classList.contains('is-open')) {
                closeMenu();
            } else {
                openMenu();
            }
        }

        toggle.addEventListener('click', toggleMenu);

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMenu();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdminUserMenu);
    } else {
        initAdminUserMenu();
    }
})();
</script>
