<?php

/**
 * SECURITY: Safely render a component by name.
 * Checks for data.json first (new safe format), falls back to component.php
 * for backward compatibility with existing components.
 *
 * @param string $name The component folder name (alphanumeric, hyphens, underscores only)
 */
function component($name) {
    // SECURITY: Validate component name to prevent path traversal
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
        return;
    }

    $base = __DIR__ . '/../components/' . $name . '/';

    // CSS
    $cssFile = $base . 'style.css';
    if (file_exists($cssFile)) {
        echo '<link rel="stylesheet" href="/components/' . htmlspecialchars($name) . '/style.css">';
    }

    // JS
    $jsFile = $base . 'script.js';
    if (file_exists($jsFile)) {
        echo '<script src="/components/' . htmlspecialchars($name) . '/script.js"></script>';
    }

    // SECURITY: Prefer data.json (safe data-driven format) over component.php
    $dataFile = $base . 'data.json';
    if (file_exists($dataFile)) {
        $raw = file_get_contents($dataFile);
        $data = json_decode($raw, true);
        if (is_array($data) && isset($data['content']) && is_string($data['content'])) {
            // Output the stored HTML content as-is — it was authored by trusted
            // admin users and stored as data, not executable PHP.
            // No PHP execution occurs here.
            echo $data['content'];
            return;
        }
    }

    // Backward compatibility: fall back to component.php for existing components
    // that haven't been migrated to data.json yet.
    $phpFile = $base . 'component.php';
    if (file_exists($phpFile)) {
        include $phpFile;
    }
}


/**
 * Render all components for a page by page_id (database-driven page builder).
 */
function render_components($page_id) {
    $db = db();

    $stmt = $db->prepare("
        SELECT c.folder
        FROM page_components pc
        JOIN components c ON c.id = pc.component_id
        WHERE pc.page_id = ?
        ORDER BY pc.sort_order ASC
    ");

    $stmt->execute([$page_id]);
    $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($components as $component) {
        component($component['folder']);
    }
}

function db() {
    global $pdo;
    return $pdo;
}
