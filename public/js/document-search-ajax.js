(function () {
    const PAGE_PARAMS = ['page', 'page_p', 'page_a', 'page_any', 'page_phrase', 'page_all', 'page_w0', 'page_w1', 'page_w2', 'page_w3'];

    function getApp() {
        return document.getElementById('categorized-search-app');
    }

    function clearPageParams(url) {
        PAGE_PARAMS.forEach(function (key) {
            url.searchParams.delete(key);
        });
    }

    function setActiveTab(tab) {
        const app = getApp();
        if (!app) return;

        app.querySelectorAll('[data-search-tab]').forEach(function (btn) {
            const isActive = btn.getAttribute('data-search-tab') === tab;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    }

    function showLoading() {
        const wrapper = document.getElementById('search-tab-content-wrapper');
        if (!wrapper) return;
        wrapper.style.opacity = '0.55';
        wrapper.style.pointerEvents = 'none';
    }

    function hideLoading() {
        const wrapper = document.getElementById('search-tab-content-wrapper');
        if (!wrapper) return;
        wrapper.style.opacity = '';
        wrapper.style.pointerEvents = '';
    }

    function scrollToSearchResults() {
        const anchor = document.getElementById('results-tabs') || document.getElementById('categorized-search-app');
        if (!anchor) return;

        const top = anchor.getBoundingClientRect().top + window.pageYOffset - 12;
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    }

    async function loadSearchFragment(fetchUrl, pushState) {
        const app = getApp();
        if (!app) return;

        const url = new URL(fetchUrl, window.location.origin);
        url.searchParams.set('fragment', '1');

        showLoading();

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Search-Fragment': '1',
                    'Accept': 'text/html',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Search fragment request failed');
            }

            const html = await response.text();
            const wrapper = document.getElementById('search-tab-content-wrapper');
            if (wrapper) {
                wrapper.innerHTML = html;
            }

            const tab = url.searchParams.get('tab');
            if (tab) {
                setActiveTab(tab);
            }

            const shareUrl = new URL(url.toString());
            shareUrl.searchParams.delete('fragment');

            if (pushState !== false) {
                history.pushState({ searchFragment: true, tab: tab || '' }, '', shareUrl.toString());
            }

            scrollToSearchResults();
        } catch (error) {
            window.location.href = fetchUrl;
        } finally {
            hideLoading();
        }
    }

    function onTabClick(event) {
        const btn = event.target.closest('[data-search-tab]');
        if (!btn) return;

        event.preventDefault();

        const tab = btn.getAttribute('data-search-tab');
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        clearPageParams(url);

        loadSearchFragment(url.toString());
    }

    function onPaginationClick(event) {
        const link = event.target.closest('#categorized-search-app .pagination a');
        if (!link || !link.href) return;

        event.preventDefault();
        loadSearchFragment(link.href);
    }

    function onPopState() {
        loadSearchFragment(window.location.href, false);
    }

    function init() {
        const app = getApp();
        if (!app) return;

        app.addEventListener('click', onTabClick);
        app.addEventListener('click', onPaginationClick);
        window.addEventListener('popstate', onPopState);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
