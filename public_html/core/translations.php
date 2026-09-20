<?php

/**
 * Translation Helpers
 * Handles database translations (content entities) and static translations (UI strings)
 */

require_once __DIR__ . '/language.php';
require_once __DIR__ . '/database.php';

/**
 * Get database connection
 */
function getDB(): PDO
{
    return $GLOBALS['pdo'] ??= db();
}

/**
 * Cache for translations to avoid repeated DB queries
 */
$translationCache = [];

/**
 * Get translated entity content (pages, campaigns, news, etc.)
 * Falls back to default locale if translation not found
 */
function getTranslatedEntity(
    string $entityType,
    int $entityId,
    string $locale = null,
    array $fields = ['*']
): ?array
{
    global $translationCache;
    
    $locale = $locale ?? getLocale();
    $cacheKey = "$entityType:$entityId:$locale";
    
    if (isset($translationCache[$cacheKey])) {
        return $translationCache[$cacheKey];
    }
    
    $tableMap = [
        'page' => 'page_translations',
        'campaign' => 'campaign_translations',
        'news' => 'news_translations',
        'hero_slide' => 'hero_slide_translations',
        'course' => 'course_translations',
        'employee' => 'employee_profile_translations',
    ];
    
    $table = $tableMap[$entityType] ?? null;
    if (!$table) {
        return null;
    }
    
    $fieldList = implode(', ', array_map(fn($f) => "`$f`", $fields));
    
    // Try requested locale first, then fallback to default
    $sql = "
        SELECT $fieldList FROM `$table` 
        WHERE entity_id = ? AND locale = ?
        UNION ALL
        SELECT $fieldList FROM `$table` 
        WHERE entity_id = ? AND locale = ?
        LIMIT 1
    ";
    
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$entityId, $locale, $entityId, DEFAULT_LOCALE]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $translationCache[$cacheKey] = $result ?: null;
    return $translationCache[$cacheKey];
}

/**
 * Get translated page data with components
 */
function getTranslatedPage(int $pageId, string $locale = null): ?array
{
    $locale = $locale ?? getLocale();
    
    $sql = "
        SELECT 
            p.*,
            pt.title AS trans_title,
            pt.slug AS trans_slug,
            pt.meta_title AS trans_meta_title,
            pt.meta_description AS trans_meta_description,
            pt.components AS trans_components
        FROM pages p
        LEFT JOIN page_translations pt ON p.id = pt.page_id AND pt.locale = ?
        WHERE p.id = ?
    ";
    
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$locale, $pageId]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$page) {
        return null;
    }
    
    // Fallback to default locale if translation missing
    if (!$page['trans_title'] && $locale !== DEFAULT_LOCALE) {
        $stmt->execute([DEFAULT_LOCALE, $pageId]);
        $fallback = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fallback) {
            $page['trans_title'] = $fallback['trans_title'];
            $page['trans_slug'] = $fallback['trans_slug'];
            $page['trans_meta_title'] = $fallback['trans_meta_title'];
            $page['trans_meta_description'] = $fallback['trans_meta_description'];
            $page['trans_components'] = $fallback['trans_components'];
        }
    }
    
    // Use translated components if available
    if ($page['trans_components']) {
        $page['components'] = $page['trans_components'];
    }
    
    return $page;
}

/**
 * Get page by slug with translation
 */
function getPageBySlug(string $slug, string $locale = null): ?array
{
    $locale = $locale ?? getLocale();
    
    $sql = "
        SELECT 
            p.*,
            pt.title AS trans_title,
            pt.slug AS trans_slug,
            pt.meta_title AS trans_meta_title,
            pt.meta_description AS trans_meta_description,
            pt.components AS trans_components
        FROM pages p
        LEFT JOIN page_translations pt ON p.id = pt.page_id AND pt.locale = ?
        WHERE p.slug = ? AND p.status = 'published'
    ";
    
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$locale, $slug]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$page) {
        return null;
    }
    
    // Fallback to default locale
    if (!$page['trans_title'] && $locale !== DEFAULT_LOCALE) {
        $stmt->execute([DEFAULT_LOCALE, $slug]);
        $fallback = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fallback) {
            $page['trans_title'] = $fallback['trans_title'];
            $page['trans_slug'] = $fallback['trans_slug'];
            $page['trans_meta_title'] = $fallback['trans_meta_title'];
            $page['trans_meta_description'] = $fallback['trans_meta_description'];
            $page['trans_components'] = $fallback['trans_components'];
        }
    }
    
    if ($page['trans_components']) {
        $page['components'] = $page['trans_components'];
    }
    
    return $page;
}

/**
 * Get translated campaign
 */
function getTranslatedCampaign(int $campaignId, string $locale = null): ?array
{
    $locale = $locale ?? getLocale();
    
    $sql = "
        SELECT 
            c.*,
            ct.title AS trans_title,
            ct.short_description AS trans_short_description,
            ct.description AS trans_description
        FROM campaigns c
        LEFT JOIN campaign_translations ct ON c.id = ct.campaign_id AND ct.locale = ?
        WHERE c.id = ?
    ";
    
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$locale, $campaignId]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$campaign) {
        return null;
    }
    
    if (!$campaign['trans_title'] && $locale !== DEFAULT_LOCALE) {
        $stmt->execute([DEFAULT_LOCALE, $campaignId]);
        $fallback = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fallback) {
            $campaign['trans_title'] = $fallback['trans_title'];
            $campaign['trans_short_description'] = $fallback['trans_short_description'];
            $campaign['trans_description'] = $fallback['trans_description'];
        }
    }
    
    return $campaign;
}

/**
 * Get all campaigns with translations for current locale
 */
function getAllTranslatedCampaigns(string $locale = null, bool $activeOnly = true): array
{
    $locale = $locale ?? getLocale();
    
    $where = $activeOnly ? 'WHERE c.is_active = 1' : '';
    
    $sql = "
        SELECT 
            c.*,
            ct.title AS trans_title,
            ct.short_description AS trans_short_description,
            ct.description AS trans_description
        FROM campaigns c
        LEFT JOIN campaign_translations ct ON c.id = ct.campaign_id AND ct.locale = ?
        $where
        ORDER BY c.sort_order, c.id
    ";
    
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$locale]);
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fill missing translations with default locale
    if ($locale !== DEFAULT_LOCALE) {
        foreach ($campaigns as &$campaign) {
            if (!$campaign['trans_title']) {
                $stmt = getDB()->prepare("
                    SELECT title, short_description, description 
                    FROM campaign_translations 
                    WHERE campaign_id = ? AND locale = ?
                ");
                $stmt->execute([$campaign['id'], DEFAULT_LOCALE]);
                $fallback = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($fallback) {
                    $campaign['trans_title'] = $fallback['title'];
                    $campaign['trans_short_description'] = $fallback['short_description'];
                    $campaign['trans_description'] = $fallback['description'];
                }
            }
        }
    }
    
    return $campaigns;
}

/**
 * Get translated news article
 */
function getTranslatedNews(int $newsId, string $locale = null): ?array
{
    $locale = $locale ?? getLocale();
    
    $sql = "
        SELECT 
            n.*,
            nt.title AS trans_title,
            nt.summary AS trans_summary,
            nt.content AS trans_content,
            nt.meta_title AS trans_meta_title,
            nt.meta_description AS trans_meta_description
        FROM news n
        LEFT JOIN news_translations nt ON n.id = nt.news_id AND nt.locale = ?
        WHERE n.id = ?
    ";
    
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$locale, $newsId]);
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$news) {
        return null;
    }
    
    if (!$news['trans_title'] && $locale !== DEFAULT_LOCALE) {
        $stmt->execute([DEFAULT_LOCALE, $newsId]);
        $fallback = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fallback) {
            $news['trans_title'] = $fallback['trans_title'];
            $news['trans_summary'] = $fallback['trans_summary'];
            $news['trans_content'] = $fallback['trans_content'];
            $news['trans_meta_title'] = $fallback['trans_meta_title'];
            $news['trans_meta_description'] = $fallback['trans_meta_description'];
        }
    }
    
    return $news;
}

/**
 * Get all news with translations
 */
function getAllTranslatedNews(string $locale = null, int $limit = 10, int $offset = 0): array
{
    $locale = $locale ?? getLocale();
    
    $sql = "
        SELECT 
            n.*,
            nt.title AS trans_title,
            nt.summary AS trans_summary,
            nt.content AS trans_content,
            nt.meta_title AS trans_meta_title,
            nt.meta_description AS trans_meta_description
        FROM news n
        LEFT JOIN news_translations nt ON n.id = nt.news_id AND nt.locale = ?
        WHERE n.status = 'published'
        ORDER BY n.publish_date DESC, n.id DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$locale, $limit, $offset]);
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($locale !== DEFAULT_LOCALE) {
        foreach ($news as &$item) {
            if (!$item['trans_title']) {
                $stmt = getDB()->prepare("
                    SELECT title, summary, content, meta_title, meta_description 
                    FROM news_translations 
                    WHERE news_id = ? AND locale = ?
                ");
                $stmt->execute([$item['id'], DEFAULT_LOCALE]);
                $fallback = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($fallback) {
                    $item['trans_title'] = $fallback['title'];
                    $item['trans_summary'] = $fallback['summary'];
                    $item['trans_content'] = $fallback['content'];
                    $item['trans_meta_title'] = $fallback['meta_title'];
                    $item['trans_meta_description'] = $fallback['meta_description'];
                }
            }
        }
    }
    
    return $news;
}

/**
 * Get translated hero slide
 */
function getTranslatedHeroSlide(int $slideId, string $locale = null): ?array
{
    $locale = $locale ?? getLocale();
    
    $sql = "
        SELECT 
            h.*,
            ht.title AS trans_title,
            ht.description AS trans_description,
            ht.button_text AS trans_button_text,
            ht.button_link AS trans_button_link
        FROM hero_slides h
        LEFT JOIN hero_slide_translations ht ON h.id = ht.hero_slide_id AND ht.locale = ?
        WHERE h.id = ?
    ";
    
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$locale, $slideId]);
    $slide = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$slide) {
        return null;
    }
    
    if (!$slide['trans_title'] && $locale !== DEFAULT_LOCALE) {
        $stmt->execute([DEFAULT_LOCALE, $slideId]);
        $fallback = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fallback) {
            $slide['trans_title'] = $fallback['trans_title'];
            $slide['trans_description'] = $fallback['trans_description'];
            $slide['trans_button_text'] = $fallback['trans_button_text'];
            $slide['trans_button_link'] = $fallback['trans_button_link'];
        }
    }
    
    return $slide;
}

/**
 * Get all active hero slides with translations
 */
function getActiveHeroSlides(string $locale = null): array
{
    $locale = $locale ?? getLocale();
    
    $sql = "
        SELECT 
            h.*,
            ht.title AS trans_title,
            ht.description AS trans_description,
            ht.button_text AS trans_button_text,
            ht.button_link AS trans_button_link
        FROM hero_slides h
        LEFT JOIN hero_slide_translations ht ON h.id = ht.hero_slide_id AND ht.locale = ?
        WHERE h.status = 1
        ORDER BY h.sort_order, h.id
    ";
    
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$locale]);
    $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($locale !== DEFAULT_LOCALE) {
        foreach ($slides as &$slide) {
            if (!$slide['trans_title']) {
                $stmt = getDB()->prepare("
                    SELECT title, description, button_text, button_link 
                    FROM hero_slide_translations 
                    WHERE hero_slide_id = ? AND locale = ?
                ");
                $stmt->execute([$slide['id'], DEFAULT_LOCALE]);
                $fallback = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($fallback) {
                    $slide['trans_title'] = $fallback['title'];
                    $slide['trans_description'] = $fallback['description'];
                    $slide['trans_button_text'] = $fallback['button_text'];
                    $slide['trans_button_link'] = $fallback['button_link'];
                }
            }
        }
    }
    
    return $slides;
}

/**
 * Get translated employee profile
 */
function getTranslatedEmployee(int $employeeId, string $locale = null): ?array
{
    $locale = $locale ?? getLocale();
    
    $sql = "
        SELECT 
            e.*,
            et.role AS trans_role,
            et.bio_professional AS trans_bio_professional,
            et.academic_background AS trans_academic_background,
            et.maxa_responsibilities AS trans_maxa_responsibilities
        FROM employee_profiles e
        LEFT JOIN employee_profile_translations et ON e.id = et.employee_id AND et.locale = ?
        WHERE e.id = ?
    ";
    
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$locale, $employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        return null;
    }
    
    if (!$employee['trans_role'] && $locale !== DEFAULT_LOCALE) {
        $stmt->execute([DEFAULT_LOCALE, $employeeId]);
        $fallback = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fallback) {
            $employee['trans_role'] = $fallback['trans_role'];
            $employee['trans_bio_professional'] = $fallback['trans_bio_professional'];
            $employee['trans_academic_background'] = $fallback['trans_academic_background'];
            $employee['trans_maxa_responsibilities'] = $fallback['trans_maxa_responsibilities'];
        }
    }
    
    return $employee;
}

/**
 * Static translations (UI strings) - key-value store
 */
function getStaticTranslation(string $key, string $locale = null, string $context = null): ?string
{
    $locale = $locale ?? getLocale();
    
    try {
        $sql = "
            SELECT value FROM static_translations 
            WHERE translation_key = ? AND locale = ?
            UNION ALL
            SELECT value FROM static_translations 
            WHERE translation_key = ? AND locale = ?
            LIMIT 1
        ";
        
        $stmt = getDB()->prepare($sql);
        $stmt->execute([$key, $locale, $key, DEFAULT_LOCALE]);
        $result = $stmt->fetchColumn();
        
        return $result ?: null;
    } catch (Throwable $e) {
        // Table may not exist yet — fall through to file-based translations
        return null;
    }
}

/**
 * Translation function for UI strings (like WordPress __())
 * Usage: __('key', 'context') or __('key')
 */
function __($key, $context = null): string
{
    $translation = getStaticTranslation($key, getLocale(), $context);
    
    if ($translation !== null) {
        return $translation;
    }
    
    // Fallback: try to get from PHP language files
    $fileTranslation = getFileTranslation($key, getLocale());
    if ($fileTranslation !== null) {
        return $fileTranslation;
    }
    
    // Ultimate fallback: return the key itself
    return $key;
}

/**
 * Translation with printf-style formatting
 * Usage: __f('key', 'arg1', 'arg2') or __f('key', ['arg1', 'arg2'])
 */
function __f(string $key, ...$args): string
{
    $translation = __($key);
    
    if (count($args) === 1 && is_array($args[0])) {
        $args = $args[0];
    }
    
    if (!empty($args)) {
        return vsprintf($translation, $args);
    }
    
    return $translation;
}

/**
 * Get translation from PHP language files (fallback)
 */
function getFileTranslation(string $key, string $locale): ?string
{
    static $fileCache = [];
    
    if (!isset($fileCache[$locale])) {
        $file = __DIR__ . "/../lang/{$locale}.php";
        if (file_exists($file)) {
            $fileCache[$locale] = require $file;
        } else {
            $fileCache[$locale] = [];
        }
    }
    
    // Support nested keys like 'nav.home'
    $keys = explode('.', $key);
    $value = $fileCache[$locale];
    
    foreach ($keys as $k) {
        if (is_array($value) && array_key_exists($k, $value)) {
            $value = $value[$k];
        } else {
            return null;
        }
    }
    
    return is_string($value) ? $value : null;
}

/**
 * Get component content with locale support
 * Handles both old format (string) and new format (object with locales)
 */
function getComponentContent(string $componentName, string $locale = null): ?string
{
    $locale = $locale ?? getLocale();
    $basePath = __DIR__ . "/../dashboard/components/$componentName/";
    $dataFile = $basePath . 'data.json';
    
    if (!file_exists($dataFile)) {
        return null;
    }
    
    $raw = file_get_contents($dataFile);
    $data = json_decode($raw, true);
    
    if (!is_array($data) || !isset($data['content'])) {
        return null;
    }
    
    $content = $data['content'];
    
    // New format: content is an object with locale keys
    if (is_array($content)) {
        // Try requested locale
        if (isset($content[$locale]) && is_string($content[$locale])) {
            return $content[$locale];
        }
        
        // Fallback to default locale
        if (isset($content[DEFAULT_LOCALE]) && is_string($content[DEFAULT_LOCALE])) {
            return $content[DEFAULT_LOCALE];
        }
        
        // Return first available
        foreach ($content as $val) {
            if (is_string($val)) {
                return $val;
            }
        }
        
        return null;
    }
    
    // Old format: content is a string (assume it's in default locale)
    return is_string($content) ? $content : null;
}

/**
 * Save component translation (for dashboard)
 */
function saveComponentTranslation(string $componentName, string $locale, string $content): bool
{
    // Store in component_translations table
    $sql = "
        INSERT INTO component_translations (component_name, locale, content)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE content = VALUES(content), updated_at = CURRENT_TIMESTAMP
    ";
    
    $stmt = getDB()->prepare($sql);
    return $stmt->execute([$componentName, $locale, json_encode(['content' => $content])]);
}

/**
 * Get component translation from database
 */
function getComponentTranslation(string $componentName, string $locale = null): ?string
{
    $locale = $locale ?? getLocale();
    
    try {
        $sql = "SELECT content FROM component_translations WHERE component_name = ? AND locale = ?";
        $stmt = getDB()->prepare($sql);
        $stmt->execute([$componentName, $locale]);
        $result = $stmt->fetchColumn();
        
        if ($result) {
            $data = json_decode($result, true);
            return $data['content'] ?? null;
        }
        
        // Fallback to default locale
        if ($locale !== DEFAULT_LOCALE) {
            $stmt->execute([$componentName, DEFAULT_LOCALE]);
            $result = $stmt->fetchColumn();
            if ($result) {
                $data = json_decode($result, true);
                return $data['content'] ?? null;
            }
        }
    } catch (Throwable $e) {
        // Table may not exist yet
    }
    
    return null;
}

/**
 * Save entity translation (generic)
 */
function saveEntityTranslation(
    string $entityType,
    int $entityId,
    string $locale,
    array $fields
): bool {
    $tableMap = [
        'page' => 'page_translations',
        'campaign' => 'campaign_translations',
        'news' => 'news_translations',
        'hero_slide' => 'hero_slide_translations',
        'course' => 'course_translations',
        'employee' => 'employee_profile_translations',
    ];
    
    $table = $tableMap[$entityType] ?? null;
    if (!$table) {
        return false;
    }
    
    $idField = $entityType . '_id';
    $columns = array_keys($fields);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $columnList = implode(', ', array_map(fn($c) => "`$c`", $columns));
    $updateList = implode(', ', array_map(fn($c) => "`$c` = VALUES(`$c`)", $columns));
    
    $sql = "
        INSERT INTO `$table` (`$idField`, `locale`, $columnList)
        VALUES (?, ?, $placeholders)
        ON DUPLICATE KEY UPDATE $updateList, updated_at = CURRENT_TIMESTAMP
    ";
    
    $values = array_merge([$entityId, $locale], array_values($fields));
    $stmt = getDB()->prepare($sql);
    return $stmt->execute($values);
}

/**
 * Delete entity translation
 */
function deleteEntityTranslation(string $entityType, int $entityId, string $locale): bool
{
    $tableMap = [
        'page' => 'page_translations',
        'campaign' => 'campaign_translations',
        'news' => 'news_translations',
        'hero_slide' => 'hero_slide_translations',
        'course' => 'course_translations',
        'employee' => 'employee_profile_translations',
    ];
    
    $table = $tableMap[$entityType] ?? null;
    if (!$table) {
        return false;
    }
    
    $idField = $entityType . '_id';
    $sql = "DELETE FROM `$table` WHERE `$idField` = ? AND `locale` = ?";
    $stmt = getDB()->prepare($sql);
    return $stmt->execute([$entityId, $locale]);
}

/**
 * Get all translations for an entity (for dashboard language tabs)
 */
function getAllEntityTranslations(string $entityType, int $entityId): array
{
    $tableMap = [
        'page' => 'page_translations',
        'campaign' => 'campaign_translations',
        'news' => 'news_translations',
        'hero_slide' => 'hero_slide_translations',
        'course' => 'course_translations',
        'employee' => 'employee_profile_translations',
    ];
    
    $table = $tableMap[$entityType] ?? null;
    if (!$table) {
        return [];
    }
    
    $idField = $entityType . '_id';
    $sql = "SELECT * FROM `$table` WHERE `$idField` = ?";
    $stmt = getDB()->prepare($sql);
    $stmt->execute([$entityId]);
    
    $translations = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $translations[$row['locale']] = $row;
    }
    
    return $translations;
}

/**
 * Clear translation cache
 */
function clearTranslationCache(): void
{
    global $translationCache;
    $translationCache = [];
}