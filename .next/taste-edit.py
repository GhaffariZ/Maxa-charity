from pathlib import Path
import re

root = Path('D:/Maxa-charity/public_html')
def read(p): return (root / p).read_text(encoding='utf-8-sig')
def write(p, text): (root / p).write_text(text, encoding='utf-8', newline='\n')

header_path = 'dashboard/components/header/component.php'
header = read(header_path)
header = "<?php\nif (defined('MACSA_PUBLIC_HEADER')) return;\ndefine('MACSA_PUBLIC_HEADER', true);\nrequire_once __DIR__ . '/../../../core/icons.php';\n?>\n" + header
header = header.replace('<body>', '<body class="macsa-public">')
header = header.replace('</head>', '<link rel="stylesheet" href="/assets/public-site.css">\n<script src="/assets/public-site.js" defer></script>\n</head>', 1)
header = header.replace('</body>', '').replace('</html>', '')
header = header.replace('<div class="menu-icon" id="menuToggle" aria-label="منوی موبایل">\n            <div></div>\n            <div></div>\n            <div></div>\n          </div>', '<button type="button" class="menu-icon" id="menuToggle" aria-label="منوی موبایل" aria-controls="mobileMenu" aria-expanded="false">\n            <span></span><span></span><span></span>\n          </button>')
# Replace only navigation behavior; retain the existing authentication flow.
start = header.index('  <script>\n  (function(){')
end = header.index('  </script>', start) + len('  </script>')
header = header[:start] + header[end:]
header = header.replace("      var hasSession = document.cookie.indexOf('maksa_session=1') !== -1 ||\n                       localStorage.getItem('maksa_logged') === '1' ||\n                       sessionStorage.getItem('maxa_access_token');", "      var hasSession = document.cookie.indexOf('maksa_session=1') !== -1;\n      try { hasSession = hasSession || localStorage.getItem('maksa_logged') === '1' || sessionStorage.getItem('maxa_access_token'); } catch (_) {}")
write(header_path, header)

hero_path = 'dashboard/components/heroindex/component.php'
old = read(hero_path)
css = old[old.index('    /* ===== HERO ===== */'):old.index('    /* ===== TOP BAR ===== */')]
css += old[old.index('    /* ===== Slider ===== */'):old.index('    /* ===== Responsive ===== */')]
markup = old[old.index('    <div class="cta-hero-wrap">'):old.index('\n  </section>\n', old.index('    <div class="cta-hero-wrap">'))]
markup = markup.replace('aria-label="CTA Hero Slider"', 'aria-label="معرفی مکسا"')
markup = markup.replace('<h1 class="cta-title" id="heroTitle"></h1>', '<h1 class="cta-title" id="heroTitle">موسسه نیکوکاری کنترل سرطان ایرانیان (مکسا)</h1>')
markup = markup.replace('<p class="cta-desc" id="heroDesc"></p>', '<p class="cta-desc" id="heroDesc">هم سنگر بیماران در مبارزه با سرطان</p>')
markup = markup.replace('id="heroBtn" href="#"', 'id="heroBtn" href="#" hidden')
markup = markup.replace('<div class="cta-track" id="ctaTrack">', '<div class="cta-track" id="ctaTrack">\n             <div class="cta-slide" style="background-image:url(\'/uploads/hero/hero_1780827718_9482.png\')"></div>')
markup = markup.replace('id="ctaPrev"', 'id="ctaPrev" hidden').replace('id="ctaNext"', 'id="ctaNext" hidden')
markup += '\n<p class="hero-status" role="status" aria-live="polite">در حال بارگذاری معرفی مکسا...</p>\n'
write(hero_path, "<?php require __DIR__ . '/../header/component.php'; ?>\n<style>\n" + css + '\n</style>\n<section class="cta">\n' + markup + '\n</section>\n')

# Keep document ownership in the header/footer; hero already includes the header.
path = 'dashboard/page-view.php'
s = read(path).replace("['header', 'topbar']", "['header', 'heroindex', 'heroindex ikhc']")
s = s.replace("    echo '<script>window.__MAXA_BRANCH__='", "    $GLOBALS['macsaBranchContext'] = '<script>window.__MAXA_BRANCH__='")
s = s.replace("    echo_components($components, $pageTitle);", "    echo_components($components, $pageTitle);", 1)
# Place branch context after the document opens, without changing branch values.
needle = "            $code = ob_get_clean();"
s = s.replace(needle, needle + "\n            if (!empty($GLOBALS['macsaBranchContext']) && strpos($code, '<body') !== false) {\n                $code = preg_replace('/(<body\\b[^>]*>)/i', '$1' . $GLOBALS['macsaBranchContext'], $code, 1);\n                unset($GLOBALS['macsaBranchContext']);\n            }\n            // CMS content blocks are fragments inside the public document.\n            if (defined('MACSA_PUBLIC_HEADER') && !in_array($cleanComponent, ['header', 'heroindex', 'footer'], true)) {\n                $code = preg_replace('/<!doctype[^>]*>|<\\/?(?:html|head|body)\\b[^>]*>|<title\\b[^>]*>.*?<\\/title>|<meta\\b[^>]*>/is', '', $code);\n            }")
write(path, s)

for path in ['dashboard/components/aboutus/component.php', 'dashboard/components/hamrah/component.php']:
    s = read(path).replace('<h1', '<h2').replace('</h1>', '</h2>')
    s = s.replace('<img src="{{image1}}">', '<img src="{{image1}}" alt="خدمات مراقبتی مکسا" loading="lazy">').replace('<img src="{{image2}}">', '<img src="{{image2}}" alt="تیم مراقبت و حمایت مکسا" loading="lazy">')
    if '/hamrah/' in path:
        # Keep native swipe/scroll and destination controls; remove autoplay entirely.
        s = s[:s.index('<script>')] + '''<script>
document.addEventListener('DOMContentLoaded', function () {
  const gallery = document.querySelector('.macsa-gallery');
  const cards = gallery ? Array.from(gallery.querySelectorAll('.gallery-item')) : [];
  document.querySelectorAll('.macsa-dot').forEach((dot, i) => dot.addEventListener('click', () => {
    if (cards[i]) cards[i].scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'instant' });
  }));
});
</script>
'''
    write(path, s)

path = 'dashboard/components/macsa_stories/component.php'
s = read(path).replace('track.innerHTML = cardsHtml + cardsHtml;', 'track.innerHTML = cardsHtml;')
s = s.replace('<img src="{{image1}}">', '<img src="{{image1}}" alt="روایت‌های امید و همراهی بیماران مکسا" loading="lazy">')
write(path, s)

path = 'dashboard/components/ourexcellence/component.php'
s = read(path)
start = s.index('<script>')
s = s[:start] + '''<script>
document.querySelectorAll('.travel-number').forEach(counter => {
  const target = Number(counter.dataset.target);
  if (Number.isFinite(target)) counter.textContent = target.toLocaleString('fa-IR');
});
</script>
'''
write(path, s)

path = 'dashboard/components/footer/component.php'
s = read(path)
s = s.replace('<input type="email" placeholder=', '<input type="email" required autocomplete="email" aria-label="آدرس ایمیل برای درخواست عضویت در خبرنامه" placeholder=', 1)
s = s.replace('<p class="gf-privacy">', '<p class="newsletter-status" role="status" aria-live="polite"></p>\n<p class="gf-privacy">', 1)
s += "\n<?php if (defined('MACSA_PUBLIC_HEADER') && !defined('MACSA_PUBLIC_CLOSED')) { define('MACSA_PUBLIC_CLOSED', true); echo '</body></html>'; } ?>\n"
write(path, s)

# Preserve page voice; replace only the forbidden punctuation in public-facing titles.
path = 'news.php'
s = read(path).replace('آخرین اخبار و رویدادها — مکسا', 'آخرین اخبار و رویدادها - مکسا')
s = s.replace('\n</body>\n</html>', '')
write(path, s)
