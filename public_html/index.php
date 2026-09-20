<?php

/**
 * Main entry point - Multi-language support
 * URL format: /fa/page-slug, /en/page-slug, /ar/page-slug
 */

require_once __DIR__ . "/core/database.php";
require_once __DIR__ . "/core/language.php";
require_once __DIR__ . "/core/translations.php";

// Start session for locale persistence
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect and set locale from URL
$locale = detectLocaleFromUrl();
setLocale($locale);

// Make locale available globally
define('CURRENT_LOCALE', $locale);
define('CURRENT_DIRECTION', getDirection($locale));

// Get current page slug (without locale prefix)
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$segments = array_filter(explode('/', trim($path, '/')));

// Remove locale from segments if present
if (!empty($segments) && in_array($segments[0], SUPPORTED_LOCALES, true)) {
    array_shift($segments);
}

$slug = !empty($segments) ? trim($segments[0]) : 'home';

// Handle special pages (keep existing logic)
if ($slug === 'under-construction') {
    include __DIR__ . "/under-construction.html";
    exit;
}

if ($slug === 'macsa-story') {
    include __DIR__ . "/macsa-story.php";
    exit;
}

if ($slug === 'network' || $slug === 'personal-resume-list') {
    include __DIR__ . "/network.php";
    exit;
}

if ($slug === 'stand-order' || $slug === 'stand-sell-section') {
    include __DIR__ . "/stand-order.php";
    exit;
}

if ($slug === 'branch-intro' || $slug === 'branch-about') {
    include __DIR__ . "/branch-intro.php";
    exit;
}

// Fetch page with translation support
$page = getPageBySlug($slug, $locale);

if (!$page) {
    http_response_code(404);
    include __DIR__ . "/404.html";
    exit;
}

// Set page meta for layout
$pageTitle = $page['trans_title'] ?? $page['title'] ?? __('meta.site_name');
$pageMetaDescription = $page['trans_meta_description'] ?? $page['meta_description'] ?? __('meta.site_description');
$pageMetaTitle = $page['trans_meta_title'] ?? $page['meta_title'] ?? $pageTitle;

// Parse components (with translation support)
$componentsJson = $page['components'] ?? '[]';
$components = json_decode($componentsJson, true);

if (!is_array($components)) {
    $components = [];
}

// Include layout header
include __DIR__ . "/layout/header.php";

// Render components
if (is_array($components)) {
    foreach ($components as $c) {
        // SECURITY: Validate component name to prevent path traversal
        if (!is_string($c) || !preg_match('/^[a-zA-Z0-9_-]+$/', $c)) {
            continue;
        }

        $base = __DIR__ . "/dashboard/components/$c/";

        // SECURITY: Prefer data.json (safe data-driven format) over component.php
        $dataFile = $base . 'data.json';
        if (file_exists($dataFile)) {
            $raw = file_get_contents($dataFile);
            $data = json_decode($raw, true);
            
            if (is_array($data) && isset($data['content'])) {
                // Handle multi-locale content format
                $content = $data['content'];
                
                if (is_array($content)) {
                    // New format: content is object with locale keys
                    $displayContent = $content[$locale] ?? $content[DEFAULT_LOCALE] ?? '';
                    // Fallback to first available string
                    if (!$displayContent) {
                        foreach ($content as $val) {
                            if (is_string($val)) {
                                $displayContent = $val;
                                break;
                            }
                        }
                    }
                } else {
                    // Old format: content is string (assume default locale)
                    $displayContent = is_string($content) ? $content : '';
                }
                
                if ($displayContent) {
                    echo $displayContent;
                    continue;
                }
            }
        }

        // Backward compatibility: fall back to component.php for existing components
        $phpFile = $base . 'component.php';
        if (file_exists($phpFile)) {
            include $phpFile;
        }
    }
}

// Include layout footer
include __DIR__ . "/layout/footer.php";