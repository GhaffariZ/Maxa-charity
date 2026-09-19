<?php
declare(strict_types=1);

require_once __DIR__ . '/html-sanitizer.php';

/** Resolve legacy aliases while preventing path traversal. */
function dashboard_component_name(string $raw): ?string
{
    $raw = trim($raw);
    $base = dirname(__DIR__) . '/dashboard/components/';

    if (preg_match('/^[\p{L}\p{N}_-]+$/u', $raw) && is_dir($base . $raw)) {
        return $raw;
    }

    // Preserve the historical "heroindex ikhc" naming convention.
    $parts = preg_split('/\\s+/', $raw) ?: [];
    if (count($parts) === 2) {
        foreach ([$parts[1] . $parts[0], $parts[0]] as $candidate) {
            if (preg_match('/^[\p{L}\p{N}_-]+$/u', $candidate) && is_dir($base . $candidate)) {
                return $candidate;
            }
        }
    }

    return null;
}

/** @return list<string> */
function dashboard_component_images(string $name): array
{
    $dir = dirname(__DIR__) . '/dashboard/components/' . $name . '/images/';
    if (!is_dir($dir)) {
        return [];
    }

    $images = [];
    foreach (scandir($dir) ?: [] as $file) {
        if (preg_match('/^[A-Za-z0-9_.-]+\\.(?:jpe?g|png|gif|webp)$/i', $file)
            && is_file($dir . $file)) {
            $images[] = $file;
        }
    }
    natsort($images);
    return array_values($images);
}

/** Replace {{imageN}} placeholders with public URLs for a component's images. */
function dashboard_component_replace_images(string $html, string $name): string
{
    $images = dashboard_component_images($name);

    return preg_replace_callback('/{{image(\\d+)}}/', static function (array $match) use ($name, $images): string {
        $index = (int)$match[1] - 1;
        if ($index < 0 || !isset($images[$index])) {
            return '';
        }
        return '/dashboard/components/' . rawurlencode($name) . '/images/' . rawurlencode($images[$index]);
    }, $html) ?? '';
}

/** Return safe data-driven markup, or null for an immutable legacy component. */
function dashboard_component_data(string $name): ?string
{
    $file = dirname(__DIR__) . '/dashboard/components/' . $name . '/data.json';
    if (!is_file($file)) {
        return null;
    }

    $data = json_decode((string)file_get_contents($file), true);
    if (!is_array($data) || !isset($data['content']) || !is_string($data['content'])) {
        return null;
    }

    $html = HtmlSanitizer::sanitizeComponent($data['content']);
    return dashboard_component_replace_images($html, $name);
}

/**
 * Render safe data markup first; execute only immutable, repository-managed
 * legacy PHP components. Runtime endpoints never write component.php.
 */
function render_dashboard_component(string $rawName, array $scope = []): bool
{
    $name = dashboard_component_name($rawName);
    if ($name === null) {
        return false;
    }

    $data = dashboard_component_data($name);
    if ($data !== null) {
        echo $data;
        return true;
    }

    $file = dirname(__DIR__) . '/dashboard/components/' . $name . '/component.php';
    if (!is_file($file)) {
        return false;
    }

    ob_start();
    try {
        (static function (string $__file, array $__scope): void {
            extract($__scope, EXTR_SKIP);
            include $__file;
        })($file, $scope);
        $html = (string)ob_get_clean();
    } catch (Throwable $error) {
        ob_end_clean();
        throw $error;
    }

    echo dashboard_component_replace_images($html, $name);
    return true;
}

function dashboard_component_html(string $name, array $scope = []): string
{
    ob_start();
    render_dashboard_component($name, $scope);
    return (string)ob_get_clean();
}
