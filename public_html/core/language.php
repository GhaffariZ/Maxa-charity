<?php

/**
 * Multi-Language Core Infrastructure
 * Handles language detection, locale management, and direction (RTL/LTR)
 */

// Supported languages configuration
const SUPPORTED_LOCALES = ['fa', 'en', 'ar'];
const DEFAULT_LOCALE = 'fa';

/**
 * Language metadata - single source of truth
 */
const LANGUAGE_META = [
    'fa' => [
        'code' => 'fa',
        'name_native' => 'فارسی',
        'name_en' => 'Persian',
        'direction' => 'rtl',
        'date_format' => 'Y/m/d',
        'number_locale' => 'fa-IR',
        'flag' => '🇮🇷',
    ],
    'en' => [
        'code' => 'en',
        'name_native' => 'English',
        'name_en' => 'English',
        'direction' => 'ltr',
        'date_format' => 'F j, Y',
        'number_locale' => 'en-US',
        'flag' => '🇺🇸',
    ],
    'ar' => [
        'code' => 'ar',
        'name_native' => 'العربية',
        'name_en' => 'Arabic',
        'direction' => 'rtl',
        'date_format' => 'Y/m/d',
        'number_locale' => 'ar-SA',
        'flag' => '🇸🇦',
    ],
];

/**
 * Detect locale from URL path
 * URL format: /fa/page-slug, /en/page-slug, /ar/page-slug
 */
function detectLocaleFromUrl(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $segments = array_filter(explode('/', trim($path, '/')));
    
    if (empty($segments)) {
        return DEFAULT_LOCALE;
    }
    
    $firstSegment = $segments[0] ?? '';
    if (in_array($firstSegment, SUPPORTED_LOCALES, true)) {
        return $firstSegment;
    }
    
    return DEFAULT_LOCALE;
}

/**
 * Get current locale (from session or detect)
 */
function getLocale(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['locale']) && in_array($_SESSION['locale'], SUPPORTED_LOCALES, true)) {
        return $_SESSION['locale'];
    }
    
    $locale = detectLocaleFromUrl();
    $_SESSION['locale'] = $locale;
    return $locale;
}

/**
 * Set locale and persist in session
 */
function setLocale(string $locale): bool
{
    if (!in_array($locale, SUPPORTED_LOCALES, true)) {
        return false;
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['locale'] = $locale;
    return true;
}

/**
 * Get language metadata
 */
function getLanguageMeta(string $locale = null): array
{
    $locale = $locale ?? getLocale();
    return LANGUAGE_META[$locale] ?? LANGUAGE_META[DEFAULT_LOCALE];
}

/**
 * Get current language direction (rtl/ltr)
 */
function getDirection(string $locale = null): string
{
    return getLanguageMeta($locale)['direction'];
}

/**
 * Check if current locale is RTL
 */
function isRTL(string $locale = null): bool
{
    return getDirection($locale) === 'rtl';
}

/**
 * Build localized URL
 * @param string $locale Target locale
 * @param string $path Path without locale prefix (e.g., "page-slug" or "news/article-id")
 * @param array $query Query parameters
 */
function buildLocalizedUrl(string $locale, string $path = '', array $query = []): string
{
    $baseUrl = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''), '/');
    
    $path = ltrim($path, '/');
    $url = "$baseUrl/$locale" . ($path ? "/$path" : '');
    
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }
    
    return $url;
}

/**
 * Get current page URL in a different locale
 */
function getCurrentUrlInLocale(string $targetLocale): string
{
    $currentLocale = getLocale();
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    
    // Remove current locale prefix if present
    $segments = array_filter(explode('/', trim($path, '/')));
    if (!empty($segments) && in_array($segments[0], SUPPORTED_LOCALES, true)) {
        array_shift($segments);
    }
    
    $pathWithoutLocale = implode('/', $segments);
    $query = $_GET;
    unset($query['lang']); // Remove lang param if exists
    
    return buildLocalizedUrl($targetLocale, $pathWithoutLocale, $query);
}

/**
 * Get all locale URLs for current page (for hreflang)
 */
function getAllLocaleUrls(): array
{
    $urls = [];
    foreach (SUPPORTED_LOCALES as $locale) {
        $urls[$locale] = getCurrentUrlInLocale($locale);
    }
    // x-default points to default locale
    $urls['x-default'] = getCurrentUrlInLocale(DEFAULT_LOCALE);
    return $urls;
}

/**
 * Get canonical URL (current locale)
 */
function getCanonicalUrl(): string
{
    return getCurrentUrlInLocale(getLocale());
}

/**
 * Format date according to locale
 */
function formatDateForLocale(\DateTimeInterface $date, string $locale = null): string
{
    $locale = $locale ?? getLocale();
    $format = LANGUAGE_META[$locale]['date_format'] ?? 'Y/m/d';
    return $date->format($format);
}

/**
 * Format number according to locale
 */
function formatNumberForLocale(float|int $number, int $decimals = 0, string $locale = null): string
{
    $locale = $locale ?? getLocale();
    $numberLocale = LANGUAGE_META[$locale]['number_locale'] ?? 'en-US';
    
    $formatter = new NumberFormatter($numberLocale, NumberFormatter::DECIMAL);
    $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
    $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
    
    return $formatter->format($number);
}

/**
 * Format currency according to locale (Iranian Rial)
 */
function formatCurrencyForLocale(float|int $amount, string $locale = null): string
{
    $locale = $locale ?? getLocale();
    $numberLocale = LANGUAGE_META[$locale]['number_locale'] ?? 'en-US';
    
    $formatter = new NumberFormatter($numberLocale, NumberFormatter::CURRENCY);
    return $formatter->formatCurrency($amount, 'IRR');
}

/**
 * Get HTML lang attribute value
 */
function getHtmlLang(): string
{
    return getLocale();
}

/**
 * Get HTML dir attribute value
 */
function getHtmlDir(): string
{
    return getDirection();
}

/**
 * Language switcher HTML (for components)
 */
function renderLanguageSwitcher(array $options = []): string
{
    $currentLocale = getLocale();
    $showFlags = $options['show_flags'] ?? true;
    $showNativeNames = $options['show_native_names'] ?? true;
    $class = $options['class'] ?? 'lang-switcher';
    
    $html = "<nav class=\"$class\" aria-label=\"Language selection\">\n";
    $html .= "  <ul class=\"lang-switcher__list\">\n";
    
    foreach (SUPPORTED_LOCALES as $locale) {
        $meta = LANGUAGE_META[$locale];
        $isActive = $locale === $currentLocale;
        $url = getCurrentUrlInLocale($locale);
        
        $label = $showNativeNames ? $meta['name_native'] : $meta['name_en'];
        if ($showFlags) {
            $label = $meta['flag'] . ' ' . $label;
        }
        
        $activeClass = $isActive ? ' lang-switcher__item--active' : '';
        $ariaCurrent = $isActive ? ' aria-current="page"' : '';
        
        $html .= "    <li class=\"lang-switcher__item$activeClass\">\n";
        $html .= "      <a href=\"$url\" hreflang=\"$locale\"$ariaCurrent>$label</a>\n";
        $html .= "    </li>\n";
    }
    
    $html .= "  </ul>\n";
    $html .= "</nav>\n";
    
    return $html;
}

/**
 * Redirect to localized version of current page
 */
function redirectToLocale(string $locale): never
{
    if (!in_array($locale, SUPPORTED_LOCALES, true)) {
        $locale = DEFAULT_LOCALE;
    }
    
    setLocale($locale);
    $url = getCurrentUrlInLocale($locale);
    header("Location: $url", true, 302);
    exit;
}