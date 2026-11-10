(() => {
  const root = document.querySelector('[data-site-nav]');
  const drawer = document.querySelector('[data-site-drawer]');
  if (!root || !drawer) return;
  const header = root.querySelector('.cta-header');
  const list = root.querySelector('.cta-menu');
  const controls = root.querySelector('.cta-left');
  const brand = root.querySelector('.cta-right');
  const toggle = root.querySelector('#menuToggle');
  const close = drawer.querySelector('#mobileMenuClose');
  const backdrop = document.querySelector('[data-site-backdrop]');
  const language = root.querySelector('.site-language');
  const center = root.querySelector('.cta-center');
  let frame = 0;
  let restoreFocus = null;
  let previousOverflow = '';

  const focusable = element => [...element.querySelectorAll('a[href],button:not([disabled]),summary,[tabindex="0"]')]
    .filter(node => node.getClientRects().length && getComputedStyle(node).visibility !== 'hidden');
  const collapse = group => {
    group.classList.remove('open');
    group.querySelector('.site-nav-submenu')?.setAttribute('aria-expanded', 'false');
  };
  const closeSubmenus = () => document.querySelectorAll('[data-site-nav] .mega-menu.open,[data-site-drawer] .mega-menu.open').forEach(collapse);
  const closeAccount = () => root.querySelectorAll('.cta-account.open').forEach(account => {
    account.classList.remove('open');
    account.querySelector('button')?.setAttribute('aria-expanded', 'false');
  });
  const openSubmenu = (group, source = 'button') => {
    closeSubmenus();
    if (language) language.open = false;
    closeAccount();
    group.classList.add('open');
    group.dataset.openedBy = source;
    group.querySelector('.site-nav-submenu')?.setAttribute('aria-expanded', 'true');
  };

  // One real link and a separate disclosure button: touch never loses the parent page.
  list.querySelectorAll('.mega-menu').forEach((group, index) => {
    const link = group.querySelector('.mega-toggle');
    const panel = group.querySelector('.mega-menu-content');
    if (!link || !panel) return;
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'site-nav-submenu';
    button.setAttribute('aria-label', `${link.textContent.trim()} – ${root.dataset.submenuLabel}`);
    button.setAttribute('aria-expanded', 'false');
    panel.id = `site-nav-section-${index}`;
    button.setAttribute('aria-controls', panel.id);
    button.innerHTML = '<span class="site-nav-chevron" aria-hidden="true"></span>';
    link.after(button);
  });
  const mobileList = list.cloneNode(true);
  mobileList.className = 'mobile-menu';
  mobileList.removeAttribute('id');
  mobileList.querySelectorAll('[id]').forEach(node => { node.id += '-mobile'; });
  mobileList.querySelectorAll('[aria-controls]').forEach(node => {
    node.setAttribute('aria-controls', node.getAttribute('aria-controls') + '-mobile');
  });
  drawer.querySelector('.mobile-menu-container').replaceChildren(mobileList);

  [list, mobileList].forEach(menu => {
    menu.querySelectorAll('.mega-menu').forEach(group => {
      const button = group.querySelector('.site-nav-submenu');
      let timer;
      button?.addEventListener('click', () => {
        clearTimeout(timer);
        if (group.classList.contains('open') && group.dataset.openedBy === 'button') collapse(group);
        else openSubmenu(group);
      });
      group.addEventListener('pointerenter', event => {
        if (menu !== list || root.dataset.navMode !== 'desktop' || event.pointerType !== 'mouse') return;
        clearTimeout(timer);
        if (!group.classList.contains('open')) openSubmenu(group, 'hover');
      });
      group.addEventListener('pointerleave', event => {
        if (menu === list && event.pointerType === 'mouse' && group.dataset.openedBy !== 'button') timer = setTimeout(() => collapse(group), 160);
      });
      group.addEventListener('focusout', () => {
        requestAnimationFrame(() => { if (!group.contains(document.activeElement)) collapse(group); });
      });
    });
  });

  function closeDrawer(returnFocus = true) {
    if (!drawer.classList.contains('open')) return;
    drawer.classList.remove('open');
    drawer.setAttribute('aria-hidden', 'true');
    drawer.removeAttribute('aria-modal');
    drawer.inert = true;
    backdrop.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
    toggle.classList.remove('active');
    document.body.classList.remove('mobile-menu-open');
    document.documentElement.style.overflow = previousOverflow;
    closeSubmenus();
    if (returnFocus && restoreFocus?.getClientRects().length) restoreFocus.focus();
  }
  function openDrawer() {
    if (root.dataset.navMode !== 'compact') return;
    restoreFocus = toggle;
    previousOverflow = document.documentElement.style.overflow;
    closeSubmenus();
    closeAccount();
    if (language) language.open = false;
    drawer.inert = false;
    drawer.setAttribute('aria-hidden', 'false');
    drawer.setAttribute('aria-modal', 'true');
    drawer.classList.add('open');
    backdrop.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
    document.body.classList.add('mobile-menu-open');
    document.documentElement.style.overflow = 'hidden';
    requestAnimationFrame(() => { if (drawer.classList.contains('open')) close.focus(); });
  }
  toggle.addEventListener('click', () => drawer.classList.contains('open') ? closeDrawer() : openDrawer());
  close.addEventListener('click', () => closeDrawer());
  backdrop.addEventListener('click', () => closeDrawer());

  function measure() {
    frame = 0;
    const styles = getComputedStyle(header);
    const compactNow = root.dataset.navMode === 'compact';
    const desktopControls = controls.getBoundingClientRect().width + (compactNow
      ? root.querySelector('.cta-donate').getBoundingClientRect().width - toggle.getBoundingClientRect().width : 0);
    const available = header.clientWidth - parseFloat(styles.paddingLeft) - parseFloat(styles.paddingRight)
      - brand.getBoundingClientRect().width - desktopControls - 2 * parseFloat(styles.columnGap);
    const compact = innerWidth < 1120 || list.scrollWidth + 12 > available;
    const mode = compact ? 'compact' : 'desktop';
    if (root.dataset.navMode !== mode) {
      const drawerWasOpen = drawer.classList.contains('open');
      root.dataset.navMode = mode;
      closeSubmenus();
      if (!compact) closeDrawer(false);
      center.inert = compact;
      if (!compact && drawerWasOpen) requestAnimationFrame(() => {
        if (root.dataset.navMode === 'desktop') list.querySelector('a')?.focus();
      });
    }
    center.inert = compact;
    center.setAttribute('aria-hidden', String(compact));
    root.querySelector('.cta-donate').inert = compact;
  }
  const schedule = () => { if (!frame) frame = requestAnimationFrame(measure); };
  if ('ResizeObserver' in window) {
    const observer = new ResizeObserver(schedule);
    observer.observe(header);
    observer.observe(controls);
  }
  document.fonts.ready.then(schedule);
  window.addEventListener('resize', schedule, {passive: true});
  window.addEventListener('pageshow', schedule);
  window.addEventListener('scroll', () => root.classList.toggle('scrolled', scrollY > 30), {passive: true});
  measure();
  root.dataset.navReady = 'true';

  language?.addEventListener('toggle', () => {
    if (language.open) { closeSubmenus(); closeAccount(); }
  });
  if (location.hash) language?.querySelectorAll('a[hreflang]').forEach(link => { link.hash = location.hash; });
  document.addEventListener('click', event => {
    if (language && !language.contains(event.target)) language.open = false;
    if (!event.target.closest('.mega-menu')) closeSubmenus();
    if (event.target.closest('.cta-account-btn')) {
      closeSubmenus();
      if (language) language.open = false;
    }
  }, true);
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      if (drawer.classList.contains('open')) { event.preventDefault(); closeDrawer(); return; }
      if (language?.open) { language.open = false; language.querySelector('summary').focus(); return; }
      const account = root.querySelector('.cta-account.open');
      if (account) { closeAccount(); account.querySelector('button')?.focus(); return; }
      const activeGroup = document.activeElement.closest('.mega-menu.open');
      if (activeGroup) { collapse(activeGroup); activeGroup.querySelector('.site-nav-submenu')?.focus(); }
      else closeSubmenus();
    }
    if (event.key === 'Tab' && drawer.classList.contains('open')) {
      const items = focusable(drawer);
      const first = items[0], last = items.at(-1);
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
      else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }
  }, true);
})();
