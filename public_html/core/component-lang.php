<?php
/**
 * Component Language Bootstrap
 * Include this at the top of any component that needs translated strings.
 * 
 * Usage in component.php:
 *   require_once __DIR__ . '/../../../core/component-lang.php';
 *   // Then use: echo __('key.subkey');
 */

// Only include once
if (!defined('COMPONENT_LANG_LOADED')) {
    define('COMPONENT_LANG_LOADED', true);
    
    require_once __DIR__ . '/language.php';
    require_once __DIR__ . '/translations.php';
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Ensure locale is set
    if (!defined('CURRENT_LOCALE')) {
        // Try to detect from page-view context
        $detected = $_GET['lang'] ?? $_SESSION['locale'] ?? DEFAULT_LOCALE;
        if (in_array($detected, SUPPORTED_LOCALES, true)) {
            setLocale($detected);
        }
        define('CURRENT_LOCALE', getLocale());
        define('CURRENT_DIRECTION', getDirection());
    }
}
