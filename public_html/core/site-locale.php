<?php
declare(strict_types=1);

/** Public templates only. Never infer the language of an authenticated panel. */
function site_locale(): string
{
    $path = defined('MACSA_PUBLIC_PATH') ? MACSA_PUBLIC_PATH : (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    return preg_match('~^/(en|ar)(?:/|$)~', $path, $match) ? $match[1] : 'fa';
}

function site_direction(): string { return site_locale() === 'en' ? 'ltr' : 'rtl'; }

function site_text(string $source): string
{
    static $catalogue;
    $locale = site_locale();
    if ($locale === 'fa') return $source;
    $catalogue ??= json_decode((string)file_get_contents(__DIR__ . '/locales/static.json'), true, 512, JSON_THROW_ON_ERROR);
    $key = preg_replace('/\s+/u', ' ', html_entity_decode($source, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    return $catalogue[trim($key)][$locale] ?? $source;
}

function site_e(string $source): string { return htmlspecialchars(site_text($source), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/** Only pass literal, repository-owned markup, never rendered/fetched content. */
function site_static_markup(string $html): string
{
    if (site_locale()==='fa') return $html;
    $html = preg_replace_callback('~(^|>)([^<>]+)(?=<|$)~u', static function ($m) {
        if ($m[1] === '' && (str_contains($m[2], '>') || str_contains($m[2], 'class='))) return $m[0];
        preg_match('/^(\s*)(.*?)(\s*)$/su',$m[2],$parts);
        return $m[1].$parts[1].site_e($parts[2]).$parts[3];
    },$html);
    return preg_replace_callback('~\b(alt|title|placeholder|aria-label)=("|\')([^"\']*)\2~u', static fn($m) => $m[1].'='.$m[2].site_e($m[3]).$m[2],$html);
}

function site_js_text(string $source): string
{
    return json_encode(site_static_markup($source), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
}

function site_js_fragment(string $source): string
{
    return str_replace(['`','$'], ['\\`','\\u0024'], substr(site_js_text($source),1,-1));
}

function site_error_page(int $code = 404): void
{
    http_response_code($code);
    echo site_static_markup((string)file_get_contents(dirname(__DIR__).'/'.($code===403?'403':'404').'.html'));
}

function site_page_title(string $slug, string $fallback): string
{
    $names = [
        'home'=>'مکسا | مؤسسه نیکوکاری کنترل سرطان - مراقبت‌های حمایتی و تسکینی',
        'history'=>'تاریخچه و نحوه تاسیس', 'aboutus'=>'درباره ما', 'mission-vision'=>'ماموریت و چشم انداز',
        'association'=>'اساسنامه', 'organizationalchart'=>'چارت سازمانی', 'directors'=>'هیئت مدیره',
        'headdirectors'=>'شورای عالی', 'ceooffice'=>'مدیرعامل', 'ceo-office'=>'مدیرعامل',
        'medicalcare'=>'مراقبت های پزشکی تسکینی','nursecare'=>'مراقبت های پرستاری تسکینی',
        'physiotherapycare'=>'مراقبت های بازتوانی و توانبخشی','psychologicalcare'=>'مراقبت های روانشناختی',
        'spritualcare'=>'مراقبت های معنوی','nutritioncare'=>'مراقبت های تغذیه',
        'socialworkcare'=>'خدمات مددکاری اجتماعی','medicalequipments'=>'تأمین تجهیزات پزشکی',
        'genetic_conseuling'=>'مشاوره ژنتیک و غربالگری','end_of_life_care'=>'مراقبت‌های پایان زندگی',
        'supportiveandpalliativecare'=>'مراقبت‌های حمایتی و تسکینی',
        'supportiveandpalliativecareteam'=>'اعضای تیم مراقبت‌های حمایتی و تسکینی',
        'doctorspage'=>'کادر درمان و متخصصان مکسا','annual-reports'=>'گزارش‌های سالانه',
        'financial-reports'=>'صورت‌های مالی و گزارش‌های حسابرسی',
        'patientoption'=>'بیماران و مراقبین','patient-option'=>'بیماران و مراقبین',
    ];
    return site_text($names[strtolower($slug)] ?? $fallback);
}

/** Only navigable public pages receive a prefix; assets, APIs and panels do not. */
function site_url(string $url, ?string $locale = null): string
{
    $locale ??= site_locale();
    if (!in_array($locale, ['fa','en','ar'], true) || $url === '' || $url[0] === '#' || str_starts_with($url, '//')) return $url;
    $parts = parse_url($url);
    if ($parts === false || isset($parts['scheme']) && (!in_array($parts['scheme'], ['http','https'], true) || !in_array(strtolower($parts['host'] ?? ''), ['mymacsa.ir','www.mymacsa.ir'], true))) return $url;
    $path = $parts['path'] ?? '';
    if ($path === '' || $path[0] !== '/') return $url;
    $path = preg_replace('~^/(?:en|ar|fa)(?=/|$)~', '', $path) ?: '/';
    if (preg_match('~^/(?:dashboard|benefactor-dashboard|api|core|uploads|assets|webfont|docs|error pages)(?:/|$)|^/(?:api-|favicon|sitemap|robots)|\.(?:css|js|json|xml|txt|pdf|png|jpe?g|webp|svg|gif|woff2?|mp4|zip)$~i', $path)) return $url;
    $prefix = isset($parts['host']) ? $parts['scheme'].'://'.$parts['host'] : '';
    return $prefix . ($locale === 'fa' ? '' : '/'.$locale) . $path
        . (isset($parts['query']) ? '?'.$parts['query'] : '') . (isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
}

function site_language_picker(): string
{
    $current = defined('MACSA_PUBLIC_PATH') ? MACSA_PUBLIC_PATH : ($_SERVER['REQUEST_URI'] ?? '/home');
    $labels = ['fa'=>'فارسی','en'=>'English','ar'=>'العربية'];
    $html = '<details class="site-language"><summary aria-label="'.site_e('انتخاب زبان').'">'
        . '<span aria-hidden="true">'.strtoupper(site_locale()).'</span><span class="site-language-caret" aria-hidden="true">⌄</span></summary>'
        . '<nav class="site-language-options" aria-label="'.site_e('انتخاب زبان').'">';
    foreach ($labels as $code=>$label) {
        $html .= '<a href="'.htmlspecialchars(site_url($current, $code), ENT_QUOTES, 'UTF-8').'" lang="'.$code.'" dir="'.($code==='en'?'ltr':'rtl').'" hreflang="'.$code.'"'
            . ($code===site_locale() ? ' aria-current="true"' : '').'>'.$label.'</a>';
    }
    return $html.'</nav></details>';
}

/** Preserve all live content. This buffer changes document metadata and page links only. */
function site_document(string $html): string
{
    foreach (headers_list() as $header) {
        if (stripos($header, 'Location: ') === 0) header('Location: '.site_url(substr($header,10)));
        if (stripos($header, 'Content-Type:') === 0 && stripos($header, 'text/html') === false) return $html;
    }
    if (stripos($html, '<html') === false) return $html;
    $locale = site_locale(); $dir = site_direction();
    $html = preg_replace_callback('~<html\b[^>]*>~i', static function ($match) use ($locale,$dir) {
        $tag = preg_replace('~\s(?:lang|dir)\s*=\s*["\'][^"\']*["\']~i', '', $match[0]);
        return substr($tag,0,-1).' lang="'.$locale.'" dir="'.$dir.'">';
    }, $html);
    $html = preg_replace_callback('~<(?:a|link|form)\b[^>]*>~i', static function ($tag) {
        if (stripos($tag[0], 'hreflang=') !== false) return $tag[0];
        return preg_replace_callback('~\b(href|action)=("|\')([^"\']+)\2~i', static function ($match) {
            $url = html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return $match[1].'='.$match[2].htmlspecialchars(site_url($url),ENT_QUOTES,'UTF-8').$match[2];
        },$tag[0]);
    }, $html);
    $assetRoot=dirname(__DIR__).'/assets/';
    $assets = '<link rel="stylesheet" href="/assets/site-locale.css?v='.filemtime($assetRoot.'site-locale.css').'">'
        . '<script src="/assets/site-locale.js?v='.filemtime($assetRoot.'site-locale.js').'" defer></script>';
    if ($locale !== 'fa') {
        $html = preg_replace_callback('~(<link\s+rel=["\']canonical["\']\s+href=["\'])([^"\']+)(["\'][^>]*>)~i', static fn($m) => $m[1].site_url($m[2]).$m[3],$html);
        $html = preg_replace_callback('~(<meta\s+property=["\']og:url["\']\s+content=["\'])([^"\']+)(["\'][^>]*>)~i', static fn($m) => $m[1].site_url($m[2]).$m[3],$html);
    }
    return preg_replace('~</head>~i', $assets.'</head>', $html, 1);
}

if (PHP_SAPI !== 'cli' && !defined('MACSA_SITE_LOCALE_BUFFER')) {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/',PHP_URL_PATH) ?: '/';
    if (!preg_match('~^/(?:dashboard|api|benefactor-dashboard)(?:/|$)~', $path) || defined('MACSA_PUBLIC_PATH')) {
        define('MACSA_SITE_LOCALE_BUFFER', true);
        ob_start('site_document');
    }
}
