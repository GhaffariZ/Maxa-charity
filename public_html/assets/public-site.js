/* Shared public navigation and request states; no autoplay or scroll listeners. */
(() => {
  const toggle = document.getElementById('menuToggle');
  const drawer = document.getElementById('mobileMenu');
  const backdrop = document.getElementById('mobileBackdrop');
  const close = document.getElementById('mobileMenuClose');
  const desktop = document.querySelector('.cta-menu');
  const mobile = document.getElementById('mobileMenuContainer');
  if (desktop && mobile) {
    const clone = desktop.cloneNode(true);
    clone.removeAttribute('id');
    clone.className = 'mobile-menu';
    mobile.append(clone);
  }
  function setDrawer(open) {
    if (!drawer || !toggle) return;
    drawer.classList.toggle('open', open);
    backdrop?.classList.toggle('show', open);
    document.body.classList.toggle('mobile-menu-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    drawer.inert = !open;
    if (open) close?.focus(); else toggle.focus();
  }
  if (drawer) drawer.inert = true;
  toggle?.addEventListener('click', () => setDrawer(!drawer.classList.contains('open')));
  close?.addEventListener('click', () => setDrawer(false));
  backdrop?.addEventListener('click', () => setDrawer(false));
  drawer?.addEventListener('keydown', event => {
    if (event.key !== 'Tab') return;
    const focusable = Array.from(drawer.querySelectorAll('a,button,input,[tabindex="0"]')).filter(el => el.getClientRects().length);
    const first = focusable[0], last = focusable.at(-1);
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
  });
  document.querySelectorAll('.mega-menu').forEach(menu => {
    const link = menu.querySelector('.mega-toggle');
    const content = menu.querySelector('.mega-menu-content');
    if (!link || !content) return;
    link.setAttribute('aria-expanded', 'false');
    const setOpen = open => {
      link.setAttribute('aria-expanded', String(open));
      menu.classList.toggle('open', open);
      content.classList.toggle('active', open);
    };
    menu.addEventListener('mouseenter', () => setOpen(true));
    menu.addEventListener('mouseleave', () => setOpen(false));
    menu.addEventListener('focusout', event => { if (!menu.contains(event.relatedTarget)) setOpen(false); });
    link.addEventListener('click', event => {
      if (menu.closest('.mobile-menu') || link.getAttribute('href') === 'javascript:void(0);') {
        event.preventDefault(); setOpen(link.getAttribute('aria-expanded') !== 'true');
      }
    });
    link.addEventListener('keydown', event => {
      if (event.key === 'ArrowDown' || event.key === ' ') {
        event.preventDefault(); setOpen(true); content.querySelector('a')?.focus();
      }
    });
  });
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    if (drawer?.classList.contains('open')) setDrawer(false);
    document.querySelectorAll('.mega-menu.open').forEach(menu => {
      menu.classList.remove('open');
      menu.querySelector('.mega-menu-content')?.classList.remove('active');
      menu.querySelector('.mega-toggle')?.setAttribute('aria-expanded', 'false');
    });
  });
  matchMedia('(min-width: 1321px)').addEventListener('change', event => {
    if (event.matches && drawer?.classList.contains('open')) setDrawer(false);
  });
  document.querySelectorAll('#ctaCards .cta-card').forEach(card => {
    const navigate = () => { if (card.dataset.link) window.location.href = card.dataset.link; };
    card.addEventListener('click', navigate);
    card.addEventListener('keydown', event => {
      if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); navigate(); }
    });
  });

const track = document.getElementById('ctaTrack');
if (track) {
  const title = document.getElementById('heroTitle');
  const description = document.getElementById('heroDesc');
  const button = document.getElementById('heroBtn');
  const status = document.querySelector('.hero-status');
  const prev = document.getElementById('ctaPrev'), next = document.getElementById('ctaNext');
  const dots = document.getElementById('ctaDots');
  let index = 0;
  const branch = window.__MAXA_BRANCH__ ? '?branch=' + encodeURIComponent(window.__MAXA_BRANCH__) : '';
  fetch('/dashboard/hero-list.php' + branch, { signal: AbortSignal.timeout(10000) })
    .then(response => { if (!response.ok) throw new Error('hero'); return response.json(); })
    .then(json => {
      const slides = json.status === 'success' && Array.isArray(json.data) ? json.data : [];
      if (!slides.length) { status.textContent = 'معرفی تازه‌ای برای نمایش وجود ندارد.'; return; }
      track.replaceChildren();
      dots.replaceChildren();
      const plain = value => {
        const template = document.createElement('template');
        template.innerHTML = String(value || '');
        return template.content.textContent.replace(/\u2014/g, '-');
      };
      function show(i) {
        index = (i + slides.length) % slides.length;
        const slide = slides[index];
        track.style.transform = 'translateX(' + index * 100 + '%)';
        title.textContent = plain(slide.title) || 'مکسا';
        description.textContent = plain(slide.description);
        button.hidden = !slide.button_link;
        if (slide.button_link) button.setAttribute('href', slide.button_link);
        Array.from(dots.children).forEach((dot, j) => {
          dot.classList.toggle('active', j === index);
          dot.setAttribute('aria-pressed', String(j === index));
        });
      }
      slides.forEach((slide, i) => {
        const photo = document.createElement('div');
        photo.className = 'cta-slide';
        if (slide.image) {
          const picture = document.createElement('picture');
          if (slide.image_mobile) {
            const source = document.createElement('source'); source.srcset = slide.image_mobile; source.type = 'image/webp'; source.media = '(max-width: 767px)'; picture.append(source);
          }
          if (slide.image_webp) {
            const source = document.createElement('source'); source.srcset = slide.image_webp; source.type = 'image/webp'; picture.append(source);
          }
          const img = document.createElement('img'); img.src = slide.image; img.alt = '';
          img.width = 1400; img.height = 800; img.fetchPriority = i === 0 ? 'high' : 'low';
          picture.append(img); photo.append(picture);
        }
        track.append(photo);
        if (slides.length > 1) {
          const dot = document.createElement('button');
          dot.className = 'cta-dot'; dot.type = 'button';
          dot.setAttribute('aria-label', 'رفتن به اسلاید ' + (i + 1));
          dot.addEventListener('click', () => show(i)); dots.append(dot);
        }
      });
      prev.hidden = next.hidden = slides.length < 2;
      prev.addEventListener('click', () => show(index - 1));
      next.addEventListener('click', () => show(index + 1));
      const hero = document.querySelector('.cta-hero');
      hero.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
          event.preventDefault(); show(index + (event.key === 'ArrowLeft' ? 1 : -1));
        }
      });
      let touchX = null;
      hero.addEventListener('touchstart', event => { touchX = event.touches[0].clientX; }, { passive: true });
      hero.addEventListener('touchend', event => {
        if (touchX !== null) {
          const distance = event.changedTouches[0].clientX - touchX;
          if (Math.abs(distance) > 40) show(index + (distance < 0 ? 1 : -1));
          touchX = null;
        }
      }, { passive: true });
      show(0); status.textContent = '';
    }).catch(() => { status.textContent = 'معرفی تازه بارگذاری نشد. اطلاعات اصلی مکسا همچنان در دسترس است.'; });
}

  const newsletter = document.querySelector('.gf-newsletter');
  if (newsletter) {
    const input = newsletter.querySelector('input'), submit = newsletter.querySelector('button');
    const status = document.querySelector('.newsletter-status');
    async function requestSubscription() {
      if (submit.disabled || !input.reportValidity()) return;
      submit.disabled = true; newsletter.setAttribute('aria-busy', 'true');
      status.textContent = 'در حال ارسال درخواست عضویت...';
      try {
        const response = await fetch('/api/contact', {
          method: 'POST', headers: { 'Content-Type': 'application/json' }, signal: AbortSignal.timeout(15000),
          body: JSON.stringify({ name: 'متقاضی خبرنامه', email: input.value.trim(), subject: 'درخواست عضویت در خبرنامه', message: 'درخواست دریافت گزارش کمک‌ها و داستان بیماران از طریق ایمیل.', website: '' })
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error('newsletter');
        status.textContent = 'درخواست عضویت شما برای تیم مکسا ارسال شد.';
        input.value = '';
      } catch (_) { status.textContent = 'درخواست ارسال نشد. لطفاً دوباره تلاش کنید.'; }
      finally { submit.disabled = false; newsletter.removeAttribute('aria-busy'); }
    }
    submit.addEventListener('click', requestSubscription);
    input.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); requestSubscription(); } });
  }
})();
