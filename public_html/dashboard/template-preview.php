<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/component-renderer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
csrf_check();

$data = json_decode(file_get_contents("php://input"), true);

foreach (($data['components'] ?? []) as $c) {
    render_dashboard_component((string)$c, get_defined_vars());
}
