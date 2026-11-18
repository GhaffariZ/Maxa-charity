<?php
declare(strict_types=1);

// Isolated SQLite regression: execute production CRUD without booting the site's database.
require_once __DIR__ . '/../public_html/core/content-locales.php';
$source = file_get_contents(__DIR__ . '/../public_html/dashboard/maxapedia-db.php');
eval(substr($source, strpos($source, 'function maxapedia_create(')));
function maxapedia_is_section(string $section): bool { return in_array($section, ['brochures', 'books', 'gallery'], true); }
function q(PDO $pdo, string $sql, array $params = []): PDOStatement {
    $statement = $pdo->prepare($sql); $statement->execute($params); return $statement;
}
function check(bool $value, string $message): void { if (!$value) throw new RuntimeException($message); }
$removedFiles = [];
function maxapedia_delete_local_file(?string $path): void { $GLOBALS['removedFiles'][] = $path; }
$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$db->exec('CREATE TABLE maxapedia_items (id INTEGER PRIMARY KEY AUTOINCREMENT, section TEXT, category TEXT, title TEXT, description TEXT, url TEXT, thumbnail TEXT, back_image TEXT, status TEXT, sort_order INTEGER);
CREATE TABLE maxapedia_gallery_photos (id INTEGER PRIMARY KEY, item_id INTEGER, image_url TEXT);
CREATE TABLE public_content_locales (content_type TEXT, content_id TEXT, locale TEXT, payload TEXT)');
$data = ['section'=>'brochures', 'title'=>'Two-sided guide', 'thumbnail'=>'/uploads/front.png', 'back_image'=>'/uploads/back.png'];
$id = maxapedia_create($db, $data);
$row = q($db, 'SELECT * FROM maxapedia_items WHERE id=?', [$id])->fetch();
check($row['thumbnail']===$data['thumbnail'] && $row['back_image']===$data['back_image'], 'Both images must be saved');
$data['back_image'] = '/uploads/new-back.png';
maxapedia_update($db, $id, $data);
check(q($db, 'SELECT back_image FROM maxapedia_items WHERE id=?', [$id])->fetchColumn()===$data['back_image'], 'Back image can be replaced');
q($db, 'INSERT INTO public_content_locales VALUES (?,?,?,?)', ['maxapedia_items', (string)$id, 'en', '{}']);
q($db, 'INSERT INTO maxapedia_gallery_photos VALUES (?,?,?)', [1, $id, '/uploads/attached.png']);
$db->exec("CREATE TRIGGER fail_delete BEFORE DELETE ON maxapedia_items BEGIN SELECT RAISE(ABORT, 'test failure'); END");
try { maxapedia_delete($db, $id); throw new RuntimeException('Expected deletion failure'); }
catch (PDOException $expected) {}
check(!$db->inTransaction() && $removedFiles===[], 'Failed deletion must not remove files');
check((int)$db->query('SELECT count(*) FROM public_content_locales')->fetchColumn()===1, 'Translations must roll back');
check((int)$db->query('SELECT count(*) FROM maxapedia_gallery_photos')->fetchColumn()===1, 'Attached photos must roll back');
$db->exec('DROP TRIGGER fail_delete');
check(maxapedia_delete($db, $id), 'Deletion succeeds');
foreach (['maxapedia_items', 'public_content_locales', 'maxapedia_gallery_photos'] as $table) {
    check((int)$db->query('SELECT count(*) FROM '.$table)->fetchColumn()===0, $table.' must be empty');
}
check(in_array('/uploads/front.png', $removedFiles, true) && in_array('/uploads/new-back.png', $removedFiles, true), 'Both sides are cleaned after commit');
check(maxapedia_create($db, $data)>$id, 'Brochure can be recreated after deletion');
check(!maxapedia_delete($db, 99999), 'Missing records must not report successful deletion');

// Render the real delete form to catch misplaced CSRF markup.
$page = file_get_contents(__DIR__ . '/../public_html/dashboard/maxapedia.php');
$start = strrpos($page, '<form method="post" action="maxapedia.php?section=');
$form = substr($page, $start, strpos($page, '</form>', $start) - $start + 7);
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="test-token">'; }
$section='brochures'; $it=['id'=>1];
ob_start(); eval('?>'.$form); $html=ob_get_clean();
$dom = new DOMDocument(); @$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
check($xpath->evaluate('string(//form/@action)')==='maxapedia.php?section=brochures', 'Delete action URL must be valid');
check($xpath->evaluate('string(//form/input[@name="csrf_token"]/@value)')==='test-token', 'CSRF token belongs inside the form');
echo "PASS two-sided brochure CRUD, atomic deletion, recreation and delete form CSRF\n";
