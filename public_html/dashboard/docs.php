<?php
/* ============================================================================
 *  سامانه مستندات و راهنمای جامع مکسا (MACSA Docs Portal)
 *  الهام‌گرفته از ساختار حرفه‌ای Read the Docs / Sphinx / MkDocs Material
 *  طراحی کاملاً بومی، راست‌چین (RTL)، واکنش‌گرا، با پشتیبانی از تم تاریک/روشن و جستجوی آنی.
 * ========================================================================== */
declare(strict_types=1);
require_once __DIR__ . '/_guard.php';

// پوشه مستندات کاربری در مخزن پروژه
$DOCS_DIR = realpath(__DIR__ . '/../../docs/user-manual');
if (!$DOCS_DIR || !is_dir($DOCS_DIR)) {
    // مسیر جایگزین در صورت تغییر چیدمان دایرکتوری
    $DOCS_DIR = realpath(__DIR__ . '/../docs/user-manual') ?: (__DIR__ . '/docs');
}

// ساختار کامل فصول و دسته‌بندی‌های مستندات
$DOCS_NAV = [
    [
        'category' => 'مقدمه و شروع کار',
        'icon' => 'compass',
        'items' => [
            ['id' => 'README', 'title' => 'صفحه اصلی و نقشه راهنما', 'status' => '🟢', 'summary' => 'معرفی سامانه، راهنمای نمادها و دسترسی سریع'],
            ['id' => '01-getting-started', 'title' => 'مفاهیم پایه و معماری چندشعبه‌ای', 'status' => '🟢', 'summary' => 'معماری سه پرتال، تفکیک داده‌ها و آدرس‌های تمیز'],
            ['id' => '02-roles-and-permissions', 'title' => 'نقش‌ها و ماتریس دسترسی‌ها', 'status' => '🟢', 'summary' => 'مدیر ارشد، مدیر شعبه، خبرنگار، سردبیر و ماتریس دسترسی'],
            ['id' => '03-login-and-account', 'title' => 'ورود، خروج و امنیت حساب کاربری', 'status' => '🟢', 'summary' => 'قفل ۱۵ دقیقه‌ای، انقضای نشست کاری و تغییر رمز'],
        ]
    ],
    [
        'category' => 'پیشخوان و مدیریت سازمان',
        'icon' => 'dashboard',
        'items' => [
            ['id' => '04-admin-dashboard', 'title' => 'پیشخوان مدیریت و شاخص‌های کلیدی', 'status' => '🟢', 'summary' => 'شاخص‌های زنده، سوییچ شعبه، هدف ماهانه و تراکنش‌ها'],
            ['id' => '05-branch-management', 'title' => 'مدیریت و تعریف شعب جدید', 'status' => '🟢', 'summary' => 'فرآیند راه‌اندازی مرکز جدید، ایجاد ادمین و تگ اسلاگ'],
            ['id' => '06-user-management', 'title' => 'مدیریت کاربران و نقش‌های شعبه', 'status' => '🟢', 'summary' => 'تعریف پرسنل، انتساب نقش‌های سفارشی و رفع قفل حساب'],
        ]
    ],
    [
        'category' => 'صفحه‌ساز و مدیریت محتوا',
        'icon' => 'layers',
        'items' => [
            ['id' => '07-page-builder', 'title' => 'صفحه‌ساز و کاتالوگ کامپوننت‌ها', 'status' => '🟢', 'summary' => 'ساخت صفحات با Drag & Drop و کاتالوگ ۴۸ کامپوننت سیستم'],
            ['id' => '08-news', 'title' => 'مدیریت اخبار و گردش‌کار تحریریه', 'status' => '🟢', 'summary' => 'ایجاد خبر، تصویر شاخص، زیرعنوان و چرخه تایید سردبیر'],
            ['id' => '09-campaigns', 'title' => 'کمپین‌های حمایتی و جذب مشارکت', 'status' => '🟢', 'summary' => 'ایجاد کمپین، کارت پیش‌نمایش زنده و پایش مبالغ هدفی'],
            ['id' => '12-maxapedia', 'title' => 'دانشنامه سلامت مکساپدیا', 'status' => '🟢', 'summary' => 'مدیریت ۶ بخش ویدیو، بروشور، کتاب، پادکست، کلیپ و گالری'],
            ['id' => '13-stories', 'title' => 'بانک روایات امید مکسا', 'status' => '🟢', 'summary' => 'روایت‌های امید، داستان‌های بیماران، همراهان و خیرین'],
        ]
    ],
    [
        'category' => 'خدمات حمایتی و آکادمی',
        'icon' => 'heart',
        'items' => [
            ['id' => '10-stands-and-orders', 'title' => 'استندهای همدلی و کارتابل سفارشات', 'status' => '🟢', 'summary' => 'استندهای تبریک و تسلیت، قیمت‌گذاری و مدیریت تحویل مراسمات'],
            ['id' => '11-courses', 'title' => 'سامانه آموزش و آکادمی (LMS)', 'status' => '🟢', 'summary' => 'ایجاد دوره‌ها، سرفصل‌ها، ویدیوهای آموزشی و کاتالوگ آکادمی'],
            ['id' => '14-partners', 'title' => 'شبکه همکاران و معرفی پرسنل', 'status' => '🟢', 'summary' => 'کادر پزشکی، پرستاری، روانشناسی و مددکاری هر مرکز'],
        ]
    ],
    [
        'category' => 'امور مالی، ارتباطات و خیرین',
        'icon' => 'wallet',
        'items' => [
            ['id' => '15-ticketing', 'title' => 'سامانه تیکتینگ و مکاتبات داخلی', 'status' => '🟢', 'summary' => 'هرم مکاتبات ۳ سطحی: کاربر به مدیر شعبه و ارجاع به ستاد'],
            ['id' => '16-financial', 'title' => 'گزارش‌های مالی و تحلیل تراکنش‌ها', 'status' => '🟢', 'summary' => 'فیلتر تاریخ شمسی، ایزولاسیون درآمد شعب و گزارش چاپی'],
            ['id' => '17-feedback', 'title' => 'انتقادات و پیشنهادات مراجعان', 'status' => '🟡', 'summary' => 'پایش نظرات مراجعان (نسخه آزمایشی / ماک‌آپ UI)'],
            ['id' => '18-benefactor-dashboard', 'title' => 'پرتال و داشبورد اختصاصی خیرین', 'status' => '🟢', 'summary' => 'واریز آنلاین، پیشنهاد کمپین و گواهی مالیاتی ماده ۱۷۲'],
        ]
    ],
    [
        'category' => 'سایت عمومی، عیب‌یابی و پشتیبانی',
        'icon' => 'life-buoy',
        'items' => [
            ['id' => '19-public-site', 'title' => 'سایت عمومی و درگاه‌های شعب', 'status' => '🟢', 'summary' => 'آدرس‌های تمیز، درگاه اختصاصی هر شعبه و معرفی خدمات'],
            ['id' => '20-troubleshooting', 'title' => 'راهنمای عیب‌یابی و رفع خطاها', 'status' => '🟢', 'summary' => 'حل خطاهای رایج، رفع قفل حساب و خطای ۴۱۹ CSRF'],
            ['id' => '21-faq', 'title' => 'پرسش‌های متداول پرسنل و خیرین', 'status' => '🟢', 'summary' => 'پاسخ به سوالات پرتکرار پرسنل، داوطلبان و نیکوکاران'],
        ]
    ],
];

// استخراج لیست تخت از کلیه مقالات جهت ناوبری قبلی/بعدی و جستجو
$FLAT_ITEMS = [];
foreach ($DOCS_NAV as $cat) {
    foreach ($cat['items'] as $item) {
        $item['category'] = $cat['category'];
        $FLAT_ITEMS[] = $item;
    }
}

// تعیین سند درخواستی (با اعتبارسنجی دقیق جهت جلوگیری از Path Traversal)
$rawDoc = trim((string)($_GET['doc'] ?? 'README'));
$currentDocId = 'README';
if (preg_match('/^[a-zA-Z0-9_-]+$/', $rawDoc)) {
    foreach ($FLAT_ITEMS as $it) {
        if ($it['id'] === $rawDoc) {
            $currentDocId = $it['id'];
            break;
        }
    }
}

// پیدا کردن ایندکس سند جاری در لیست تخت
$currentIndex = 0;
$currentMeta = $FLAT_ITEMS[0];
foreach ($FLAT_ITEMS as $idx => $it) {
    if ($it['id'] === $currentDocId) {
        $currentIndex = $idx;
        $currentMeta = $it;
        break;
    }
}
$prevDoc = $currentIndex > 0 ? $FLAT_ITEMS[$currentIndex - 1] : null;
$nextDoc = $currentIndex < (count($FLAT_ITEMS) - 1) ? $FLAT_ITEMS[$currentIndex + 1] : null;

// خواندن محتوای فایل مارک‌داون
$mdFilePath = $DOCS_DIR . '/' . $currentDocId . '.md';
$rawMarkdown = '';
if (file_exists($mdFilePath)) {
    $rawMarkdown = (string)file_get_contents($mdFilePath);
} else {
    $rawMarkdown = "# ۴۰۴ | سند یافت نشد\n\nمتأسفانه سند درخواستی با شناسه `{$currentDocId}` در مسیر مستندات یافت نشد.";
}

// ----------------------------------------------------------------------------
// مفسر مارک‌داون اختصاصی و سبک‌بار PHP با استانداردهای مدرن
// ----------------------------------------------------------------------------
class MacsaMarkdownParser {
    public array $headings = [];

    public function parse(string $text): string {
        $this->headings = [];
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
        $html = [];
        $inCodeBlock = false;
        $codeLang = '';
        $codeLines = [];
        $inTable = false;
        $tableRows = [];
        $inList = false;
        $listType = 'ul';
        $inBlockquote = false;
        $blockquoteLines = [];
        $inAdmonition = false;
        $admonitionType = 'note';
        $admonitionLines = [];

        foreach ($lines as $line) {
            // بلوک‌های کد
            if (preg_match('/^```(\w*)/', $line, $m)) {
                if ($inCodeBlock) {
                    $codeText = htmlspecialchars(implode("\n", $codeLines), ENT_QUOTES, 'UTF-8');
                    if ($codeLang === 'mermaid') {
                        $html[] = '<div class="mermaid-card"><div class="mermaid-header"><span class="mermaid-label">نمودار فرآیند</span></div><pre class="mermaid-code"><code>' . $codeText . '</code></pre></div>';
                    } else {
                        $langLabel = $codeLang ? strtoupper($codeLang) : 'TEXT';
                        $html[] = '<div class="code-box"><div class="code-box-header"><span class="code-lang">' . $langLabel . '</span><button type="button" class="copy-code-btn" onclick="copyCodeBlock(this)">کپی</button></div><pre><code>' . $codeText . '</code></pre></div>';
                    }
                    $codeLines = [];
                    $inCodeBlock = false;
                    $codeLang = '';
                } else {
                    $this->closeContainers($html, $inTable, $tableRows, $inList, $inBlockquote, $blockquoteLines, $inAdmonition, $admonitionLines);
                    $inCodeBlock = true;
                    $codeLang = strtolower($m[1] ?? '');
                }
                continue;
            }

            if ($inCodeBlock) {
                $codeLines[] = $line;
                continue;
            }

            // جداول مارک‌داون
            if (preg_match('/^\|(.+)\|$/', trim($line))) {
                $this->closeContainers($html, $inTable, $tableRows, $inList, $inBlockquote, $blockquoteLines, $inAdmonition, $admonitionLines, false);
                $inTable = true;
                $tableRows[] = $line;
                continue;
            } elseif ($inTable) {
                $html[] = $this->renderTable($tableRows);
                $tableRows = [];
                $inTable = false;
            }

            // هشدارهای GitHub Callouts (> [!NOTE] ...)
            if (preg_match('/^>\s*\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]\s*(.*)$/i', $line, $m)) {
                $this->closeContainers($html, $inTable, $tableRows, $inList, $inBlockquote, $blockquoteLines, $inAdmonition, $admonitionLines);
                $inAdmonition = true;
                $admonitionType = strtolower($m[1]);
                if (!empty($m[2])) $admonitionLines[] = $m[2];
                continue;
            }

            if ($inAdmonition) {
                if (preg_match('/^>\s?(.*)$/', $line, $bm)) {
                    $admonitionLines[] = $bm[1];
                    continue;
                } else {
                    $html[] = $this->renderAdmonition($admonitionType, $admonitionLines);
                    $admonitionLines = [];
                    $inAdmonition = false;
                }
            }

            // نقل قول‌های عادی (> ...)
            if (preg_match('/^>\s?(.*)$/', $line, $bm)) {
                $this->closeContainers($html, $inTable, $tableRows, $inList, $inBlockquote, $blockquoteLines, $inAdmonition, $admonitionLines, true, false);
                $inBlockquote = true;
                $blockquoteLines[] = $bm[1];
                continue;
            } elseif ($inBlockquote) {
                $html[] = '<blockquote><p>' . implode("<br>", array_map([$this, 'formatInlines'], $blockquoteLines)) . '</p></blockquote>';
                $blockquoteLines = [];
                $inBlockquote = false;
            }

            // لیست‌های نامرتب و چک‌باکس‌ها
            if (preg_match('/^(\*|-)\s+(.*)$/', $line, $lm)) {
                if (!$inList) {
                    $this->closeContainers($html, $inTable, $tableRows, $inList, $inBlockquote, $blockquoteLines, $inAdmonition, $admonitionLines);
                    $inList = true;
                    $listType = 'ul';
                    $html[] = '<ul class="doc-list">';
                }
                $itemText = $lm[2];
                if (preg_match('/^\[(x| )\]\s+(.*)$/i', $itemText, $cm)) {
                    $checked = strtolower($cm[1]) === 'x';
                    $html[] = '<li class="task-item"><input type="checkbox" ' . ($checked ? 'checked' : '') . ' disabled> <span>' . $this->formatInlines($cm[2]) . '</span></li>';
                } else {
                    $html[] = '<li>' . $this->formatInlines($itemText) . '</li>';
                }
                continue;
            }

            // لیست‌های ترتیبی (1. 2. ...)
            if (preg_match('/^(\d+)\.\s+(.*)$/', $line, $om)) {
                if (!$inList || $listType !== 'ol') {
                    $this->closeContainers($html, $inTable, $tableRows, $inList, $inBlockquote, $blockquoteLines, $inAdmonition, $admonitionLines);
                    $inList = true;
                    $listType = 'ol';
                    $html[] = '<ol class="doc-list-ordered">';
                }
                $html[] = '<li>' . $this->formatInlines($om[2]) . '</li>';
                continue;
            }

            if ($inList) {
                $html[] = $listType === 'ol' ? '</ol>' : '</ul>';
                $inList = false;
            }

            // تیترها (# H1, ## H2, ### H3, #### H4)
            if (preg_match('/^(#{1,4})\s+(.*)$/', $line, $hm)) {
                $level = strlen($hm[1]);
                $headingText = trim($hm[2]);
                $rawClean = strip_tags($this->formatInlines($headingText));
                $slug = $this->slugify($rawClean);

                if ($level === 2 || $level === 3) {
                    $this->headings[] = [
                        'level' => $level,
                        'text'  => $rawClean,
                        'id'    => $slug,
                    ];
                }

                $html[] = "<h{$level} id=\"{$slug}\" class=\"doc-heading doc-h{$level}\">"
                        . $this->formatInlines($headingText)
                        . "<a href=\"#{$slug}\" class=\"header-anchor\" title=\"پیوند به این بخش\">#</a>"
                        . "</h{$level}>";
                continue;
            }

            // خطوط جداکننده
            if (preg_match('/^(\-{3,}|\*{3,})$/', trim($line))) {
                $html[] = '<hr class="doc-hr">';
                continue;
            }

            // پاراگراف‌های متنی عادی
            $trimLine = trim($line);
            if ($trimLine !== '') {
                $html[] = '<p>' . $this->formatInlines($trimLine) . '</p>';
            }
        }

        // بستن بلوک‌های بازمانده
        $this->closeContainers($html, $inTable, $tableRows, $inList, $inBlockquote, $blockquoteLines, $inAdmonition, $admonitionLines);
        if ($inCodeBlock) {
            $html[] = '<pre><code>' . htmlspecialchars(implode("\n", $codeLines), ENT_QUOTES, 'UTF-8') . '</code></pre>';
        }

        return implode("\n", $html);
    }

    private function closeContainers(array &$html, bool &$inTable, array &$tableRows, bool &$inList, bool &$inBlockquote, array &$blockquoteLines, bool &$inAdmonition, array &$admonitionLines, bool $closeTable = true, bool $closeQuote = true): void {
        if ($closeTable && $inTable) {
            $html[] = $this->renderTable($tableRows);
            $tableRows = [];
            $inTable = false;
        }
        if ($inList) {
            $html[] = '</ul>';
            $inList = false;
        }
        if ($closeQuote && $inBlockquote) {
            $html[] = '<blockquote><p>' . implode("<br>", array_map([$this, 'formatInlines'], $blockquoteLines)) . '</p></blockquote>';
            $blockquoteLines = [];
            $inBlockquote = false;
        }
        if ($inAdmonition) {
            $html[] = $this->renderAdmonition('note', $admonitionLines);
            $admonitionLines = [];
            $inAdmonition = false;
        }
    }

    private function renderAdmonition(string $type, array $lines): string {
        $labels = [
            'note'      => ['label' => 'یادداشت', 'icon' => 'ℹ️'],
            'tip'       => ['label' => 'نکته کاربردی', 'icon' => '💡'],
            'important' => ['label' => 'مهم و الزامی', 'icon' => '📌'],
            'warning'   => ['label' => 'هشدار', 'icon' => '⚠️'],
            'caution'   => ['label' => 'توجه امنیتی', 'icon' => '🛑'],
        ];
        $meta = $labels[$type] ?? $labels['note'];
        $body = implode("<br>", array_map([$this, 'formatInlines'], $lines));
        return "<div class=\"admonition admonition-{$type}\">"
             . "<div class=\"admonition-title\"><span class=\"admonition-icon\">{$meta['icon']}</span> <strong>{$meta['label']}</strong></div>"
             . "<div class=\"admonition-body\">{$body}</div>"
             . "</div>";
    }

    private function renderTable(array $lines): string {
        if (empty($lines)) return '';
        $html = '<div class="table-wrap"><table class="doc-table">';
        $isHeader = true;

        foreach ($lines as $row) {
            $row = trim($row, '|');
            $cols = array_map('trim', explode('|', $row));
            // بررسی سطر جداکننده (---|---|---)
            if (isset($cols[0]) && preg_match('/^:?-+:?$/', $cols[0])) {
                $isHeader = false;
                continue;
            }
            $tag = $isHeader ? 'th' : 'td';
            $html .= '<tr>';
            foreach ($cols as $c) {
                $html .= "<{$tag}>" . $this->formatInlines($c) . "</{$tag}>";
            }
            $html .= '</tr>';
        }

        $html .= '</table></div>';
        return $html;
    }

    public function formatInlines(string $s): string {
        // بازنویسی لینک‌های درون مستندات (مثال: [متن](01-getting-started.md) -> ?doc=01-getting-started)
        $s = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function($m) {
            $text = $m[1];
            $url = $m[2];
            if (preg_match('/^([0-9a-zA-Z_-]+)\.md(#.*)?$/', $url, $um)) {
                $docId = $um[1];
                $hash = $um[2] ?? '';
                return "<a href=\"docs.php?doc={$docId}{$hash}\" class=\"doc-link\">{$text}</a>";
            }
            $target = str_starts_with($url, 'http') ? ' target="_blank" rel="noopener noreferrer"' : '';
            return "<a href=\"{$url}\" class=\"doc-link\"{$target}>{$text}</a>";
        }, $s);

        // بولد و ایتالیک
        $s = preg_replace('/\*\*\*([^*]+)\*\*\*/', '<strong><em>$1</em></strong>', $s);
        $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s);
        $s = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $s);
        $s = preg_replace('/~~([^~]+)~~/', '<del>$1</del>', $s);

        // کدهای درون خطی (`code`)
        $s = preg_replace('/`([^`]+)`/', '<code class="inline-code">$1</code>', $s);

        // نشان‌های وضعیت (🟢, 🟡, 🔴)
        $s = str_replace(
            ['🟢', '🟡', '🔴'],
            [
                '<span class="badge badge-green">🟢 فعال</span>',
                '<span class="badge badge-yellow">🟡 در حال توسعه</span>',
                '<span class="badge badge-red">🔴 برنامه‌ریزی‌شده</span>'
            ],
            $s
        );

        return $s;
    }

    private function slugify(string $text): string {
        $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $slug = preg_replace('/[\s-]+/u', '-', $slug);
        $slug = trim($slug, '-');
        return $slug ?: ('sec-' . substr(md5($text), 0, 6));
    }
}

// پارس محتوا و استخراج فهرست عناوین
$parser = new MacsaMarkdownParser();
$parsedHtml = $parser->parse($rawMarkdown);
$pageHeadings = $parser->headings;

// محاسبه زمان تخمینی مطالعه
$wordCount = count(preg_split('/\s+/u', strip_tags($rawMarkdown)));
$readingMinutes = max(1, (int)ceil($wordCount / 180));
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($currentMeta['title'], ENT_QUOTES, 'UTF-8') ?> | مستندات و راهنمای کاربری مکسا</title>
<link rel="stylesheet" href="/font.css">
<script>
(function(){
  try {
    var t = localStorage.getItem('maxa-theme');
    if(t === 'dark' || (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.setAttribute('data-theme', 'dark');
    } else {
      document.documentElement.setAttribute('data-theme', 'light');
    }
  } catch(e){}
})();
</script>
<style>
/* ============================================================================
 *  سیستم طراحی Read the Docs / MkDocs Material سفارشی برای مکسا
 * ========================================================================== */
:root {
  --primary: #007b7a;
  --primary-hover: #006665;
  --primary-light: #4fb2b0;
  --primary-bg: rgba(0, 123, 122, 0.08);
  --secondary: #f4a61e;
  --secondary-bg: rgba(244, 166, 30, 0.12);
  --bg-page: #f8fafc;
  --bg-surface: #ffffff;
  --bg-sidebar: #f1f5f9;
  --text-main: #0f172a;
  --text-muted: #64748b;
  --text-light: #94a3b8;
  --border: #e2e8f0;
  --border-subtle: #f1f5f9;
  --border-hover: #cbd5e1;
  --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
  --shadow-md: 0 4px 16px -2px rgba(15, 23, 42, 0.07);
  --shadow-lg: 0 20px 35px -8px rgba(15, 23, 42, 0.12);
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --sidebar-w: 295px;
  --toc-w: 240px;
  --header-h: 64px;
}

[data-theme="dark"] {
  --primary: #2dd4bf;
  --primary-hover: #14b8a6;
  --primary-light: #5eead4;
  --primary-bg: rgba(45, 212, 191, 0.12);
  --secondary: #fbbf24;
  --secondary-bg: rgba(251, 191, 36, 0.15);
  --bg-page: #0b1120;
  --bg-surface: #111827;
  --bg-sidebar: #0f172a;
  --text-main: #f8fafc;
  --text-muted: #94a3b8;
  --text-light: #64748b;
  --border: #1f2937;
  --border-subtle: #172033;
  --border-hover: #374151;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.5);
  --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.6);
  --shadow-lg: 0 24px 45px rgba(0, 0, 0, 0.7);
  color-scheme: dark;
}

* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, Tahoma, sans-serif;
  background-color: var(--bg-page);
  color: var(--text-main);
  line-height: 1.8;
  font-size: 15px;
  direction: rtl;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  -webkit-font-smoothing: antialiased;
}
a { color: inherit; text-decoration: none; }

/* ------------------- سربرگ اصلی (Top Navbar) ------------------- */
.site-header {
  position: sticky;
  top: 0;
  z-index: 50;
  height: var(--header-h);
  background: var(--bg-surface);
  border-bottom: 1px solid var(--border);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
}
.header-left, .header-right { display: flex; align-items: center; gap: 16px; }

.brand-wrap { display: flex; align-items: center; gap: 12px; font-weight: 900; font-size: 17px; color: var(--primary); }
.brand-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 9px;
  border-radius: 99px;
  background: var(--secondary-bg);
  color: var(--secondary);
  border: 1px solid rgba(244, 166, 30, 0.25);
  white-space: nowrap;
}

.search-trigger-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  height: 38px;
  padding: 0 14px;
  background: var(--bg-page);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  color: var(--text-muted);
  font-family: inherit;
  font-size: 13.5px;
  cursor: pointer;
  min-width: 240px;
  transition: border-color .2s, box-shadow .2s;
}
.search-trigger-btn:hover { border-color: var(--primary); color: var(--text-main); }
.search-kbd {
  margin-right: auto;
  font-size: 11px;
  font-family: monospace;
  background: var(--bg-surface);
  padding: 2px 7px;
  border-radius: 4px;
  border: 1px solid var(--border);
}

.header-btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  height: 38px;
  padding: 0 14px;
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-weight: 700;
  border: 1px solid var(--border);
  background: var(--bg-surface);
  color: var(--text-main);
  cursor: pointer;
  transition: all .2s;
}
.header-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-bg); }
.header-btn.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
.header-btn.primary:hover { background: var(--primary-hover); }

/* ------------------- لایه‌بندی ۳ ستونه Read the Docs ------------------- */
.layout-container {
  display: flex;
  flex: 1;
  width: 100%;
  max-width: 1600px;
  margin: 0 auto;
  position: relative;
}

/* سایدبار سمت راست (ناوبری فصول) */
.doc-sidebar {
  width: var(--sidebar-w);
  flex-shrink: 0;
  background: var(--bg-sidebar);
  border-left: 1px solid var(--border);
  height: calc(100vh - var(--header-h));
  position: sticky;
  top: var(--header-h);
  overflow-y: auto;
  padding: 20px 14px;
}
.sidebar-filter-input {
  width: 100%;
  height: 36px;
  padding: 0 12px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--bg-surface);
  color: var(--text-main);
  font-family: inherit;
  font-size: 12.5px;
  margin-bottom: 18px;
}
.sidebar-filter-input:focus { outline: none; border-color: var(--primary); }

.nav-group-title {
  font-size: 11.5px;
  font-weight: 800;
  text-transform: uppercase;
  color: var(--text-muted);
  letter-spacing: .02em;
  padding: 12px 10px 6px;
  display: flex;
  align-items: center;
  gap: 7px;
}
.nav-link-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 8px 12px;
  border-radius: var(--radius-sm);
  font-size: 13.5px;
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 3px;
  transition: all .16s ease;
}
.nav-link-item:hover { background: var(--bg-surface); color: var(--primary); }
.nav-link-item.active {
  background: var(--primary-bg);
  color: var(--primary);
  font-weight: 800;
  border-right: 3px solid var(--primary);
}
.nav-link-title { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ناحیه مرکزی محتوا */
.doc-main {
  flex: 1;
  min-width: 0;
  padding: 36px 48px 80px;
}
.doc-article-wrap {
  max-width: 890px;
  margin: 0 auto;
}

/* سایدبار سمت چپ (در این صفحه / TOC) */
.doc-toc-wrap {
  width: var(--toc-w);
  flex-shrink: 0;
  height: calc(100vh - var(--header-h));
  position: sticky;
  top: var(--header-h);
  overflow-y: auto;
  padding: 30px 18px;
}
.toc-card {
  border-right: 1px solid var(--border);
  padding-right: 14px;
}
.toc-title {
  font-size: 12.5px;
  font-weight: 800;
  color: var(--text-main);
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.toc-list { list-style: none; }
.toc-item { margin-bottom: 6px; }
.toc-link {
  display: block;
  font-size: 12.5px;
  color: var(--text-muted);
  line-height: 1.5;
  transition: color .18s;
  padding: 3px 0;
}
.toc-link:hover, .toc-link.active { color: var(--primary); font-weight: 700; }
.toc-link.h3 { padding-right: 14px; font-size: 12px; }

/* ------------------- استایل مقالات و متون مستندات ------------------- */
.breadcrumbs {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  color: var(--text-light);
  margin-bottom: 20px;
}
.breadcrumbs a:hover { color: var(--primary); }
.breadcrumbs .sep { opacity: .6; }

.article-header {
  border-bottom: 1px solid var(--border);
  padding-bottom: 20px;
  margin-bottom: 32px;
}
.article-title {
  font-size: clamp(26px, 3.5vw, 36px);
  font-weight: 900;
  line-height: 1.4;
  color: var(--text-main);
  letter-spacing: -.02em;
  margin-bottom: 14px;
}
.article-meta-row {
  display: flex;
  align-items: center;
  gap: 16px;
  font-size: 12.5px;
  color: var(--text-muted);
  flex-wrap: wrap;
}
.meta-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 10px;
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 6px;
}

/* تیترهای داخلی */
.doc-heading {
  position: relative;
  font-weight: 800;
  color: var(--text-main);
  margin-top: 40px;
  margin-bottom: 16px;
  line-height: 1.4;
  scroll-margin-top: 80px;
}
.doc-h2 { font-size: 22px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 8px; }
.doc-h3 { font-size: 17px; }
.header-anchor {
  opacity: 0;
  margin-right: 8px;
  color: var(--primary);
  font-weight: 400;
  transition: opacity .2s;
}
.doc-heading:hover .header-anchor { opacity: 1; }

.doc-hr { border: none; border-top: 1px solid var(--border); margin: 32px 0; }
p { margin-bottom: 16px; color: var(--text-main); line-height: 1.9; }

/* باکس‌های اخطار و کال‌اوت (Admonitions) */
.admonition {
  border-radius: var(--radius-md);
  border-right: 4px solid var(--primary);
  background: var(--bg-surface);
  padding: 16px 20px;
  margin: 22px 0;
  box-shadow: var(--shadow-sm);
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  border-left: 1px solid var(--border);
}
.admonition-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  margin-bottom: 8px;
}
.admonition-body { font-size: 14px; color: var(--text-main); line-height: 1.85; }
.admonition-note { border-right-color: #3b82f6; background: rgba(59, 130, 246, 0.05); }
.admonition-note .admonition-title { color: #2563eb; }
.admonition-tip { border-right-color: #10b981; background: rgba(16, 185, 129, 0.05); }
.admonition-tip .admonition-title { color: #059669; }
.admonition-important { border-right-color: var(--primary); background: var(--primary-bg); }
.admonition-important .admonition-title { color: var(--primary); }
.admonition-warning { border-right-color: #f59e0b; background: rgba(245, 158, 11, 0.06); }
.admonition-warning .admonition-title { color: #d97706; }
.admonition-caution { border-right-color: #ef4444; background: rgba(239, 68, 68, 0.06); }
.admonition-caution .admonition-title { color: #dc2626; }

/* جداول */
.table-wrap { overflow-x: auto; margin: 24px 0; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-surface); }
.doc-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.doc-table th { background: var(--bg-page); padding: 12px 14px; font-weight: 800; text-align: right; border-bottom: 1px solid var(--border); color: var(--text-muted); }
.doc-table td { padding: 12px 14px; border-bottom: 1px solid var(--border); vertical-align: middle; }
.doc-table tr:last-child td { border-bottom: none; }
.doc-table tr:hover td { background: var(--primary-bg); }

/* کدها و پیش‌نمایش */
.code-box {
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--bg-surface);
  margin: 20px 0;
  overflow: hidden;
}
.code-box-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 14px;
  background: var(--bg-page);
  border-bottom: 1px solid var(--border);
  font-size: 11.5px;
  font-weight: 800;
  color: var(--text-muted);
}
.copy-code-btn {
  background: none;
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 3px 8px;
  font-family: inherit;
  font-size: 11px;
  cursor: pointer;
  color: var(--text-muted);
}
.copy-code-btn:hover { color: var(--primary); border-color: var(--primary); }
pre { padding: 16px; overflow-x: auto; font-family: monospace; font-size: 13px; line-height: 1.7; direction: ltr; text-align: left; }
.inline-code { font-family: monospace; background: var(--primary-bg); color: var(--primary); padding: 2px 6px; border-radius: 4px; font-size: 13px; direction: ltr; display: inline-block; }

/* نمودارهای فرآیندی Mermaid */
.mermaid-card {
  border: 1px dashed var(--primary-light);
  border-radius: var(--radius-md);
  padding: 16px;
  background: var(--primary-bg);
  margin: 22px 0;
}
.mermaid-header { font-size: 12px; font-weight: 800; color: var(--primary); margin-bottom: 10px; }
.mermaid-code { direction: ltr; font-size: 12.5px; color: var(--text-main); }

/* لیست‌ها و چک‌باکس‌ها */
.doc-list, .doc-list-ordered { padding-right: 24px; margin-bottom: 20px; }
.doc-list li, .doc-list-ordered li { margin-bottom: 8px; line-height: 1.8; }
.task-item { list-style: none; display: flex; align-items: center; gap: 8px; }
.task-item input { accent-color: var(--primary); width: 16px; height: 16px; }

/* پیوندها و نشان‌ها */
.doc-link { color: var(--primary); font-weight: 700; text-decoration: underline; text-underline-offset: 3px; }
.doc-link:hover { color: var(--primary-hover); }
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 99px; font-size: 12px; font-weight: 700; white-space: nowrap; }
.badge-green { background: rgba(16, 185, 129, 0.12); color: #059669; }
.badge-yellow { background: rgba(245, 158, 11, 0.14); color: #d97706; }
.badge-red { background: rgba(239, 68, 68, 0.12); color: #dc2626; }

/* کارت‌های ناوبری قبلی و بعدی */
.article-nav-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-top: 50px;
  padding-top: 30px;
  border-top: 1px solid var(--border);
}
.nav-card {
  display: flex;
  flex-direction: column;
  padding: 16px 20px;
  border-radius: var(--radius-md);
  border: 1px solid var(--border);
  background: var(--bg-surface);
  transition: all .2s;
}
.nav-card:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: var(--shadow-sm); }
.nav-card-dir { font-size: 12px; font-weight: 700; color: var(--text-light); margin-bottom: 4px; }
.nav-card-title { font-size: 14.5px; font-weight: 800; color: var(--text-main); }
.nav-card.next { text-align: left; }

/* ------------------- مودال جستجوی زنده (Live Search Modal) ------------------- */
.search-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  z-index: 100;
  display: none;
  align-items: flex-start;
  justify-content: center;
  padding-top: 80px;
}
.search-modal-backdrop.open { display: flex; }
.search-dialog {
  width: 100%;
  max-width: 620px;
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  overflow: hidden;
}
.search-input-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 20px;
  border-bottom: 1px solid var(--border);
}
.search-main-input {
  flex: 1;
  border: none;
  background: none;
  font-family: inherit;
  font-size: 16px;
  font-weight: 600;
  color: var(--text-main);
}
.search-main-input:focus { outline: none; }
.search-results-list {
  max-height: 420px;
  overflow-y: auto;
  padding: 10px;
}
.search-result-item {
  display: flex;
  flex-direction: column;
  padding: 10px 14px;
  border-radius: var(--radius-sm);
  margin-bottom: 4px;
  cursor: pointer;
  transition: background .15s;
}
.search-result-item:hover, .search-result-item.highlighted {
  background: var(--primary-bg);
}
.search-result-title { font-size: 14px; font-weight: 800; color: var(--text-main); }
.search-result-cat { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }

/* ------------------- ریسپانسیو موبایل ------------------- */
.mobile-menu-btn { display: none; }
@media (max-width: 1180px) {
  .doc-toc-wrap { display: none; }
  .doc-main { padding: 30px 24px 60px; }
}
@media (max-width: 820px) {
  .search-trigger-btn { min-width: auto; padding: 0 10px; }
  .search-trigger-btn span, .search-kbd { display: none; }
  .mobile-menu-btn { display: inline-flex; }
  .doc-sidebar {
    position: fixed;
    top: var(--header-h);
    right: 0;
    bottom: 0;
    z-index: 60;
    transform: translateX(100%);
    transition: transform .25s ease;
    box-shadow: var(--shadow-lg);
  }
  .doc-sidebar.open { transform: translateX(0); }
  .article-nav-row { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- سربرگ اصلی مستندات -->
<header class="site-header">
  <div class="header-right">
    <button type="button" class="header-btn mobile-menu-btn" onclick="toggleMobileSidebar()" aria-label="منو">☰</button>
    <a href="docs.php" class="brand-wrap">
      <span style="font-size: 22px;">🕊️</span>
      <span>مستندات مکسا</span>
    </a>
    <span class="brand-badge">نسخه آزمایشی v1.0.0</span>
  </div>

  <div class="header-left">
    <button type="button" class="search-trigger-btn" onclick="openSearchModal()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      <span>جستجو در مستندات...</span>
      <span class="search-kbd">Ctrl+K</span>
    </button>

    <button type="button" class="header-btn" onclick="toggleTheme()" id="themeToggleBtn" title="تغییر تم">
      <span id="themeIcon">🌙</span>
    </button>

    <a href="index.php" class="header-btn primary" title="بازگشت به پیشخوان اداری مکسا">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      <span>پیشخوان ادمین</span>
    </a>
  </div>
</header>

<!-- بدنه اصلی ۳ ستونه -->
<div class="layout-container">

  <!-- ستون راست: ناوبری فصول و درختی -->
  <aside class="doc-sidebar" id="docSidebar">
    <input type="text" id="sidebarFilter" class="sidebar-filter-input" placeholder="فیلتر بخش‌ها..." oninput="filterSidebar(this.value)">

    <nav>
      <?php foreach ($DOCS_NAV as $group): ?>
        <div class="nav-group-section">
          <div class="nav-group-title">
            <span><?= htmlspecialchars($group['category'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <?php foreach ($group['items'] as $item): ?>
            <?php $isActive = ($item['id'] === $currentDocId); ?>
            <a href="docs.php?doc=<?= urlencode($item['id']) ?>" class="nav-link-item <?= $isActive ? 'active' : '' ?>">
              <span class="nav-link-title"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></span>
              <span class="badge badge-subtle"><?= $item['status'] ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </nav>
  </aside>

  <!-- ستون میانی: محتوای مقاله -->
  <main class="doc-main">
    <div class="doc-article-wrap">
      
      <!-- خرده‌نان (Breadcrumbs) -->
      <div class="breadcrumbs">
        <a href="index.php">مکسا</a>
        <span class="sep">/</span>
        <a href="docs.php">راهنمای سامانه</a>
        <span class="sep">/</span>
        <span><?= htmlspecialchars($currentMeta['category'], ENT_QUOTES, 'UTF-8') ?></span>
        <span class="sep">/</span>
        <span style="color: var(--text-main); font-weight: 700;"><?= htmlspecialchars($currentMeta['title'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>

      <!-- سربرگ مقاله -->
      <header class="article-header">
        <h1 class="article-title"><?= htmlspecialchars($currentMeta['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="article-meta-row">
          <span class="meta-chip">⏱️ زمان تخمینی مطالعه: <?= $readingMinutes ?> دقیقه</span>
          <span class="meta-chip">وضعیت: <?= $currentMeta['status'] ?></span>
          <span class="meta-chip">مرجع: نسخه آزمایشی سامانه</span>
        </div>
      </header>

      <!-- متن اصلی پارس‌شده مارک‌داون -->
      <article class="article-content">
        <?= $parsedHtml ?>
      </article>

      <!-- ناوبری مقاله قبلی / بعدی -->
      <div class="article-nav-row">
        <?php if ($prevDoc): ?>
          <a href="docs.php?doc=<?= urlencode($prevDoc['id']) ?>" class="nav-card prev">
            <span class="nav-card-dir">← بخش قبلی</span>
            <span class="nav-card-title"><?= htmlspecialchars($prevDoc['title'], ENT_QUOTES, 'UTF-8') ?></span>
          </a>
        <?php else: ?>
          <div></div>
        <?php endif; ?>

        <?php if ($nextDoc): ?>
          <a href="docs.php?doc=<?= urlencode($nextDoc['id']) ?>" class="nav-card next">
            <span class="nav-card-dir">بخش بعدی →</span>
            <span class="nav-card-title"><?= htmlspecialchars($nextDoc['title'], ENT_QUOTES, 'UTF-8') ?></span>
          </a>
        <?php else: ?>
          <div></div>
        <?php endif; ?>
      </div>

    </div>
  </main>

  <!-- ستون چپ: در این صفحه (On this page) -->
  <aside class="doc-toc-wrap">
    <div class="toc-card">
      <div class="toc-title">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        <span>در این صفحه</span>
      </div>

      <?php if (!empty($pageHeadings)): ?>
        <ul class="toc-list" id="tocList">
          <?php foreach ($pageHeadings as $h): ?>
            <li class="toc-item">
              <a href="#<?= urlencode($h['id']) ?>" class="toc-link <?= $h['level'] === 3 ? 'h3' : 'h2' ?>">
                <?= htmlspecialchars($h['text'], ENT_QUOTES, 'UTF-8') ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p style="font-size: 12px; color: var(--text-light);">سرفصل فرعی برای این صفحه ثبت نشده است.</p>
      <?php endif; ?>

      <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">
      <button type="button" class="header-btn" style="width: 100%; justify-content: center; font-size: 12px;" onclick="window.print()">
        🖨️ چاپ این سند / PDF
      </button>
    </div>
  </aside>

</div>

<!-- مودال جستجوی زنده (Live Search Modal) -->
<div class="search-modal-backdrop" id="searchBackdrop" onclick="handleBackdropClick(event)">
  <div class="search-dialog" role="dialog" aria-modal="true">
    <div class="search-input-wrap">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="text" id="searchModalInput" class="search-main-input" placeholder="عنوان یا کلمه کلیدی را تایپ کنید..." autocomplete="off" oninput="performLiveSearch(this.value)">
      <button type="button" class="header-btn" style="padding: 4px 8px; font-size: 11px;" onclick="closeSearchModal()">ESC</button>
    </div>
    <div class="search-results-list" id="searchResultsList">
      <p style="font-size: 13px; color: var(--text-muted); text-align: center; padding: 20px;">عبارتی را برای جستجو در میان تمام ۲۲ فصل راهنما وارد کنید.</p>
    </div>
  </div>
</div>

<script>
// نمایه جستجوی مقالات در کلاینت
const DOCS_INDEX = <?= json_encode($FLAT_ITEMS, JSON_UNESCAPED_UNICODE) ?>;

// تغییر تم تاریک / روشن با همگام‌سازی با داشبورد
function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme') || 'light';
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  try { localStorage.setItem('maxa-theme', next); } catch(e){}
  updateThemeIcon(next);
}
function updateThemeIcon(theme) {
  const ic = document.getElementById('themeIcon');
  if(ic) ic.textContent = theme === 'dark' ? '☀️' : '🌙';
}
updateThemeIcon(document.documentElement.getAttribute('data-theme'));

// سایدبار موبایل
function toggleMobileSidebar() {
  const sb = document.getElementById('docSidebar');
  if(sb) sb.classList.toggle('open');
}

// فیلتر سریع در سایدبار
function filterSidebar(query) {
  const q = (query || '').toLowerCase().trim();
  const items = document.querySelectorAll('.nav-link-item');
  items.forEach(el => {
    const text = el.textContent.toLowerCase();
    el.style.display = text.includes(q) ? 'flex' : 'none';
  });
}

// کپی کردن کدها
function copyCodeBlock(btn) {
  const pre = btn.closest('.code-box').querySelector('pre code');
  if(pre) {
    navigator.clipboard.writeText(pre.innerText).then(() => {
      btn.textContent = 'کپی شد!';
      setTimeout(() => btn.textContent = 'کپی', 2000);
    });
  }
}

// اسکرول نرم و فعال‌سازی TOC
document.querySelectorAll('.toc-link').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    const targetId = decodeURIComponent(link.getAttribute('href').substring(1));
    const targetEl = document.getElementById(targetId);
    if(targetEl) {
      targetEl.scrollIntoView({ behavior: 'smooth' });
      history.pushState(null, '', '#' + encodeURIComponent(targetId));
    }
  });
});

// مودال جستجو
function openSearchModal() {
  const b = document.getElementById('searchBackdrop');
  b.classList.add('open');
  const inp = document.getElementById('searchModalInput');
  inp.value = '';
  inp.focus();
  renderSearchResults(DOCS_INDEX);
}
function closeSearchModal() {
  document.getElementById('searchBackdrop').classList.remove('open');
}
function handleBackdropClick(e) {
  if(e.target.id === 'searchBackdrop') closeSearchModal();
}
document.addEventListener('keydown', e => {
  if((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    openSearchModal();
  }
  if(e.key === 'Escape') closeSearchModal();
});

function performLiveSearch(term) {
  const q = (term || '').toLowerCase().trim();
  if(!q) { renderSearchResults(DOCS_INDEX); return; }
  const matches = DOCS_INDEX.filter(it => {
    return it.title.toLowerCase().includes(q) ||
           (it.summary && it.summary.toLowerCase().includes(q)) ||
           it.category.toLowerCase().includes(q) ||
           it.id.toLowerCase().includes(q);
  });
  renderSearchResults(matches);
}

function renderSearchResults(items) {
  const list = document.getElementById('searchResultsList');
  if(!items.length) {
    list.innerHTML = '<p style="font-size: 13px; color: var(--text-muted); text-align: center; padding: 24px;">موردی یافت نشد.</p>';
    return;
  }
  list.innerHTML = items.map(it => `
    <a href="docs.php?doc=${encodeURIComponent(it.id)}" class="search-result-item">
      <div class="search-result-title">${it.title} ${it.status}</div>
      <div class="search-result-cat">${it.category} ${it.summary ? '— ' + it.summary : ''}</div>
    </a>
  `).join('');
}
</script>

</body>
</html>
