<?php

declare(strict_types=1);

namespace Maksa\Support;

use Maksa\Core\Config;
use Maksa\Core\Exceptions\ApiException;

/**
 * Handles avatar image uploads safely:
 *   - validates the real MIME type with finfo (not the client-sent name/type)
 *   - enforces a byte size limit
 *   - re-encodes via GD to strip EXIF/metadata and any embedded payload
 *   - stores with a random UUID filename in a dir ABOVE the web root
 * Files are then served back through a guarded API route, never executed.
 */
final class AvatarStorage
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /** @param array<string,mixed> $file a single $_FILES entry. Returns the public URL path. */
    public static function store(array $file, int $userId): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
            throw ApiException::badRequest('فایلی دریافت نشد.', 'no_file');
        }

        $maxBytes = Config::int('UPLOAD_MAX_BYTES', 2097152);
        if (($file['size'] ?? 0) > $maxBytes) {
            throw ApiException::badRequest('حجم تصویر بیش از حد مجاز است.', 'file_too_large');
        }

        $tmp = (string) $file['tmp_name'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        if (!isset(self::ALLOWED[$mime])) {
            throw ApiException::badRequest('فقط تصاویر JPG، PNG یا WebP مجاز است.', 'invalid_image');
        }

        $dir = rtrim((string) Config::require('UPLOAD_DIR'), '/\\') . '/avatars';
        if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
            throw new \RuntimeException('پوشهٔ بارگذاری قابل ایجاد نیست.');
        }

        $ext = self::ALLOWED[$mime];
        $name = $userId . '-' . Str::uuid4() . '.' . $ext;
        $dest = $dir . '/' . $name;

        self::reencode($tmp, $dest, $mime);

        // Public path served by GET /api/user/avatar/{file} (same origin).
        return '/api/user/avatar/' . $name;
    }

    /** Returns [absolutePath, mimeType] for a stored avatar, or null if invalid. */
    public static function resolve(string $filename): ?array
    {
        // Defend against traversal: only a bare uuid-ish filename is acceptable.
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $filename) || str_contains($filename, '..')) {
            return null;
        }
        $path = rtrim((string) Config::require('UPLOAD_DIR'), '/\\') . '/avatars/' . $filename;
        if (!is_file($path)) {
            return null;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            default       => 'application/octet-stream',
        };
        return [$path, $mime];
    }

    /** Re-create the image with GD to strip metadata. Falls back to a plain move. */
    private static function reencode(string $src, string $dest, string $mime): void
    {
        if (!\function_exists('imagecreatefromstring')) {
            if (!move_uploaded_file($src, $dest)) {
                throw new \RuntimeException('ذخیرهٔ تصویر ناموفق بود.');
            }
            @chmod($dest, 0600);
            return;
        }

        $data = file_get_contents($src);
        $img = $data !== false ? @imagecreatefromstring($data) : false;
        if ($img === false) {
            throw ApiException::badRequest('تصویر قابل پردازش نیست.', 'invalid_image');
        }

        // Downscale very large images to a sane avatar size (max 512px).
        $w = imagesx($img);
        $h = imagesy($img);
        $max = 512;
        if ($w > $max || $h > $max) {
            $scale = $max / max($w, $h);
            $nw = (int) ($w * $scale);
            $nh = (int) ($h * $scale);
            $resized = imagecreatetruecolor($nw, $nh);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($img);
            $img = $resized;
        }

        $ok = match ($mime) {
            'image/png'  => imagepng($img, $dest, 8),
            'image/webp' => imagewebp($img, $dest, 85),
            default      => imagejpeg($img, $dest, 85),
        };
        imagedestroy($img);

        if (!$ok) {
            throw new \RuntimeException('ذخیرهٔ تصویر ناموفق بود.');
        }
        @chmod($dest, 0600);
    }
}
