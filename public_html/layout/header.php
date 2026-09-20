<?php

/**
 * Layout Header - Multi-language support
 * Includes hreflang tags, language switcher, RTL/LTR support
 */

$locale = CURRENT_LOCALE ?? getLocale();
$direction = CURRENT_DIRECTION ?? getDirection($locale);
$htmlLang = getHtmlLang();
$htmlDir = getHtmlDir();
$canonicalUrl = getCanonicalUrl();
$alternateUrls = getAllLocaleUrls();

$pageTitle = $pageTitle ?? __('meta.site_name');
$pageMetaDescription = $pageMetaDescription ?? __('meta.site_description');
$pageMetaTitle = $pageMetaTitle ?? $pageTitle;

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($htmlLang) ?>" dir="<?= htmlspecialchars($htmlDir) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta -->
    <title><?= htmlspecialchars($pageMetaTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageMetaDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars(__('meta.keywords')) ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    
    <!-- Hreflang Alternates -->
    <?php foreach ($alternateUrls as $altLocale => $altUrl): ?>
        <?php if ($altLocale !== 'x-default'): ?>
    <link rel="alternate" hreflang="<?= htmlspecialchars($altLocale) ?>" href="<?= htmlspecialchars($altUrl) ?>">
        <?php endif; ?>
    <?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars($alternateUrls['x-default'] ?? $alternateUrls[DEFAULT_LOCALE]) ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($pageMetaTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageMetaDescription) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?= htmlspecialchars($locale) ?>">
    <?php foreach (SUPPORTED_LOCALES as $altLocale): ?>
        <meta property="og:locale:alternate" content="<?= htmlspecialchars($altLocale) ?>">
    <?php endforeach; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageMetaTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageMetaDescription) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="/font.css" rel="stylesheet">
    
    <!-- RTL/LTR specific styles -->
    <style>
        /* Direction-aware utilities */
        [dir="rtl"] { direction: rtl; text-align: right; }
        [dir="ltr"] { direction: ltr; text-align: left; }
        
        /* Logical properties for RTL support */
        .ms-auto { margin-inline-start: auto; }
        .me-auto { margin-inline-end: auto; }
        .ps-0 { padding-inline-start: 0; }
        .pe-0 { padding-inline-end: 0; }
        
        /* Language switcher styles */
        .lang-switcher { 
            display: inline-flex; 
            align-items: center; 
        }
        .lang-switcher__list { 
            display: flex; 
            gap: 0.5rem; 
            list-style: none; 
            margin: 0; 
            padding: 0; 
        }
        .lang-switcher__item a { 
            display: inline-flex; 
            align-items: center; 
            gap: 0.25rem; 
            padding: 0.375rem 0.75rem; 
            border-radius: 0.375rem; 
            text-decoration: none; 
            font-size: 0.875rem; 
            font-weight: 500; 
            transition: all 0.2s ease; 
        }
        .lang-switcher__item a:hover { 
            background-color: rgba(0,0,0,0.05); 
        }
        .lang-switcher__item--active a { 
            background-color: var(--primary-color, #2563eb); 
            color: white; 
        }
        
        /* Skip links for accessibility */
        .skip-link { 
            position: absolute; 
            top: -100%; 
            left: 1rem; 
            padding: 0.75rem 1rem; 
            background: var(--primary-color, #2563eb); 
            color: white; 
            z-index: 10000; 
            border-radius: 0 0 0.375rem 0.375rem; 
        }
        .skip-link:focus { 
            top: 0; 
        }
    </style>
    
    <!-- Theme color -->
    <meta name="theme-color" content="#2563eb">
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NonprofitOrganization",
        "name": "<?= htmlspecialchars(__('meta.site_name')) ?>",
        "description": "<?= htmlspecialchars(__('meta.site_description')) ?>",
        "url": "<?= htmlspecialchars($canonicalUrl) ?>",
        "logo": "<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . '/favicon.png') ?>",
        "sameAs": [
            "https://instagram.com/mahak_charity",
            "https://twitter.com/mahak_charity",
            "https://linkedin.com/company/mahak"
        ],
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "IR"
        }
    }
    </script>
</head>
<body data-locale="<?= htmlspecialchars($locale) ?>" data-direction="<?= htmlspecialchars($direction) ?>">

<!-- Skip Links (Accessibility) -->
<a href="#main-content" class="skip-link"><?= __('a11y.skip_to_content') ?></a>
<a href="#main-navigation" class="skip-link"><?= __('a11y.skip_to_navigation') ?></a>

<!-- Language Switcher (Top Right) -->
<div class="lang-switcher-container" style="position: fixed; top: 1rem; right: 1rem; z-index: 1000;">
    <?= renderLanguageSwitcher(['show_flags' => true, 'show_native_names' => true, 'class' => 'lang-switcher']) ?>
</div>

<!-- Header Component will be loaded here -->
<?php
// The header component should be included as a page component
// This file only provides the HTML head and opening body tags