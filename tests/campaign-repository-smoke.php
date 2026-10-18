<?php

declare(strict_types=1);

// Run once for each API source tree; no real database or campaign service needed.
$apiRoot = $argv[1] ?? __DIR__ . '/../public_html/api';
require $apiRoot . '/src/Core/Database.php';
require $apiRoot . '/src/Repositories/CampaignRepository.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$db->exec('CREATE TABLE campaigns (
    id INTEGER PRIMARY KEY, campaign_code TEXT, title TEXT,
    short_description TEXT, description TEXT, details TEXT, category TEXT,
    target_amount NUMERIC, collected_amount NUMERIC, image_url TEXT,
    is_active INTEGER, sort_order INTEGER, branch_id INTEGER
)');
$db->exec('CREATE TABLE panel_donations (campaign_id INTEGER, user_id INTEGER, status TEXT)');
$insert = $db->prepare('INSERT INTO campaigns VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
// Representative rows from the supplied schema; amounts are already Toman.
$insert->execute([8, 'CAMP-20260524-6653', 'واکسن', null, 'متن کمپین', null, 'food', 50000000000, 801000000, '/uploads/campaigns/8/thumb.png', 1, 100, 1]);
$insert->execute([9, 'CAMP-20260524-6305', 'حمایت', null, 'متن', null, 'drug', 200000000, 220000000, null, 1, 100, 1]);
$insert->execute([1, null, 'صندوق عمومی سلامت', 'خلاصه', null, 'جزئیات', 'drug', 500000000, 481500000, null, 0, 1, 1]);
$insert->execute([2, null, 'بایگانی', null, null, 'جزئیات', 'food', 100, 100, null, 0, 2, 1]);
$insert->execute([13, 'CAMP-BRANCH', 'شعبه', '', null, 'جزئیات شعبه', 'education ', null, 0, null, 1, 50, 2]);
$insert->execute([14, 'CAMP-NULL', 'بدون دریافتی', null, null, null, 'drug', 150000, null, null, 1, 100, 1]);
$db->exec("INSERT INTO panel_donations VALUES (8, 1, 'success'), (8, 2, 'success'), (8, 3, 'pending'), (8, 4, 'failed')");

$connection = new ReflectionProperty(Maksa\Core\Database::class, 'pdo');
$connection->setValue(null, $db);
$campaigns = new Maksa\Repositories\CampaignRepository();

$active = $campaigns->list('active');
check(array_column($active, 'id') === ['13', '8', '14'], 'Active filter or sort order is wrong.');
check(array_column($campaigns->list('completed'), 'id') === ['9'], 'Completed filter included an inactive campaign.');
$vaccine = $campaigns->findBySlug('CAMP-20260524-6653');
check($vaccine !== null && $vaccine['goal_amount'] === 50000000000 && $vaccine['raised_amount'] === 801000000, 'Amounts were changed from Toman.');
check($vaccine['image_url'] === '/uploads/campaigns/8/thumb.png' && $vaccine['description'] === 'متن کمپین', 'Card content mapping failed.');
check($vaccine['donor_count'] === 2 && $vaccine['progress'] === 2, 'Successful donations or progress were mapped incorrectly.');
check($campaigns->findBySlug('9')['progress'] === 100, 'Progress exceeded 100.');
check($campaigns->findBySlug('c1')['is_general_fund'] && $campaigns->findBySlug('c1')['status'] === 'archived', 'Fallback slug or general fund mapping failed.');
check($campaigns->findBySlug('CAMP-BRANCH')['category'] === 'مهارت‌آموزی', 'Category mapping failed.');
check($campaigns->findBySlug('CAMP-BRANCH')['days_left'] === null, 'An end date was invented.');
check($campaigns->findBySlug('missing') === null && $campaigns->findBySlug("' OR 1=1 --") === null, 'Invalid slug lookup matched a campaign.');
check(count($campaigns->list()) === 6, 'Unfiltered lookup lost campaigns.');
$campaigns->applySuccessfulDonation(14, 1000);
check($campaigns->findBySlug('14')['raised_amount'] === 1000, 'Nullable collected amount was not updated.');

echo "campaign repository smoke check: OK\n";
