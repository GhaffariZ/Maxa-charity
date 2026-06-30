<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

function slugify_feed($text) {
    $text = trim((string)$text);
    $text = preg_replace('/[^a-zA-Z0-9آ-ی\-]+/u', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function g2j_feed($gy, $gm, $gd) {
    $g=[0,31,59,90,120,151,181,212,243,273,304,334];
    if($gy>1600){$jy=979;$gy-=1600;}else{$jy=0;$gy-=621;}
    $gy2=($gm>2)?($gy+1):$gy;
    $d=(365*$gy)+intval(($gy2+3)/4)-intval(($gy2+99)/100)+intval(($gy2+399)/400)-80+$gd+$g[$gm-1];
    $jy+=33*intval($d/12053);$d%=12053;$jy+=4*intval($d/1461);$d%=1461;
    if($d>365){$jy+=intval(($d-1)/365);$d=($d-1)%365;}
    if($d<186){$jm=1+intval($d/31);$jd=1+($d%31);}else{$jm=7+intval(($d-186)/30);$jd=1+(($d-186)%30);}
    return [$jy,$jm,$jd];
}

function jalali_feed($dt) {
    $ts = strtotime((string)$dt);
    if (!$ts) return '---';
    [$jy,$jm,$jd] = g2j_feed((int)date('Y',$ts),(int)date('m',$ts),(int)date('d',$ts));
    return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
}

$limit = isset($_GET['limit']) ? max(1, min(12, (int)$_GET['limit'])) : 3;

// ایزولاسیون چندشعبه‌ای: با ?branch=<slug> فقط اخبارِ همان شعبه؛ بدون پارامتر یا
// slug ناشناخته → اخبارِ «ستاد مرکزی» (HQ، branch_id = 1). همان الگوی hero-list.php.
$branchSlug = isset($_GET['branch']) ? preg_replace('/[^a-z0-9-]/', '', strtolower((string)$_GET['branch'])) : '';
$branchId   = 1;
if ($branchSlug !== '') {
    $bs = $pdo->prepare("SELECT id FROM branches WHERE slug = ? AND status = 'active' LIMIT 1");
    $bs->execute([$branchSlug]);
    $brow = $bs->fetch(PDO::FETCH_ASSOC);
    if ($brow) { $branchId = (int)$brow['id']; }
}

$stmt = $pdo->prepare("
SELECT n.id,n.title,n.content,n.publish_date,n.read_time,n.featured_image,n.news_code,c.name AS category_name
FROM news n
LEFT JOIN news_categories c ON c.id=n.category_id
WHERE n.status='published' AND n.publish_date<=NOW() AND n.branch_id = ? AND (n.reject_reason IS NULL OR TRIM(n.reject_reason)='')
ORDER BY n.publish_date DESC
LIMIT {$limit}
");
$stmt->execute([$branchId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = array_map(function($row) use ($branchSlug){
    $image = (!empty($row['featured_image']) && !empty($row['news_code']))
      ? "/uploads/news/" . rawurlencode($row['news_code']) . "/" . rawurlencode($row['featured_image'])
      : "";
    // لینکِ خبر: برای شعبه از مسیرِ داخلیِ شعبه (‎/{slug}/news/{id}) که page-view رندرش می‌کند؛
    // برای ستاد مرکزی همان مسیرِ قدیمیِ ‎/{id}/{slug}/.
    $url = $branchSlug !== ''
      ? "/" . rawurlencode($branchSlug) . "/news/" . (int)$row['id']
      : "/" . (int)$row['id'] . "/" . rawurlencode(slugify_feed($row['title'] ?? '')) . "/";
    return [
      "id" => (int)$row['id'],
      "title" => $row['title'] ?? '',
      "category" => $row['category_name'] ?? 'اخبار',
      "date" => jalali_feed($row['publish_date'] ?? ''),
      "read_time" => max(1, (int)($row['read_time'] ?? 1)),
      "excerpt" => mb_substr(trim(preg_replace('/\s+/u',' ',strip_tags((string)($row['content'] ?? '')))),0,120),
      "url" => $url,
      "image" => $image
    ];
}, $rows);

echo json_encode(["status"=>"ok","items"=>$data], JSON_UNESCAPED_UNICODE);
