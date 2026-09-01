<h1><?= htmlspecialchars($news['title']) ?></h1>

<?php if(file_exists("/uploads/news/{$news['news_code']}/featured.jpg")): ?>
<img src="/uploads/news/<?= $news['news_code'] ?>/featured.jpg"
     style="width:100%;max-height:420px;object-fit:cover;border-radius:12px;margin:20px 0;">
<?php endif; ?>

<div class="news-content">
  <?= HtmlSanitizer::sanitize($news['content']) ?>
</div>
