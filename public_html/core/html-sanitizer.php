<?php
declare(strict_types=1);

/**
 * Centralized HTML sanitizer — the single source of truth for content safety.
 *
 * Strategy:
 *  1. Parse the HTML into a DOMDocument tree.
 *  2. Walk every node. Remove disallowed elements entirely (their children too).
 *  3. Walk every attribute on allowed elements. Remove disallowed attributes.
 *  4. Validate href / src URLs — only http and https are permitted.
 *  5. Return the cleaned HTML.
 *
 * This defeats:
 *  - <script>, <iframe>, <object>, <embed>, <form>, <base>, etc.
 *  - Event-handler attributes (onclick, onerror, onload, …)
 *  - javascript: / vbscript: / data: URLs
 *  - CSS-based attacks via style attributes
 *  - Mixed-case and obfuscated payloads
 *  - SVG-based payloads (blocked by default)
 */
final class HtmlSanitizer
{
    /** Tags that are kept; everything else is removed (along with its children). */
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr',
        'strong', 'em', 'b', 'i', 'u', 's', 'del', 'ins', 'mark', 'small', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code',
        'a', 'img',
        'figure', 'figcaption',
        'div', 'span',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        'dl', 'dt', 'dd',
    ];

    /**
     * Attributes allowed per tag.  Key '*' = attributes allowed on every tag.
     *
     * @var array<string, list<string>>
     */
    private const ALLOWED_ATTRIBUTES = [
        '*'       => ['class', 'id', 'dir', 'lang', 'title', 'role', 'aria-label', 'aria-hidden'],
        'a'       => ['href', 'target', 'rel'],
        'img'     => ['src', 'alt', 'width', 'height', 'loading', 'decoding', 'align', 'style'],
        'figure'  => ['class'],
        'td'      => ['colspan', 'rowspan'],
        'th'      => ['colspan', 'rowspan', 'scope'],
        'ol'      => ['start', 'type', 'reversed'],
        'div'     => ['class', 'style'],
        'span'    => ['class', 'style'],
    ];

    /** Only these URL schemes are allowed in href / src attributes. */
    private const ALLOWED_SCHEMES = ['http', 'https'];

    /* ─── Public API ─────────────────────────────────────────────────────── */

    /**
     * Sanitize an HTML string.  Returns clean, safe HTML.
     *
     * @param  string $html  Raw or potentially unsafe HTML.
     * @return string        Sanitized HTML.
     */
    public static function sanitize(string $html): string
    {
        if ($html === '') {
            return '';
        }

        // Normalise line endings and strip null bytes.
        $html = str_replace(["\r\n", "\r"], "\n", $html);
        $html = str_replace("\0", '', $html);

        // Suppress DOMDocument warnings for malformed HTML.
        $prev = libxml_use_internal_errors(true);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = true;
        $doc->formatOutput = false;

        // Wrap in a root element so fragments parse correctly.
        $loaded = $doc->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR
        );

        if (!$loaded) {
            \libxml_clear_errors();
            \libxml_use_internal_errors($prev);
            return '';
        }

        $body = $doc->getElementsByTagName('body')->item(0);
        if ($body === null) {
            \libxml_use_internal_errors($prev);
            return '';
        }

        // Process all nodes recursively.
        self::sanitizeNode($body, $doc);

        // Extract only the inner HTML of <body> (strip the wrapper we added).
        $inner = '';
        foreach ($body->childNodes as $child) {
            $inner .= $doc->saveHTML($child);
        }

        \libxml_use_internal_errors($prev);

        return $inner;
    }

    /* ─── Internal helpers ───────────────────────────────────────────────── */

    /**
     * Recursively sanitize a DOM node and its children.
     */
    private static function sanitizeNode(\DOMNode $node, \DOMDocument $doc): void
    {
        // Collect child nodes first — the list changes as we remove nodes.
        $children = [];
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($child->nodeName);

                if (!in_array($tagName, self::ALLOWED_TAGS, true)) {
                    // Disallowed element — remove it and all its children.
                    $node->removeChild($child);
                    continue;
                }

                // Strip disallowed attributes.
                self::sanitizeAttributes($child);

                // Recurse into children.
                self::sanitizeNode($child, $doc);

            } elseif ($child->nodeType === XML_TEXT_NODE) {
                // Text nodes are safe as-is.

            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                // Remove HTML comments (can contain conditional IE payloads).
                $node->removeChild($child);

            } else {
                // Remove any other node types (CDATA, PI, etc.).
                $node->removeChild($child);
            }
        }
    }

    /**
     * Remove disallowed attributes from an element and validate URLs.
     */
    private static function sanitizeAttributes(\DOMNode $node): void
    {
        $tagName = strtolower($node->nodeName);

        // Determine which attributes are allowed for this tag.
        $globalAttrs = self::ALLOWED_ATTRIBUTES['*'] ?? [];
        $tagAttrs    = self::ALLOWED_ATTRIBUTES[$tagName] ?? [];
        $allowed     = array_unique(array_merge($globalAttrs, $tagAttrs));

        $toRemove = [];
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $attrName = strtolower($attr->name);

                // Always strip event handlers (onclick, onerror, onload, …).
                if (strpos($attrName, 'on') === 0) {
                    $toRemove[] = $attr;
                    continue;
                }

                // Check the allowlist.
                if (!in_array($attrName, $allowed, true)) {
                    $toRemove[] = $attr;
                    continue;
                }

                // Validate URL attributes.
                if (in_array($attrName, ['href', 'src', 'action', 'formaction', 'poster', 'background'], true)) {
                    if (!self::isSafeUrl($attr->value)) {
                        $toRemove[] = $attr;
                        continue;
                    }
                }

                // Validate style attributes — strip dangerous CSS.
                if ($attrName === 'style') {
                    if (!self::isSafeStyle($attr->value)) {
                        $toRemove[] = $attr;
                        continue;
                    }
                }
            }
        }

        foreach ($toRemove as $attr) {
            $node->removeAttribute($attr->name);
        }
    }

    /**
     * Check whether a URL value is safe (only http/https, no JS/VB/data URIs).
     */
    private static function isSafeUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '') {
            return true; // Empty href is harmless.
        }

        // Decode HTML entities that might hide the real scheme.
        $decoded = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Strip whitespace and control characters that could obfuscate the scheme.
        $normalised = preg_replace('/[\s\x00-\x1f\x7f]+/', '', $decoded);

        // Check for dangerous schemes (case-insensitive).
        $lower = strtolower($normalised);

        // Block javascript:, vbscript:, data:, file:, ftp:, etc.
        if (preg_match('/^\s*(javascript|vbscript|data|file|ftp|mailto|tel):/i', $lower)) {
            return false;
        }

        // If it looks like an absolute URL, only allow http/https.
        if (preg_match('/^[a-z][a-z0-9+\-.]*:/i', $lower)) {
            $scheme = parse_url($lower, PHP_URL_SCHEME);
            return $scheme !== null && in_array(strtolower($scheme), self::ALLOWED_SCHEMES, true);
        }

        // Relative URLs, fragments, etc. are allowed.
        return true;
    }

    /**
     * Validate a style attribute value — strip dangerous CSS constructs.
     */
    private static function isSafeStyle(string $style): bool
    {
        $style = strtolower($style);

        $dangerous = [
            '/expression\s*\(/i',
            '/url\s*\(\s*["\']?\s*(?!https?:)/i',
            '/-moz-binding\s*:/i',
            '/behavior\s*:/i',
            '/@import/i',
            '/<\s*script/i',
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
        ];

        foreach ($dangerous as $pattern) {
            if (preg_match($pattern, $style)) {
                return false;
            }
        }

        return true;
    }
}
