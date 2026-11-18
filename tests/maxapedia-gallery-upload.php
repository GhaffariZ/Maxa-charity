<?php
// Isolated regression for the production gallery POST handler; no database or real files.
declare(strict_types=1);
class GalleryResponse extends RuntimeException {
    public function __construct(public array $payload) { parent::__construct('JSON response'); }
}
function maxapedia_upload_response(array $data, int $status = 200): void { throw new GalleryResponse($data); }
function maxapedia_find($pdo, int $id): array { return ['section'=>'gallery', 'thumbnail'=>'/existing-cover.jpg']; }
function maxapedia_gallery_photo_count($pdo, int $id): int { return count($GLOBALS['saved']); }
function maxapedia_upload_file(array $file, string $section): string {
    if ($file['name'] === 'bad.jpg') throw new RuntimeException('Invalid image');
    return '/uploads/' . $file['name'];
}
function maxapedia_gallery_add_photo($pdo, int $id, string $url, string $caption, string $span, int $sort): void {
    $GLOBALS['saved'][] = [$url, $sort];
}
$source = file_get_contents(__DIR__ . '/../public_html/dashboard/maxapedia.php');
$marker = "} elseif (\$action === 'gallery_upload_photos') {";
$start = strpos($source, $marker) + strlen($marker);
$end = strpos($source, "    } elseif (\$action === 'gallery_save_photos')", $start);
eval('function exercise_gallery_handler() { global $pdo, $galleryUploadJson;' . substr($source, $start, $end - $start) . '}');
function check(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$pdo = null;
$galleryUploadJson = true;
$_POST = ['album_id'=>7];
foreach ([['a.jpg'], ['a.jpg', 'bad.jpg', 'b.jpg']] as $names) {
    $saved = [];
    $_FILES = ['gallery_photos'=>[
        'name'=>$names, 'tmp_name'=>array_fill(0, count($names), '/unused'),
        'error'=>array_fill(0, count($names), UPLOAD_ERR_OK), 'size'=>array_fill(0, count($names), 5),
    ]];
    try { exercise_gallery_handler(); throw new RuntimeException('Expected JSON response'); }
    catch (GalleryResponse $response) {
        $expected = count($names) === 1 ? 1 : 2;
        check($response->payload['uploaded'] === $expected, 'Report the real number saved');
        check(count($saved) === $expected, 'Persist all valid images');
        check($response->payload['ok'] === (count($names) === 1), 'Partial success must not report complete success');
        if ($expected === 2) check($response->payload['errors'][0]['name'] === 'bad.jpg', 'Identify failed image');
    }
}
echo "Gallery PHP handler passed: success and partial failure JSON responses.\n";
