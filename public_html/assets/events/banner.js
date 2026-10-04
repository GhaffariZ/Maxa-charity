(function () {
    'use strict';
    const banner = document.querySelector('[data-banner-target]');
    if (!banner || banner.dataset.initialized) return;
    banner.dataset.initialized = 'true';

    const root = document.documentElement;
    const key = 'macsa-event-banner-dismissed-' + (banner.dataset.bannerId || 'default');
    let dismissed = false;

    try {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('banner') || urlParams.has('preview') || urlParams.has('show_banner')) {
            sessionStorage.removeItem(key);
        } else {
            const val = sessionStorage.getItem(key);
            if (val === '1') {
                // Clear old legacy flag from testing session
                sessionStorage.removeItem(key);
            } else if (val) {
                const time = parseInt(val, 10);
                if (!isNaN(time) && Date.now() - time < 30 * 60 * 1000) {
                    dismissed = true;
                }
            }
        }
    } catch (_) {}

    if (dismissed) {
        if (banner.parentNode) banner.parentNode.removeChild(banner);
        root.style.removeProperty('--event-banner-offset');
        document.body.classList.remove('has-event-banner');
        return;
    }

    // Add helper class on body
    document.body.classList.add('has-event-banner');

    const output = banner.querySelector('.event-banner-count');
    const fa = n => String(Math.max(0, n)).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);

    function tick() {
        if (!output) return;
        const targetTime = Date.parse(banner.dataset.bannerTarget);
        if (isNaN(targetTime)) {
            output.textContent = 'به‌زودی';
            return;
        }

        let remaining = targetTime - Date.now();
        if (remaining <= 0) {
            output.textContent = 'در حال برگزاری';
            return;
        }

        const days = Math.floor(remaining / 86400000);
        remaining %= 86400000;
        const hours = Math.floor(remaining / 3600000);
        remaining %= 3600000;
        const minutes = Math.floor(remaining / 60000);

        if (window.innerWidth <= 640) {
            output.textContent = fa(days) + ' روز و ' + fa(hours) + 'س';
        } else {
            output.textContent = fa(days) + ' روز · ' + fa(hours) + ' ساعت · ' + fa(minutes) + ' دقیقه';
        }
    }

    try { tick(); } catch (e) { console.error('Banner tick err:', e); }

    const timer = setInterval(tick, 60000);
    const close = banner.querySelector('.event-banner-dismiss');
    if (close) {
        close.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            try { sessionStorage.setItem(key, String(Date.now())); } catch (_) {}
            clearInterval(timer);
            if (banner.parentNode) banner.parentNode.removeChild(banner);
            root.style.removeProperty('--event-banner-offset');
            document.body.classList.remove('has-event-banner');
        });
    }
})();
