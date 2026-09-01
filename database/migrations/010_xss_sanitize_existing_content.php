<?php
/**
 * Migration 010: Sanitize existing stored content for XSS prevention.
 *
 * Reads all news.content and campaigns.description rows, sanitizes them
 * through HtmlSanitizer::sanitize(), and updates only rows that changed.
 *
 * USAGE:
 *   php database/migrations/010_xss_sanitize_existing_content.php
 *
 * Safe to run multiple times — idempotent.
 */

declare(strict_types=1);

// Load config and sanitizer.
$docRoot = dirname(__DIR__, 2) . '/public_html';
require_once $docRoot . '/core/html-sanitizer.php';

if (file_exists($docRoot . '/../config/database.php')) {
    require_once $docRoot . '/../config/database.php';
} else {
    require_once $docRoot . '/core/database.php';
}

/** @var PDO $pdo */

echo "=== XSS Content Sanitization Migration ===\n\n";

$stats = ['news' => 0, 'campaigns' => 0];

// ── Sanitize news.content ──────────────────────────────────────────────────
echo "Sanitizing news.content...\n";

$stmt = $pdo->query("SELECT id, content FROM news WHERE content IS NOT NULL AND content != ''");
$newsRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updateStmt = $pdo->prepare("UPDATE news SET content = :content WHERE id = :id");

foreach ($newsRows as $row) {
    $cleaned = HtmlSanitizer::sanitize((string)$row['content']);
    if ($cleaned !== $row['content']) {
        $updateStmt->execute([':content' => $cleaned, ':id' => $row['id']]);
        $stats['news']++;
    }
}
echo "  → Updated {$stats['news']} / " . count($newsRows) . " news records.\n\n";

// ── Sanitize campaigns.description ─────────────────────────────────────────
echo "Sanitizing campaigns.description...\n";

$stmt = $pdo->query("SELECT id, description FROM campaigns WHERE description IS NOT NULL AND description != ''");
$campRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updateStmt = $pdo->prepare("UPDATE campaigns SET description = :desc WHERE id = :id");

foreach ($campRows as $row) {
    $cleaned = HtmlSanitizer::sanitize((string)$row['description']);
    if ($cleaned !== $row['description']) {
        $updateStmt->execute([':desc' => $cleaned, ':id' => $row['id']]);
        $stats['campaigns']++;
    }
}
echo "  → Updated {$stats['campaigns']} / " . count($campRows) . " campaign records.\n\n";

$total = $stats['news'] + $stats['campaigns'];
echo "=== Done. Total rows sanitized: {$total} ===\n";
