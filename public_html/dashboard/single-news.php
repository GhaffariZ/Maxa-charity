<?php
require "../config/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/html-sanitizer.php';

$news_code = $_GET['news_code'] ?? "";
$slug = $_GET['slug'] ?? "";

// بررسی اولیه ساده
if(empty($news_code)){
    http_response_code(404);
    echo "خبر یافت نشد.";
    exit;
}

// گرفتن اطلاعات خبر با news_code
$stmt = $pdo->prepare("SELECT n.*, t.name_fa AS tag_name
    FROM news n
    LEFT JOIN news_tags t ON n.tag_id = t.id
    WHERE n.news_code = ? AND n.status = 'published' LIMIT 1
");
$stmt->execute([$news_code]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$news){
    http_response_code(404);
    echo "خبر یافت نشد یا منتشر نشده است.";
    exit;
}

// آماده‌سازی متغیرهای لازم برای نمایش
$title = $news['title'];
$content = $news['content'];
$featured_image = $news['featured_image'];
$images = json_decode($news['images'], true) ?? [];
$video = $news['video'];
$author = $news['author'];
$publish_date = $news['publish_date'];
$tag_name = $news['tag_name'] ?? "";
$keywords = $news['keywords'];

// مسیر فولدر تصاویر
$folder = "uploads/news/" . $news_code . "/";
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($title); ?></title>
<meta name="description" content="<?php echo htmlspecialchars(!empty($news['subtitle']) ? $news['subtitle'] : mb_substr(strip_tags($content),0,150)); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars($keywords); ?>">
<meta name="author" content="<?php echo htmlspecialchars($author); ?>">

<style>
body { font-family:tahoma; direction:rtl; max-width:900px; margin:auto; padding:20px; background:#f9f9f9; color:#333; }
h1 { font-size:28px; margin-bottom:15px; }
.article-meta { color:#666; margin-bottom:25px; font-size:14px; }
.article-content { line-height: 1.9; font-size: 16px; }
.article-content::after { content: ""; display: table; clear: both; }
.article-content img { max-width: 100%; height: auto; border-radius: 10px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
.article-content figure, .article-content .article-img-wrap { display: block; max-width: 100%; margin: 20px auto; clear: both; }
.article-content figure.align-right, .article-content .article-img-wrap.align-right, .article-content img.align-right { float: right !important; margin: 8px 0 20px 24px !important; display: block !important; clear: right !important; }
.article-content figure.align-left, .article-content .article-img-wrap.align-left, .article-content img.align-left { float: left !important; margin: 8px 24px 20px 0 !important; display: block !important; clear: left !important; }
.article-content figure.align-center, .article-content .article-img-wrap.align-center, .article-content img.align-center { display: block !important; margin: 24px auto !important; text-align: center !important; float: none !important; clear: both !important; }
.article-content figure.align-full, .article-content .article-img-wrap.align-full, .article-content img.align-full { display: block !important; width: 100% !important; max-width: 100% !important; margin: 28px 0 !important; float: none !important; clear: both !important; }
.article-content figcaption, .article-content .img-caption { margin-top: 8px; font-size: 13px; color: #777; text-align: center; line-height: 1.5; }
.gallery { display:flex; gap:10px; flex-wrap: wrap; margin:20px 0; clear: both; }
.gallery img{ width:150px; height:auto; border-radius:5px; cursor:pointer; transition: transform 0.3s ease; }
.gallery img:hover { transform: scale(1.1); }
.video-container { margin-top: 20px; }
.video-container video { max-width:100%; border-radius:5px; }
.tag {display:inline-block; background:#007bff; color:#fff; padding:5px 12px; border-radius:3px; font-size:13px; margin-bottom:15px;}
@media (max-width: 768px) {
    .article-content figure.align-right, .article-content figure.align-left,
    .article-content .article-img-wrap.align-right, .article-content .article-img-wrap.align-left,
    .article-content img.align-right, .article-content img.align-left {
        float: none !important; margin: 16px auto !important; display: block !important; max-width: 100% !important; width: 100% !important; text-align: center !important;
    }
}
</style>

</head>
<body>

<article>
    <h1><?php echo htmlspecialchars($title); ?></h1>
    <?php if(!empty($news['subtitle'])): ?>
        <p class="article-subtitle" style="font-size:18px; color:#555; line-height:1.6; margin-top:-5px; margin-bottom:15px; font-weight:600;"><?php echo htmlspecialchars($news['subtitle']); ?></p>
    <?php endif; ?>
    <div class="article-meta">
        <?php echo "نویسنده: " . htmlspecialchars($author) . " | تاریخ: " . htmlspecialchars($publish_date); ?><br>
        <?php 
        // Fetch multi-tags
        $db_tags = [];
        try {
            $stmt_db_tags = $pdo->prepare("
                SELECT t.name 
                FROM news_tags t 
                JOIN news_tags_map m ON t.id = m.tag_id 
                WHERE m.news_id = ?
            ");
            $stmt_db_tags->execute([$news['id']]);
            $db_tags = $stmt_db_tags->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $db_tags = [];
        }
        
        $custom_tags = !empty($news['tags']) ? explode(',', $news['tags']) : [];
        $all_news_tags = array_unique(array_merge($db_tags, $custom_tags));
        $all_news_tags = array_filter(array_map('trim', $all_news_tags));
        
        foreach ($all_news_tags as $t): 
        ?>
            <span class="tag"><?php echo htmlspecialchars($t); ?></span>
        <?php endforeach; ?>
    </div>

    <?php if($featured_image && file_exists($folder . $featured_image)): ?>
        <img src="<?php echo $folder . $featured_image; ?>" alt="<?php echo htmlspecialchars($title); ?>">
    <?php endif; ?>

    <div class="article-content">
        <?php echo HtmlSanitizer::sanitize($content); ?>
    </div>

    <?php if(!empty($images)): ?>
        <div class="gallery">
            <?php foreach($images as $img): ?>
                <?php if(file_exists($folder . $img)): ?>
                    <img src="<?php echo $folder . $img; ?>" alt="تصویر گالری">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if($video && file_exists($folder . $video)): ?>
        <div class="video-container">
            <video controls>
                <source src="<?php echo $folder . $video; ?>" type="video/mp4">
                مرورگر شما ویدیو را پشتیبانی نمی‌کند.
            </video>
        </div>
    <?php endif; ?>

</article>

</body>
</html>
