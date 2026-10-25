(() => {
  const locale = document.documentElement.lang;
  // Public cards may be inserted by live feeds after the server rendered the page.
  // Update their navigation only; never translate their text or API responses.
  const localizeLink = link => {
    if (!['en', 'ar'].includes(locale) || link.hasAttribute('hreflang')) return;
    const raw = link.getAttribute('href');
    if (!raw || raw.startsWith('#')) return;
    let url;
    try { url = new URL(raw, document.baseURI); } catch { return; }
    if (url.origin !== location.origin || !['http:', 'https:'].includes(url.protocol)) return;
    if (/^\/(?:en|ar)(?:\/|$)|^\/(?:dashboard|benefactor-dashboard|api|core|uploads|assets|webfont|docs)(?:\/|$)|^\/api-|\.(?:pdf|png|jpe?g|webp|svg|gif|mp4|zip)$/i.test(url.pathname)) return;
    link.setAttribute('href', '/' + locale + url.pathname + url.search + url.hash);
  };
  document.querySelectorAll('a[href]').forEach(localizeLink);
  new MutationObserver(records => {
    records.forEach(record => record.addedNodes.forEach(node => {
      if (node.nodeType !== Node.ELEMENT_NODE) return;
      if (node.matches('a[href]')) localizeLink(node);
      node.querySelectorAll('a[href]').forEach(localizeLink);
    }));
  }).observe(document.body, { childList: true, subtree: true });
  // Native details works without JS; add Escape/outside-click keyboard behavior.
  document.addEventListener('click', event => {
    document.querySelectorAll('.site-language[open]').forEach(menu => {
      if (!menu.contains(event.target)) menu.open = false;
    });
  });
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('.site-language[open]').forEach(menu => {
      menu.open = false;
      menu.querySelector('summary').focus();
    });
  });
})();
