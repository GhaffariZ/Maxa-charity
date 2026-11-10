<?php
// Loaded only by the ignored local php.ini. Keep internal links in this preview.
if (PHP_SAPI !== 'cli') {
    ob_start(static function (string $body): string {
        return str_replace(
            ['https://mymacsa.ir', 'http://mymacsa.ir', 'https://www.mymacsa.ir'],
            'http://localhost:8080',
            $body
        );
    });
}
