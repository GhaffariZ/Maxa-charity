<?php
// Real components, with the existing banner test double; no live DB or mail.
$pageTitle = ($argv[1] ?? '') === 'home' ? 'مکسا' : 'تماس با ما';
ob_start();
require __DIR__ . '/event-banner.php';
$html = ob_get_clean();
$html = preg_replace('/<style>body \{ margin: 0; \} #test-content.*?<\/main>/s', '', $html);
echo $html;
$root = __DIR__ . '/../../public_html';
if (($argv[1] ?? '') === 'home') {
    foreach (['hamrah', 'aboutus', 'macsa_stories', 'recent-news-hero-v2', 'ourexcellence', 'footer'] as $component) {
        ob_start();
        require "$root/dashboard/components/$component/component.php";
        $html = ob_get_clean();
        echo preg_replace_callback('/{{image(\d+)}}/', static fn($m) => "/dashboard/components/$component/images/{$m[1]}.png", $html);
    }
} else {
    require "$root/contactus.php";
}
