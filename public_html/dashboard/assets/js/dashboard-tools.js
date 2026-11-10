(function () {
  'use strict';
  const config = window.MACSA_DASHBOARD;
  if (!config || !window.MacsaDashboardCatalog) return;
  const catalog = window.MacsaDashboardCatalog;
  const $ = id => document.getElementById(id);
  const fa = value => new Intl.NumberFormat('fa-IR').format(value);
  const frame = $('spaFrame');
  const commandDialog = $('commandDialog');
  const helpPanel = $('helpPanel');
  const notifPanel = $('notifPanel');
  let currentFile = '', commandFilter = 'all', matches = [], selected = 0;
  let page = 1, notifFilter = 'all', feed = null, sequence = 0, writing = false, requestController;
  let feedbackTimer;
  const dialogs = [commandDialog, helpPanel, notifPanel];

  function node(tag, text, className) {
    const element = document.createElement(tag);
    if (text !== undefined) element.textContent = text;
    if (className) element.className = className;
    return element;
  }
  function feedback(text) {
    clearTimeout(feedbackTimer);
    $('dashFeedback').textContent = text;
    $('dashFeedback').hidden = false;
    feedbackTimer = setTimeout(() => { $('dashFeedback').hidden = true; }, 6000);
  }
  function openDialog(dialog) {
    dialogs.forEach(other => { if (other !== dialog && other.open) other.close(); });
    if (!dialog.open) dialog.showModal();
    if (innerWidth <= 1024) {
      if (!$('app').classList.contains('collapsed')) $('toggleBtn').click();
    }
  }
  dialogs.forEach(dialog => {
    dialog.querySelector('[data-close-dialog]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => {
      if (event.target !== dialog) return;
      const bounds = dialog.getBoundingClientRect();
      if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
    });
  });

  function go(href, target) {
    if (!href) { $('dashHomeBtn').click(); return; }
    const link = node('a');
    link.href = href;
    if (target) { link.target = target; link.rel = 'noopener'; }
    link.hidden = true;
    document.body.append(link);
    link.click();
    link.remove();
  }

  const commands = catalog.buildCommands(config.nav, config.menu);
  const addCommand = (id, label, keywords, run) => commands.push({ id, label, keywords, run, group: 'ابزارهای داشبورد', type: 'commands' });
  addCommand('theme', 'تغییر تم روشن و تاریک', 'ظاهر شب روز dark light', () => $('themeBtn').click());
  addCommand('sidebar', 'باز و بسته کردن منوی کناری', 'سایدبار منو', () => $('toggleBtn').click());
  addCommand('notifications', 'نمایش اعلان‌ها', 'اعلان پیام زنگ notification', () => { openDialog(notifPanel); loadFeed(); });
  addCommand('unread', 'نمایش اعلان‌های نخوانده', 'اعلان جدید', () => { setNotifFilter('unread'); openDialog(notifPanel); });
  addCommand('read-all', 'خواندن همهٔ اعلان‌ها', 'خوانده علامت همه اعلان', () => { openDialog(notifPanel); markRead(null); });
  addCommand('refresh', 'به‌روزرسانی اعلان‌ها و کارهای منتظر رسیدگی', 'تازه سازی رفرش refresh', () => loadFeed());
  addCommand('help', 'راهنمای همین بخش', 'کمک آموزش help', () => { renderHelp(); openDialog(helpPanel); });
  addCommand('back', 'بازگشت به صفحهٔ قبل', 'قبلی back', () => history.back());
  addCommand('forward', 'رفتن به صفحهٔ بعد', 'بعدی forward', () => history.forward());
  addCommand('print', 'چاپ صفحهٔ بازشده', 'پرینت print', () => (currentFile && frame.contentWindow ? frame.contentWindow : window).print());
  addCommand('logout', 'خروج از حساب کاربری', 'خروج پایان نشست logout', () => go('logout.php', '_top'));
  if (document.fullscreenEnabled) addCommand('fullscreen', 'تغییر نمایش تمام‌صفحه', 'تمام صفحه fullscreen', () => {
    const promise = document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen();
    promise.catch(() => feedback('نمایش تمام‌صفحه در این مرورگر در دسترس نیست.'));
  });
  if (config.menu.isSuper) {
    for (const branch of config.menu.branches || []) {
      if (branch.id === config.menu.activeBranch || branch.status === 'disabled') continue;
      addCommand('branch-' + branch.id, 'تعویض شعبه به ' + branch.name, 'شعبه مرکز', () => {
        const form = node('form'); form.method = 'POST'; form.action = 'switch-branch.php';
        for (const [name, value] of Object.entries({ csrf_token: config.menu.csrf, branch_id: branch.id })) {
          const input = node('input'); input.type = 'hidden'; input.name = name; input.value = value; form.append(input);
        }
        document.body.append(form); form.submit();
      });
    }
  }

  function accessibleName(element, doc) {
    const field = element.matches('input:not([type="submit"]):not([type="button"]),select,textarea');
    let name = element.getAttribute('aria-label') || element.getAttribute('title');
    if (!name && element.getAttribute('aria-labelledby')) name = element.getAttribute('aria-labelledby').split(/\s+/).map(id => doc.getElementById(id)?.textContent || '').join(' ');
    if (!name && element.labels?.length) name = Array.from(element.labels).map(label => label.textContent).join(' ');
    if (!name && field) name = element.closest('.form-group,.input-group,.field')?.querySelector('label')?.textContent;
    if (!name) name = element.getAttribute('placeholder') || (!field && (element.innerText || element.getAttribute('value') || element.querySelector('img[alt]')?.alt));
    return String(name || '').replace(/\s+/g, ' ').trim();
  }
  function contextCommands() {
    let doc;
    try { doc = currentFile ? frame.contentDocument : document; } catch (_) { return []; }
    if (!doc || (currentFile && frame.getAttribute('aria-busy') === 'true')) return [];
    const scope = currentFile ? doc.body : document.querySelector('.content');
    if (!scope) return [];
    const result = [];
    const candidates = scope.querySelectorAll('button,a[href],input:not([type="hidden"]),select,textarea,[role="button"],summary');
    for (const element of candidates) {
      if (element.matches(':disabled') || element.closest('[inert],[hidden],[aria-disabled="true"],[aria-hidden="true"],dialog:not([open])') || !element.getClientRects().length) continue;
      if (element.checkVisibility && !element.checkVisibility({checkOpacity:true,checkVisibilityCSS:true})) continue;
      const style = doc.defaultView.getComputedStyle(element);
      if (style.visibility === 'hidden' || style.display === 'none') continue;
      const name = accessibleName(element, doc);
      if (!name) continue;
      const field = element.matches('input:not([type="submit"]):not([type="button"]),select,textarea');
      const record = element.closest('tr,.tk-card,[data-id],.news-card,.course-card,.stat-card,.card');
      const identity = record ? (record.getAttribute('data-id') || record.querySelector('td')?.innerText || record.querySelector('h2,h3,.news-title,.tk-subject,.stat-label')?.textContent || '').trim().slice(0, 65) : '';
      result.push({
        id: 'context-' + result.length, label: (field ? 'رفتن به فیلد: ' : '') + name,
        group: identity ? 'همین صفحه · ' + identity : 'همین صفحه', type: 'context',
        keywords: name + ' ' + currentFile, element, doc, field,
        run: () => {
          // Preserve the page's form validation, existing handlers and confirmation flow.
          if (!element.isConnected || element.disabled || !element.getClientRects().length) { feedback('این عملیات دیگر در دسترس نیست؛ جست‌وجو را دوباره باز کنید.'); return; }
          element.scrollIntoView({ block: 'center', behavior: 'instant' });
          element.focus({ preventScroll: true });
          if (!field) element.click();
        }
      });
    }
    return result;
  }

  function selectResult(index, scroll) {
    selected = Math.max(0, Math.min(index, matches.length - 1));
    $('commandResults').querySelectorAll('[role="option"]').forEach((option, i) => option.setAttribute('aria-selected', String(i === selected)));
    const option = $('commandResults').children[selected];
    if (option) {
      $('commandInput').setAttribute('aria-activedescendant', option.id);
      if (scroll) option.scrollIntoView({ block: 'nearest' });
    } else $('commandInput').removeAttribute('aria-activedescendant');
  }
  function runCommand(command) {
    commandDialog.close();
    if (command.run) command.run();
    else {
      go(command.href, command.target);
      if (command.selectRecord) feedback('ابتدا مورد مورد نظر را در فهرست انتخاب کنید؛ عملیات از کنترل‌های همان مورد انجام می‌شود.');
    }
  }
  function renderCommands() {
    const query = $('commandInput').value;
    matches = commands.concat(contextCommands()).filter(command => (commandFilter === 'all' || command.type === commandFilter) && catalog.matches(command, query));
    const results = $('commandResults'); results.replaceChildren();
    $('commandStatus').textContent = fa(matches.length) + ' نتیجه؛ مطابق دسترسی و امکانات فعلی شما';
    for (const [index, command] of matches.entries()) {
      const option = node('div', undefined, 'command-result'); option.id = 'command-option-' + index; option.setAttribute('role', 'option');
      const label = node('span'); label.append(node('strong', command.label), node('small', command.group + (command.selectRecord ? ' · انتخاب مورد از فهرست' : '')));
      option.append(label, node('span', command.type === 'pages' ? 'صفحه' : command.type === 'context' ? 'همین صفحه' : 'فرمان'));
      option.addEventListener('mousedown', event => event.preventDefault());
      option.addEventListener('click', () => runCommand(command));
      results.append(option);
    }
    if (!matches.length) results.append(node('p', 'نتیجه‌ای پیدا نشد. نام بخش یا کار مورد نظر را کوتاه‌تر بنویسید.', 'tool-empty'));
    selectResult(0, false);
  }
  function openCommands() {
    $('commandInput').value = ''; commandFilter = 'all';
    $('commandTabs').querySelectorAll('button').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.commandFilter === 'all')));
    renderCommands(); openDialog(commandDialog); $('commandInput').focus();
  }
  $('commandBtn').addEventListener('click', openCommands);
  $('commandInput').addEventListener('input', renderCommands);
  $('commandInput').addEventListener('keydown', event => {
    if (['ArrowDown', 'ArrowUp', 'Enter'].includes(event.key)) event.preventDefault();
    if (event.key === 'ArrowDown') selectResult(selected + 1, true);
    if (event.key === 'ArrowUp') selectResult(selected - 1, true);
    if (event.key === 'Enter' && matches[selected]) runCommand(matches[selected]);
  });
  $('commandTabs').addEventListener('click', event => {
    const button = event.target.closest('[data-command-filter]'); if (!button) return;
    commandFilter = button.dataset.commandFilter;
    $('commandTabs').querySelectorAll('button').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
    renderCommands(); $('commandInput').focus();
  });
  function shortcuts(event) {
    if ((event.ctrlKey || event.metaKey) && !event.altKey && event.code === 'KeyK') { event.preventDefault(); openCommands(); }
    if (event.key === 'Escape' && $('app').classList.contains('show-overlay')) {
      $('toggleBtn').click(); $('toggleBtn').focus();
    }
    if (event.key === 'Tab' && $('app').classList.contains('show-overlay')) {
      const focusable = Array.from($('sidebar').querySelectorAll('a[href],button')).filter(element => !element.closest('[inert]') && element.getClientRects().length && getComputedStyle(element).visibility !== 'hidden' && element.offsetHeight > 0);
      const first = focusable[0], last = focusable[focusable.length - 1];
      if (event.shiftKey && (event.target === first || !$('sidebar').contains(event.target))) { event.preventDefault(); last?.focus(); }
      if (!event.shiftKey && (event.target === last || !$('sidebar').contains(event.target))) { event.preventDefault(); first?.focus(); }
    }
  }
  document.addEventListener('keydown', shortcuts);

  function renderHelp() {
    const [, title, purpose, steps, note] = catalog.helpFor(currentFile.split('?')[0]);
    $('helpPageName').textContent = title;
    const content = $('helpContent'); content.replaceChildren(node('h3', 'کاربرد این بخش'), node('p', purpose), node('h3', 'روش کار'));
    const list = node('ol'); steps.forEach(step => list.append(node('li', step)));
    content.append(list, node('h3', 'وضعیت‌ها و نکات'), node('p', note, 'help-note'));
    content.append(node('h3', 'دسترسی سریع'), node('p', 'با Ctrl+K یا دکمهٔ جست‌وجو، نام صفحه یا کار را پیدا کنید. تب «همین صفحه» ابزارهای موجود در صفحهٔ بازشده را نشان می‌دهد.'));
  }
  $('helpBtn').addEventListener('click', () => { renderHelp(); openDialog(helpPanel); });

  const attentionKinds = {
    tickets: { title: 'تیکت منتظر پاسخ شما', detail: 'آخرین پیام از فرد دیگری است', href: 'tickets.php?pending=1' },
    news: { title: 'خبر منتظر بررسی', detail: 'در صف سردبیری شعبهٔ فعال', href: 'news-list.php?review=1&scope=active' },
    medical: { title: 'درخواست پذیرش در حال پیگیری', detail: 'جدید یا در حال بررسی', href: 'medical-records.php?status=pending&scope=active' }
  };
  function renderAttention(data) {
    const grid = $('attentionGrid'); grid.replaceChildren(); grid.setAttribute('aria-busy', 'false');
    for (const kind of data.available) {
      const info = attentionKinds[kind]; if (!info) continue;
      const missing = data.unavailable.includes(kind);
      const item = node(missing ? 'div' : 'a', undefined, 'attention-item'); if (!missing) item.href = info.href;
      const label = node('span', info.title); label.append(node('small', missing ? 'داده در دسترس نیست' : info.detail));
      item.append(node('strong', missing ? '—' : fa(data.attention[kind] || 0)), label); grid.append(item);
    }
    $('attentionTime').textContent = 'به‌روزرسانی: ' + formatTime(data.updated_at);
    $('attentionStatus').textContent = data.unavailable.length ? 'دریافت اطلاعات بعضی بخش‌ها ناموفق بود؛ دوباره تلاش کنید.' : !data.available.length ? 'در دسترسی‌های فعلی شما صف رسیدگی وجود ندارد.' : 'بازکردن یا خواندن اعلان، کار را انجام‌شده محسوب نمی‌کند.';
    $('attentionStatus').classList.toggle('tool-error', !!data.unavailable.length);
  }
  function formatTime(value) {
    if (!value) return 'زمان ثبت نشده';
    try { return new Intl.DateTimeFormat('fa-IR', { timeZone: 'Asia/Tehran', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(value)); }
    catch (_) { return 'زمان ثبت نشده'; }
  }
  function renderFeed(data) {
    const listTop = $('notifList').scrollTop;
    const active = document.activeElement;
    const activeKey = active?.closest('.notification-item')?.dataset.eventKey;
    const activeWasButton = active?.tagName === 'BUTTON';
    feed = data; page = data.page;
    const incomplete = data.unavailable.length > 0;
    const count = $('notifCount'); count.hidden = data.unread === 0 && !incomplete;
    count.classList.toggle('is-warning', incomplete);
    count.textContent = incomplete && !data.unread ? '!' : data.unread > 99 ? '۹۹+' : fa(data.unread) + (incomplete ? '+' : '');
    $('notifBtn').setAttribute('aria-label', incomplete ? 'اعلان‌ها؛ اطلاعات بعضی بخش‌ها در دسترس نیست' : data.unread ? 'اعلان‌ها؛ ' + fa(data.unread) + ' نخوانده' : 'اعلان‌ها؛ مورد نخوانده ندارید');
    const list = $('notifList'); list.replaceChildren(); list.setAttribute('aria-busy', 'false');
    for (const item of data.items) {
      const row = node('article', undefined, 'notification-item' + (item.unread ? ' unread' : ''));
      row.dataset.eventKey = item.key;
      const link = node('a'); link.href = item.href;
      link.append(node('h3', item.title), node('p', item.kind === 'medical' ? 'برای بررسی درخواست و مدارک، پرونده را باز کنید.' : 'مشاهده و پیگیری مورد مربوط'));
      link.addEventListener('click', async event => {
        event.preventDefault(); event.stopPropagation();
        if (writing) return;
        if (item.unread) await markRead(item.key);
        notifPanel.close(); go(item.href);
      });
      const meta = node('div', undefined, 'notification-meta'); meta.append(node('time', formatTime(item.created_at)));
      if (item.unread) {
        const button = node('button', 'خواندم'); button.type = 'button'; button.setAttribute('aria-label', 'خواندن ' + item.title);
        button.addEventListener('click', () => markRead(item.key)); meta.append(button);
      } else meta.append(node('span', 'خوانده‌شده'));
      row.append(link, meta); list.append(row);
    }
    if (!data.items.length) list.append(node('p', notifFilter === 'unread' ? 'اعلان نخوانده‌ای ندارید.' : 'رویدادی برای نمایش وجود ندارد.', 'tool-empty'));
    if (activeKey && notifPanel.open) {
      const replacement = Array.from(list.children).find(row => row.dataset.eventKey === activeKey);
      (replacement?.querySelector(activeWasButton ? 'button,a' : 'a') || $('notifRefresh')).focus({preventScroll:true});
    }
    list.scrollTop = listTop;
    $('notifMark').disabled = !data.unread || writing;
    $('notifPrevious').disabled = page <= 1; $('notifNext').disabled = !data.has_more;
    $('notifPage').textContent = 'صفحهٔ ' + fa(page) + ' · ' + fa(data.total) + ' مورد';
    $('notifStatus').classList.toggle('tool-error', !!data.unavailable.length);
    $('notifStatus').textContent = data.unavailable.length ? 'اطلاعات بعضی بخش‌ها در دسترس نیست؛ فهرست ممکن است کامل نباشد.' : 'آخرین دریافت: ' + formatTime(data.updated_at);
    renderAttention(data);
  }
  async function requestFeed(body, signal, passive) {
    const url = new URL('notifications.php', location.href);
    url.searchParams.set('page', page); url.searchParams.set('filter', notifFilter);
    if (passive) url.searchParams.set('passive', '1');
    const response = await fetch(url, { credentials: 'same-origin', cache: 'no-store', signal,
      ...(body ? { method: 'POST', headers: { 'X-CSRF-TOKEN': config.menu.csrf }, body } : {}) });
    if (response.redirected) throw new Error('نشست شما پایان یافته است؛ دوباره وارد داشبورد شوید.');
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'دریافت اطلاعات ناموفق بود.');
    return data;
  }
  function feedError(error) {
    const message = error instanceof SyntaxError ? 'پاسخ سامانه معتبر نیست؛ دوباره تلاش کنید.' : error.message;
    $('notifStatus').textContent = message; $('notifStatus').classList.add('tool-error');
    $('notifCount').textContent='!'; $('notifCount').hidden=false; $('notifCount').classList.add('is-warning');
    $('notifBtn').setAttribute('aria-label','اعلان‌ها؛ دریافت اطلاعات ناموفق بود');
    $('attentionStatus').textContent = (feed ? 'اطلاعات نمایش‌داده‌شده ممکن است به‌روز نباشد. ' : '') + message;
    $('attentionStatus').classList.add('tool-error');
    $('notifList').setAttribute('aria-busy', 'false'); $('attentionGrid').setAttribute('aria-busy', 'false');
    if (!feed) { $('notifList').replaceChildren(node('p', 'برای تلاش دوباره از دکمهٔ به‌روزرسانی استفاده کنید.', 'tool-empty')); $('notifPage').textContent = ''; $('notifPrevious').disabled = true; $('notifNext').disabled = true; }
  }
  async function loadFeed(passive = false) {
    if (writing) return;
    requestController?.abort(); requestController = new AbortController();
    const version = ++sequence;
    $('notifList').setAttribute('aria-busy', 'true');
    try { const data = await requestFeed(null, requestController.signal, passive === true); if (version === sequence) renderFeed(data); }
    catch (error) { if (error.name !== 'AbortError' && version === sequence) feedError(error); }
  }
  async function markRead(key) {
    if (writing) return;
    writing = true; ++sequence; requestController?.abort();
    $('notifMark').disabled = true;
    $('notifList').querySelectorAll('button').forEach(button => { button.disabled = true; });
    const body = new URLSearchParams({ action: key ? 'read' : 'read-all' }); if (key) body.set('key', key);
    try { const data = await requestFeed(body); writing = false; renderFeed(data); }
    catch (error) { writing = false; feedError(error); feedback('خوانده‌شدن اعلان ذخیره نشد؛ دوباره تلاش کنید.'); if (feed) $('notifMark').disabled = !feed.unread; $('notifList').querySelectorAll('button').forEach(button => { button.disabled = false; }); }
  }
  function setNotifFilter(value) {
    if (writing) { feedback('ذخیرهٔ وضعیت اعلان در حال انجام است؛ چند لحظه صبر کنید.'); return; }
    notifFilter = value; page = 1;
    $('notifList').scrollTop = 0;
    $('notifFilters').querySelectorAll('[data-notif-filter]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.notifFilter === value)));
    loadFeed();
  }
  $('notifBtn').addEventListener('click', () => { openDialog(notifPanel); loadFeed(); });
  $('notifFilters').addEventListener('click', event => { const button = event.target.closest('[data-notif-filter]'); if (button && !writing) setNotifFilter(button.dataset.notifFilter); });
  $('notifMark').addEventListener('click', () => markRead(null));
  $('notifRefresh').addEventListener('click', loadFeed);
  $('attentionRefresh').addEventListener('click', loadFeed);
  $('notifPrevious').addEventListener('click', () => { if (!writing) { page = Math.max(1, page - 1); $('notifList').scrollTop=0; loadFeed(); } });
  $('notifNext').addEventListener('click', () => { if (!writing && feed?.has_more) { page++; $('notifList').scrollTop=0; loadFeed(); } });
  document.addEventListener('click', async event => {
    const button = event.target.closest('[data-copy-ref]'); if (!button) return;
    try { await navigator.clipboard.writeText(button.dataset.copyRef); feedback('کد پیگیری کپی شد.'); }
    catch (_) { feedback('کد پیگیری: ' + button.dataset.copyRef); }
  });

  function syncPage() {
    const match = /(?:^|[#&])page=([^&]+)/.exec(location.hash);
    try { currentFile = match ? decodeURIComponent(match[1]) : ''; } catch (_) { currentFile = ''; }
    if (helpPanel.open) renderHelp();
  }
  window.addEventListener('hashchange', syncPage);
  window.addEventListener('macsa:page-change', syncPage);
  frame.addEventListener('load', () => {
    syncPage();
    try { frame.contentDocument?.addEventListener('keydown', shortcuts); } catch (_) {}
    loadFeed();
  });
  try { frame.contentDocument?.addEventListener('keydown', shortcuts); } catch (_) {}
  const header = document.querySelector('.topbar');
  const measureHeader = () => document.documentElement.style.setProperty('--dash-topbar-h', header.getBoundingClientRect().height + 'px');
  new ResizeObserver(measureHeader).observe(header); measureHeader();
  function labelRows() {
    const headers = Array.from(document.querySelectorAll('.dtable th')).map(th => th.textContent || 'جزئیات');
    document.querySelectorAll('.dtable tbody tr').forEach(row => Array.from(row.cells).forEach((cell, index) => { cell.dataset.label = headers[index] || 'جزئیات'; }));
  }
  new MutationObserver(labelRows).observe($('tableBody'), { childList: true }); labelRows();
  document.addEventListener('visibilitychange', () => { if (!document.hidden) loadFeed(true); });
  window.addEventListener('focus', () => { if (!document.hidden) loadFeed(true); });
  setInterval(() => { if (!document.hidden && !commandDialog.open) loadFeed(true); }, 60000);
  syncPage(); loadFeed();
})();
