<div class="news-page">
    <h1><?= htmlspecialchars($news['title']) ?></h1>
    <p><small><?= htmlspecialchars($news['author']) ?> | <?= htmlspecialchars($news['publish_date']) ?></small></p>

    <?php if($news['image']): ?>
        <img src="/uploads/news/<?= htmlspecialchars($news['image']) ?>" style="max-width:600px;">
    <?php endif; ?>

    <div class="content">
        <?= nl2br(htmlspecialchars($news['content'])) ?>
    </div>

    <?php if($news['video']): ?>
        <video controls width="600" src="/uploads/news/<?= htmlspecialchars($news['video']) ?>"></video>
    <?php endif; ?>
</div>
