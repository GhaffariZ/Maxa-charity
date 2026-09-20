<?php

/**
 * Language Switcher Component
 * Renders a language selector with locale-aware URLs
 */

require_once __DIR__ . '/../../../core/language.php';
require_once __DIR__ . '/../../../core/translations.php';

// Get settings from data.json or use defaults
$dataFile = __DIR__ . '/data.json';
$settings = [
    'show_flags' => true,
    'show_native_names' => true,
    'style' => 'inline',
    'position' => 'top-right',
];

if (file_exists($dataFile)) {
    $raw = file_get_contents($dataFile);
    $data = json_decode($raw, true);
    if (is_array($data) && isset($data['settings'])) {
        $settings = array_merge($settings, $data['settings']);
    }
}

$locale = CURRENT_LOCALE ?? getLocale();
$direction = CURRENT_DIRECTION ?? getDirection($locale);
$alternateUrls = getAllLocaleUrls();

$showFlags = $settings['show_flags'] ?? true;
$showNativeNames = $settings['show_native_names'] ?? true;
$style = $settings['style'] ?? 'inline';
$position = $settings['position'] ?? 'top-right';

// Position classes
$positionClasses = [
    'top-right' => 'lang-switcher--fixed lang-switcher--top-right',
    'top-left' => 'lang-switcher--fixed lang-switcher--top-left',
    'header' => 'lang-switcher--header',
    'footer' => 'lang-switcher--footer',
];

$positionClass = $positionClasses[$position] ?? '';

// Style classes
$styleClasses = [
    'dropdown' => 'lang-switcher--dropdown',
    'inline' => 'lang-switcher--inline',
    'pills' => 'lang-switcher--pills',
];

$styleClass = $styleClasses[$style] ?? 'lang-switcher--inline';

// Build language options
$langOptions = [];
foreach (SUPPORTED_LOCALES as $loc) {
    $meta = LANGUAGE_META[$loc];
    $label = $showNativeNames ? $meta['name_native'] : $meta['name_en'];
    if ($showFlags) {
        $label = $meta['flag'] . ' ' . $label;
    }
    
    $langOptions[] = [
        'code' => $loc,
        'label' => $label,
        'url' => $alternateUrls[$loc] ?? '#',
        'active' => $loc === $locale,
        'direction' => $meta['direction'],
        'native_name' => $meta['name_native'],
        'english_name' => $meta['name_en'],
    ];
}
?>

<!-- Language Switcher Component -->
<nav 
    class="lang-switcher <?= htmlspecialchars($positionClass . ' ' . $styleClass) ?>" 
    aria-label="<?= htmlspecialchars(__('a11y.select_language')) ?>"
    data-locale="<?= htmlspecialchars($locale) ?>"
    data-direction="<?= htmlspecialchars($direction) ?>"
>
    <?php if ($style === 'dropdown'): ?>
        <!-- Dropdown Style -->
        <div class="lang-switcher__dropdown" role="combobox" aria-expanded="false" aria-haspopup="listbox">
            <button 
                class="lang-switcher__trigger" 
                type="button"
                aria-label="<?= htmlspecialchars(sprintf(__('a11y.current_language'), $langOptions[array_search($locale, array_column($langOptions, 'code'))]['label'] ?? $locale)) ?>"
            >
                <span class="lang-switcher__current">
                    <?php 
                    $current = array_filter($langOptions, fn($l) => $l['active']);
                    $current = array_values($current);
                    if ($current): 
                        echo htmlspecialchars($current[0]['label']);
                    endif; 
                    ?>
                </span>
                <svg class="lang-switcher__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </button>
            
            <ul class="lang-switcher__options" role="listbox" aria-label="<?= htmlspecialchars(__('a11y.select_language')) ?>">
                <?php foreach ($langOptions as $option): ?>
                    <li role="option" aria-selected="<?= $option['active'] ? 'true' : 'false' ?>">
                        <a 
                            href="<?= htmlspecialchars($option['url']) ?>" 
                            hreflang="<?= htmlspecialchars($option['code']) ?>"
                            class="lang-switcher__option <?= $option['active'] ? 'lang-switcher__option--active' : '' ?>"
                            data-locale="<?= htmlspecialchars($option['code']) ?>"
                        >
                            <span class="lang-switcher__option-label"><?= htmlspecialchars($option['label']) ?></span>
                            <?php if ($option['active']): ?>
                                <svg class="lang-switcher__check" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
    <?php elseif ($style === 'pills'): ?>
        <!-- Pill Buttons Style -->
        <div class="lang-switcher__pills" role="group" aria-label="<?= htmlspecialchars(__('a11y.select_language')) ?>">
            <?php foreach ($langOptions as $option): ?>
                <a 
                    href="<?= htmlspecialchars($option['url']) ?>" 
                    hreflang="<?= htmlspecialchars($option['code']) ?>"
                    class="lang-switcher__pill <?= $option['active'] ? 'lang-switcher__pill--active' : '' ?>"
                    data-locale="<?= htmlspecialchars($option['code']) ?>"
                    <?= $option['active'] ? 'aria-current="page"' : '' ?>
                >
                    <?= htmlspecialchars($option['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        
    <?php else: ?>
        <!-- Inline Links Style (default) -->
        <ul class="lang-switcher__list" role="list" aria-label="<?= htmlspecialchars(__('a11y.select_language')) ?>">
            <?php foreach ($langOptions as $option): ?>
                <li class="lang-switcher__item">
                    <a 
                        href="<?= htmlspecialchars($option['url']) ?>" 
                        hreflang="<?= htmlspecialchars($option['code']) ?>"
                        class="lang-switcher__link <?= $option['active'] ? 'lang-switcher__link--active' : '' ?>"
                        data-locale="<?= htmlspecialchars($option['code']) ?>"
                        <?= $option['active'] ? 'aria-current="page"' : '' ?>
                    >
                        <?= htmlspecialchars($option['label']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</nav>

<!-- Component Styles -->
<style>
/* Language Switcher Base */
.lang-switcher {
    font-family: inherit;
    --ls-bg: var(--ls-bg, rgba(255,255,255,0.1));
    --ls-border: var(--ls-border, rgba(255,255,255,0.2));
    --ls-text: var(--ls-text, inherit);
    --ls-active-bg: var(--ls-active-bg, var(--primary-color, #2563eb));
    --ls-active-text: var(--ls-active-text, #fff);
    --ls-hover-bg: var(--ls-hover-bg, rgba(255,255,255,0.15));
    --ls-radius: var(--ls-radius, 0.5rem);
    --ls-transition: var(--ls-transition, 0.2s ease);
}

/* Fixed Position Variants */
.lang-switcher--fixed {
    position: fixed;
    z-index: 1000;
    padding: 0.5rem;
}

.lang-switcher--top-right {
    top: 1rem;
    right: 1rem;
}

.lang-switcher--top-left {
    top: 1rem;
    left: 1rem;
}

/* Inline Style (default) */
.lang-switcher--inline .lang-switcher__list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.lang-switcher--inline .lang-switcher__item {
    margin: 0;
}

.lang-switcher--inline .lang-switcher__link {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    border-radius: var(--ls-radius);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--ls-text);
    background: var(--ls-bg);
    border: 1px solid var(--ls-border);
    transition: all var(--ls-transition);
    white-space: nowrap;
}

.lang-switcher--inline .lang-switcher__link:hover {
    background: var(--ls-hover-bg);
    transform: translateY(-1px);
}

.lang-switcher--inline .lang-switcher__link--active {
    background: var(--ls-active-bg);
    color: var(--ls-active-text);
    border-color: var(--ls-active-bg);
}

/* Pill Style */
.lang-switcher--pills .lang-switcher__pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
}

.lang-switcher--pills .lang-switcher__pill {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--ls-text);
    background: var(--ls-bg);
    border: 1px solid var(--ls-border);
    transition: all var(--ls-transition);
    white-space: nowrap;
}

.lang-switcher--pills .lang-switcher__pill:hover {
    background: var(--ls-hover-bg);
    transform: translateY(-1px);
}

.lang-switcher--pills .lang-switcher__pill--active {
    background: var(--ls-active-bg);
    color: var(--ls-active-text);
    border-color: var(--ls-active-bg);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

/* Dropdown Style */
.lang-switcher--dropdown .lang-switcher__dropdown {
    position: relative;
    display: inline-block;
}

.lang-switcher--dropdown .lang-switcher__trigger {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--ls-radius);
    border: 1px solid var(--ls-border);
    background: var(--ls-bg);
    color: var(--ls-text);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all var(--ls-transition);
    white-space: nowrap;
}

.lang-switcher--dropdown .lang-switcher__trigger:hover {
    background: var(--ls-hover-bg);
}

.lang-switcher--dropdown .lang-switcher__trigger:focus {
    outline: 2px solid var(--primary-color, #2563eb);
    outline-offset: 2px;
}

.lang-switcher--dropdown .lang-switcher__chevron {
    flex-shrink: 0;
    transition: transform var(--ls-transition);
}

.lang-switcher--dropdown .lang-switcher__dropdown[aria-expanded="true"] .lang-switcher__chevron {
    transform: rotate(180deg);
}

.lang-switcher--dropdown .lang-switcher__options {
    position: absolute;
    top: calc(100% + 0.375rem);
    right: 0;
    min-width: 160px;
    padding: 0.375rem;
    list-style: none;
    margin: 0;
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: var(--ls-radius);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: all var(--ls-transition);
    z-index: 1001;
}

.lang-switcher--dropdown .lang-switcher__dropdown[aria-expanded="true"] .lang-switcher__options {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.lang-switcher--dropdown .lang-switcher__option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: none;
    background: transparent;
    color: var(--text-primary, #1f2937);
    font-size: 0.875rem;
    font-weight: 500;
    text-align: left;
    cursor: pointer;
    border-radius: calc(var(--ls-radius) - 0.125rem);
    transition: background var(--ls-transition);
}

.lang-switcher--dropdown .lang-switcher__option:hover {
    background: var(--ls-hover-bg);
}

.lang-switcher--dropdown .lang-switcher__option--active {
    background: var(--ls-active-bg);
    color: var(--ls-active-text);
}

.lang-switcher--dropdown .lang-switcher__check {
    flex-shrink: 0;
    color: inherit;
}

/* RTL Support */
[dir="rtl"] .lang-switcher--inline .lang-switcher__list {
    flex-direction: row-reverse;
}

[dir="rtl"] .lang-switcher--pills .lang-switcher__pills {
    flex-direction: row-reverse;
}

[dir="rtl"] .lang-switcher--dropdown .lang-switcher__options {
    right: auto;
    left: 0;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .lang-switcher--dropdown .lang-switcher__options {
        background: var(--card-bg-dark, #1f2937);
        border-color: var(--border-color-dark, #374151);
    }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .lang-switcher * {
        transition: none !important;
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .lang-switcher--inline .lang-switcher__link,
    .lang-switcher--pills .lang-switcher__pill {
        border-width: 2px;
    }
}
</style>

<!-- Dropdown JavaScript -->
<?php if ($style === 'dropdown'): ?>
<script>
(function() {
    'use strict';
    
    const dropdown = document.querySelector('.lang-switcher__dropdown');
    if (!dropdown) return;
    
    const trigger = dropdown.querySelector('.lang-switcher__trigger');
    const options = dropdown.querySelector('.lang-switcher__options');
    
    if (!trigger || !options) return;
    
    // Toggle dropdown
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        const expanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !expanded);
    });
    
    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            trigger.setAttribute('aria-expanded', 'false');
        }
    });
    
    // Keyboard navigation
    options.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            trigger.setAttribute('aria-expanded', 'false');
            trigger.focus();
        }
    });
    
    // Handle option selection
    options.querySelectorAll('.lang-switcher__option').forEach(option => {
        option.addEventListener('click', function(e) {
            // Allow default link behavior
            trigger.setAttribute('aria-expanded', 'false');
        });
    });
})();
</script>
<?php endif; ?>