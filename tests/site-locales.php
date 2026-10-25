<?php
declare(strict_types=1);
require_once __DIR__.'/../public_html/core/site-locale.php';
function check(bool $condition,string $message): void { if (!$condition) throw new RuntimeException($message); }
$_SERVER['REQUEST_URI']='/en/home';
check(site_locale()==='en' && site_direction()==='ltr','English language and direction');
check(site_text('تماس با ما')==='Contact us','English static label');
check(site_url('/history?x=1#intro')==='/en/history?x=1#intro','Public navigation preserves query and fragment');
foreach(['/dashboard/login.php','/benefactor-dashboard/','/api/user/me','/api-employees.php','/uploads/document.pdf','/assets/app.js','#section','https://example.org/page','javascript:alert(1)','//example.org'] as $url) {
    check(site_url($url)===$url,'Protected/external URL changed: '.$url);
}
check(site_url('/ar/home','fa')==='/home','Switching back to Persian');
check(site_url('/home','ar')==='/ar/home','Switching to Arabic');
$live='<h1 data-site-dynamic>تماس با ما</h1><p>خدمات مکسا</p>';
$document=site_document('<html lang="fa" dir="rtl"><head></head><body>'.$live.site_language_picker().'</body></html>');
check(str_contains($document,$live),'Live text must never pass through the translation catalogue');
foreach(['href="/home"','href="/en/home"','href="/ar/home"'] as $target) check(str_contains($document,$target),'Language switch lost a target');
check(str_contains($document,'lang="en" dir="ltr"'),'Document attributes');
check(site_static_markup('<b>تماس با ما</b>')==='<b>Contact us</b>','Literal HTML interface label');
check(site_static_markup('" class="card">تماس با ما</a>')==='" class="card">Contact us</a>','Template fragments preserve markup');
$_SERVER['REQUEST_URI']='/ar/home';
check(site_text('تماس با ما')==='اتصل بنا' && site_direction()==='rtl','Arabic labels and direction');
$_SERVER['REQUEST_URI']='/dashboard/login.php?lang=en';
check(site_locale()==='fa' && site_text('تماس با ما')==='تماس با ما','Admin remains Persian');
$_SERVER['REQUEST_URI']='/benefactor-dashboard/';
check(site_locale()==='fa','Benefactor panel is excluded');
check(site_static_markup('<b>مکسا &amp; همراهان</b>')==='<b>مکسا &amp; همراهان</b>','Persian literal markup must remain byte-for-byte unchanged');
$catalogue=json_decode(file_get_contents(__DIR__.'/../public_html/core/locales/static.json'),true,512,JSON_THROW_ON_ERROR);
foreach($catalogue as $source=>$translations) {
    check(is_string($source) && $source!=='','Catalogue source');
    foreach(['en','ar'] as $locale) {
        check(isset($translations[$locale]) && is_string($translations[$locale]) && trim($translations[$locale])!=='','Missing '.$locale.' translation: '.$source);
        check(!str_contains($translations[$locale],'<?'),'Translation contains executable markup');
    }
    check(!preg_match('/[\x{0600}-\x{06FF}]/u',$translations['en']),'Untranslated English catalogue entry: '.$source);
}
$files=array_merge(glob(__DIR__.'/../public_html/*.php'),glob(__DIR__.'/../public_html/dashboard/components/*/component.php'),[__DIR__.'/../public_html/dashboard/page-view.php',__DIR__.'/../public_html/dashboard/news-view.php',__DIR__.'/../public_html/core/site-locale.php']);
$references=0;
foreach($files as $file) {
    $tokens=token_get_all(file_get_contents($file));
    foreach($tokens as $index=>$token) {
        if (!is_array($token) || $token[0]!==T_STRING || !in_array($token[1],['site_e','site_text','site_js_text','site_js_fragment'],true)) continue;
        $next=$index+1;
        while(isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0]===T_WHITESPACE) $next++;
        if (($tokens[$next] ?? '')!=='(') continue;
        $next++;
        while(isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0]===T_WHITESPACE) $next++;
        $literal=$tokens[$next] ?? null;
        if (!is_array($literal) || $literal[0]!==T_CONSTANT_ENCAPSED_STRING || $literal[1][0]!=="'") continue;
        $source=str_replace(["\\'","\\\\"],["'","\\"],substr($literal[1],1,-1));
        // HTML fragments contain several separately catalogued labels.
        if (str_contains($source,'<') || str_contains($source,'>')) continue;
        $key=trim(preg_replace('/\s+/u',' ',html_entity_decode($source,ENT_QUOTES|ENT_HTML5,'UTF-8')));
        if (!preg_match('/[\x{0600}-\x{06FF}]/u',$key)) continue;
        check(isset($catalogue[$key]),'Missing source translation in '.basename($file).': '.$key);
        $references++;
    }
}
echo "PASS: $references literal template references resolve to the catalogue.\n";
echo 'PASS: '.count($catalogue)." bilingual entries; static/live content separation; panels and navigation.\n";
