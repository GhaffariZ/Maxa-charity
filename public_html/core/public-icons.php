<?php
// Official Tabler SVG copies, licensed in assets/icons/tabler/LICENSE.
function macsa_icon(string $name, string $extraClass = '', int $size = 24): string
{
    if (!preg_match('/^[a-z0-9-]+$/', $name)) return '';
    $file = __DIR__ . '/../assets/icons/tabler/' . $name . '.svg';
    if (!is_file($file)) return '';
    $svg = preg_replace('/<!--.*?-->/s', '', file_get_contents($file));
    $svg = preg_replace('/(width|height)="24"/', '$1="' . $size . '"', $svg);
    $class = htmlspecialchars($extraClass, ENT_QUOTES, 'UTF-8');
    return preg_replace('/<svg\b/', '<svg aria-hidden="true" class="' . $class . '"', $svg, 1);
}
