(function () {
    const banner = document.getElementById('newsletter-banner');
    if (!banner) return;

    const overlay = document.getElementById('newsletter-banner-overlay');
    const style = banner.dataset.style || 'modal';
    const delay = parseInt(banner.dataset.delay || '1', 10) * 1000;
    const cooldownDays = parseInt(banner.dataset.cooldown || '30', 10);
    const storageKey = 'mks_newsletter_banner_dismissed';

    function isDismissed() {
        try {
            const raw = localStorage.getItem(storageKey);
            if (!raw) return false;
            const dismissedAt = parseInt(raw, 10);
            const cooldownMs = cooldownDays * 24 * 60 * 60 * 1000;
            return Date.now() - dismissedAt < cooldownMs;
        } catch (e) {
            return false;
        }
    }

    function dismiss() {
        try {
            localStorage.setItem(storageKey, String(Date.now()));
        } catch (e) { /* ignore */ }
        document.documentElement.classList.add('newsletter-banner-suppressed');
        hide();
    }

    function show() {
        if (style === 'modal' && overlay) {
            overlay.classList.add('is-visible');
        }
        banner.classList.add('is-visible');
        document.body.style.overflow = style === 'modal' ? 'hidden' : '';
    }

    function hide() {
        banner.classList.remove('is-visible');
        if (overlay) overlay.classList.remove('is-visible');
        document.body.style.overflow = '';
    }

    if (isDismissed()) {
        document.documentElement.classList.add('newsletter-banner-suppressed');
        return;
    }

    setTimeout(show, Math.max(0, delay));

    banner.querySelectorAll('[data-newsletter-close]').forEach(function (btn) {
        btn.addEventListener('click', dismiss);
    });

    if (overlay) {
        overlay.addEventListener('click', dismiss);
    }

    const form = banner.querySelector('.newsletter-banner__form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const messageEl = banner.querySelector('.newsletter-banner__message');
        const submitBtn = form.querySelector('.newsletter-banner__submit');
        const originalHtml = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form)
        })
        .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
        .then(function (result) {
            messageEl.className = 'newsletter-banner__message ' + (result.data.success ? 'is-success' : 'is-error');
            messageEl.textContent = result.data.message || '';
            if (result.data.success) {
                form.reset();
                setTimeout(dismiss, 2500);
            }
        })
        .catch(function () {
            messageEl.className = 'newsletter-banner__message is-error';
            messageEl.textContent = banner.dataset.error || 'حدث خطأ، حاول مرة أخرى';
        })
        .finally(function () {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        });
    });
})();
