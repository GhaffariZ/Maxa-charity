(function () {
    'use strict';
    const banner = document.querySelector('[data-banner-target]');
    if (!banner || banner.dataset.initialized) return;
    banner.dataset.initialized = 'true';
    const root = document.documentElement;
    const key = 'macsa-event-banner-dismissed-' + banner.dataset.bannerId;
    let dismissed = false;
    try { dismissed = sessionStorage.getItem(key) === '1'; } catch (_) {}
    if (dismissed) {
        banner.remove();
        root.style.removeProperty('--event-banner-offset');
        return;
    }
    let frame = 0;
    // The visible bottom handles wrapped text and restored scroll positions.
    function updatePosition() {
        frame = 0;
        const offset = banner.isConnected ? Math.max(0, banner.getBoundingClientRect().bottom) : 0;
        root.style.setProperty('--event-banner-offset', offset + 'px');
    }
    function schedulePosition() {
        if (!frame) frame = requestAnimationFrame(updatePosition);
    }
    const output = banner.querySelector('.event-banner-count');
    const fa = n => String(Math.max(0, n)).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    function tick() {
        let remaining = Math.max(0, Date.parse(banner.dataset.bannerTarget) - Date.now());
        const days = Math.floor(remaining / 86400000);
        remaining %= 86400000;
        const hours = Math.floor(remaining / 3600000);
        const minutes = Math.floor(remaining / 60000) % 60;
        output.textContent = fa(days) + ' روز · ' + fa(hours) + ' ساعت · ' + fa(minutes) + ' دقیقه';
    }
    tick();
    updatePosition();
    const timer = setInterval(tick, 60000);
    const observer = typeof ResizeObserver === 'function' ? new ResizeObserver(schedulePosition) : null;
    if (observer) observer.observe(banner);
    window.addEventListener('scroll', schedulePosition, {passive: true});
    window.addEventListener('resize', schedulePosition);
    window.addEventListener('pageshow', schedulePosition);
    window.addEventListener('load', schedulePosition);
    document.addEventListener('DOMContentLoaded', schedulePosition, {once: true});
    const close = banner.querySelector('.event-banner-dismiss');
    if (close) close.addEventListener('click', function () {
        try { sessionStorage.setItem(key, '1'); } catch (_) {}
        if (observer) observer.disconnect();
        clearInterval(timer);
        cancelAnimationFrame(frame);
        window.removeEventListener('scroll', schedulePosition);
        window.removeEventListener('resize', schedulePosition);
        window.removeEventListener('pageshow', schedulePosition);
        window.removeEventListener('load', schedulePosition);
        banner.remove();
        root.style.removeProperty('--event-banner-offset');
    });
})();
