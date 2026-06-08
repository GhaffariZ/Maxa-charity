<?php

function component($name) {
    $base = __DIR__ . '/../components/' . $name . '/';

    // CSS
    $cssFile = $base . 'style.css';
    if (file_exists($cssFile)) {
        echo '<link rel="stylesheet" href="/components/' . $name . '/style.css">';
    }

    // JS
    $jsFile = $base . 'script.js';
    if (file_exists($jsFile)) {
        echo '<script src="/components/' . $name . '/script.js"></script>';
    }

    // INDEX (HTML/PHP)
    $phpFile = $base . 'index.php';
    if (file_exists($phpFile)) {
        include $phpFile;
    }
}



function render_components($page_id) {
    echo "RENDER COMPONENTS CALLED<br>";

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

    echo "<pre>";
    print_r($components);
    echo "</pre>";

    foreach ($components as $component) {
        component($component['folder']);
    }
}

function db() {
    global $pdo;
    return $pdo;
}


