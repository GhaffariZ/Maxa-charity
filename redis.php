<?php
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    echo "Redis connected: " . $redis->ping();
    
    // چک کن کلیدهای بازدید موجودن؟
    $keys = $redis->keys('news_views_count:*');
    echo "\n\nKeys found: " . count($keys);
    print_r($keys);
} catch (Exception $e) {
    echo "Redis error: " . $e->getMessage();
}
?>
