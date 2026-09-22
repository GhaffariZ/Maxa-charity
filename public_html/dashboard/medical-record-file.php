<?php
declare(strict_types=1);

require_once __DIR__ . '/_guard.php';
dash_require('medical');

$fileId = (int)($_GET['id'] ?? 0);
$where = 'f.id = ?';
$params = [$fileId];

if (!dash_is_super() || !dash_is_hq_view()) {
    $where .= ' AND mr.branch_id = ?';
    $params[] = dash_active_branch_id();
}

$stmt = $pdo->prepare(
    "SELECT f.path, f.original_name, f.mime_type, f.size
       FROM medical_record_files f
       JOIN medical_records mr ON mr.id = f.record_id
      WHERE {$where}
      LIMIT 1"
);
$stmt->execute($params);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file || !preg_match('#^\d+/[a-f0-9]{32}\.(?:jpg|png|webp|pdf)$#', (string)$file['path'])) {
    http_response_code(404);
    exit('فایل یافت نشد.');
}

$base = realpath(__DIR__ . '/../../maksa-private/medical-records');
$path = $base ? realpath($base . '/' . $file['path']) : false;
if (!$base || !$path || !str_starts_with($path, $base . DIRECTORY_SEPARATOR) || !is_file($path)) {
    http_response_code(404);
    exit('فایل یافت نشد.');
}

$name = (string)$file['original_name'];
header('Content-Type: ' . $file['mime_type']);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: attachment; filename="medical-document"; filename*=UTF-8\'\'' . rawurlencode($name));
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
