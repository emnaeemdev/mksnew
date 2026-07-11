(function () {
    function getApp() {
        return document.getElementById('categorized-search-app');
    }

    function clearPageParams(url) {
        const toDelete = [];
        url.searchParams.forEach(function (_, key) {
            if (key === 'page' || key.indexOf('page_') === 0) {
                toDelete.push(key);
            }
        });
        toDelete.forEach(function (key) {
            url.searchParams.delete(key);
        });
    }

    function updateScatteredPicker(tab) {
        const app = getApp();
        if (!app) return;

        const select = app.querySelector('[data-search-tab-select]');
        const hint = app.querySelector('.search-scattered-picker .form-text');
        if (!select) return;

        const isScattered = tab && tab !== 'phrase' && tab !== 'all';
        select.value = isScattered ? tab : '';

        if (!hint) return;

        if (isScattered) {
            const option = select.querySelector('option[value="' + CSS.escape(tab) + '"]');
            const label = option
                ? option.textContent.replace(/\s*\(\d+\)\s*$/, '').trim()
                : '';
            hint.innerHTML = 'تعرض الآن نتائج: <strong style="color: rgb(124, 190, 86);">"' + label + '"</strong>';
        } else {
            hint.textContent = 'اختر من القائمة لعرض الوثائق التي تحتوي مجموعة كلمات محددة من بحثك.';
        }
    }

    function setActiveTab(tab) {
        const app = getApp();
        if (!app) return;

        app.querySelectorAll('[data-search-tab]').forEach(function (btn) {
            const isActive = btn.getAttribute('data-search-tab') === tab;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        updateScatteredPicker(tab);
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

    function onScatteredSelectChange(event) {
        const select = event.target.closest('[data-search-tab-select]');
        if (!select || !select.value) return;

        const url = new URL(window.location.href);
        url.searchParams.set('tab', select.value);
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
        app.addEventListener('change', onScatteredSelectChange);
        app.addEventListener('click', onPaginationClick);
        window.addEventListener('popstate', onPopState);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
