(() => {
  let requests = 0;
  let navigating = false;
  let revealTimer;
  let navigationTimer;
  const indicator = () => document.getElementById('macsaSiteLoading');
  function update() {
    clearTimeout(revealTimer);
    const node = indicator();
    if (!node) return;
    if (!requests && !navigating) { node.hidden = true; return; }
    // Fast requests never flash a loader, and slow requests never block the page.
    if (node.hidden) revealTimer = setTimeout(() => { node.hidden = false; }, 180);
  }
  window.addEventListener('macsa:loading', event => {
    requests = Math.max(0, requests + event.detail);
    update();
  });
  document.addEventListener('DOMContentLoaded', update, { once: true });
  document.addEventListener('click', event => {
    const link = event.target.closest?.('a[href]');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey
      || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
    const url = new URL(link.href, location.href);
    if (url.origin !== location.origin || !/^https?:$/.test(url.protocol)
      || (url.pathname === location.pathname && url.search === location.search)
      || /\.(?:pdf|zip|png|jpe?g|webp|svg|csv|xlsx?|docx?)$/i.test(url.pathname)) return;
    queueMicrotask(() => {
      if (event.defaultPrevented) return;
      navigating = true;
      update();
      clearTimeout(navigationTimer);
      navigationTimer = setTimeout(() => { navigating = false; update(); }, 12000);
    });
  });
  window.addEventListener('pageshow', () => {
    navigating = false;
    clearTimeout(navigationTimer);
    update();
  });
})();
