<?php
/* ============================================================================
 *  انتقادات و پیشنهادات — پنل مکسا  (فعلاً فقط ظاهر؛ بعداً به بک‌اند وصل می‌شود)
 *  آرایه‌ی $items را با کوئریِ واقعیِ دیتابیس جایگزین کنید. هر آیتم:
 *    name, type ('suggestion' | 'criticism'), message, date,
 *    read (bool), approved (bool: تأیید جهت نمایش در سایت), replied (bool)
 * ========================================================================== */
$items = [
  ['name'=>'سارا محمدی','type'=>'suggestion','message'=>'پیشنهاد می‌کنم امکان پرداختِ اقساطی برای کمک‌ها اضافه شود تا خیرینِ بیشتری بتوانند مشارکت کنند.','date'=>'۱۴۰۳/۰۵/۱۸ – ۱۴:۳۰','read'=>false,'approved'=>false,'replied'=>false,'color'=>'#007b7a'],
  ['name'=>'علی رضایی','type'=>'criticism','message'=>'بارگذاریِ صفحهٔ کمپین‌ها روی موبایل کمی کند است؛ لطفاً بررسی کنید.','date'=>'۱۴۰۳/۰۵/۱۷ – ۰۹:۱۲','read'=>false,'approved'=>false,'replied'=>false,'color'=>'#f4a61e'],
  ['name'=>'مریم احمدی','type'=>'suggestion','message'=>'اگر گزارشِ شفاف از محلِ هزینهٔ کمک‌ها منتشر شود، اعتمادِ بیشتری ایجاد می‌شود.','date'=>'۱۴۰۳/۰۵/۱۵ – ۱۹:۴۵','read'=>true,'approved'=>true,'replied'=>false,'color'=>'#8b5cf6'],
  ['name'=>'حسین کریمی','type'=>'criticism','message'=>'پیامکِ تأییدِ پرداخت با تأخیر ارسال می‌شود؛ گاهی چند ساعت طول می‌کشد.','date'=>'۱۴۰۳/۰۵/۱۲ – ۱۱:۰۰','read'=>true,'approved'=>false,'replied'=>true,'reply_text'=>'سلام و سپاس از اطلاع‌رسانی شما. مشکلِ تأخیرِ پیامک بررسی و درگاهِ ارسال به‌روزرسانی شد.','color'=>'#16a37a'],
  ['name'=>'زهرا نیک‌نام','type'=>'suggestion','message'=>'یک بخشِ «داستانِ خیرین» اضافه کنید تا تجربهٔ کمک‌کنندگان به اشتراک گذاشته شود.','date'=>'۱۴۰۳/۰۵/۱۰ – ۰۸:۲۰','read'=>true,'approved'=>true,'replied'=>true,'reply_text'=>'پیشنهادِ بسیار خوبی است؛ بخشِ «داستانِ خیرین» در برنامهٔ توسعهٔ سایت قرار گرفت. سپاس‌گزاریم.','color'=>'#e0556b'],
];

$total    = count($items);
$unread   = 0; $approved = 0; $replied = 0;
foreach($items as $it){
  if(empty($it['read']))     $unread++;
  if(!empty($it['approved'])) $approved++;
  if(!empty($it['replied']))  $replied++;
}
if(!function_exists('fb_fa')){
  function fb_fa($n){ return strtr((string)$n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']); }
}
if(!function_exists('fb_initials')){
  function fb_initials($n){ $p=preg_split('/\s+/',trim($n)); $a=mb_substr($p[0]??'',0,1,'UTF-8'); $b=isset($p[1])?mb_substr($p[1],0,1,'UTF-8'):''; return $a.$b; }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>انتقادات و پیشنهادات | پنل مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-secondary-dark:#b9760a; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b; --violet:#7c4ddb;
  --primary-08:rgba(0,123,122,.08); --primary-12:rgba(0,123,122,.12);
  --secondary-12:rgba(244,166,30,.16); --danger-12:rgba(224,85,107,.12);
  --success-12:rgba(22,163,122,.14); --violet-12:rgba(124,77,219,.14);
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --shadow-lg:0 20px 44px -14px rgba(0,102,101,.20),0 8px 20px -10px rgba(16,40,40,.12);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a; --color-secondary-dark:#e0a528;
  --primary-08:rgba(79,178,176,.10); --primary-12:rgba(79,178,176,.16);
  --secondary-12:rgba(244,166,30,.18); --danger-12:rgba(224,85,107,.16);
  --success-12:rgba(22,163,122,.18); --violet-12:rgba(124,77,219,.20);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  --shadow-lg:0 24px 48px -16px rgba(0,0,0,.62),0 10px 24px -12px rgba(0,0,0,.5);
  color-scheme:dark; background-color:var(--color-bg);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;padding:28px 22px;transition:background .3s,color .3s}
*::-webkit-scrollbar{width:9px;height:9px}
*::-webkit-scrollbar-thumb{background:rgba(0,123,122,.20);border-radius:99px;border:2px solid transparent;background-clip:padding-box}
[data-theme="dark"] *::-webkit-scrollbar-thumb{background:rgba(255,255,255,.16);background-clip:padding-box}

.fb-wrap{max-width:900px;margin:0 auto}

.fb-head{display:flex;align-items:center;gap:16px;margin-bottom:20px}
.fb-head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.fb-head-ic svg{width:27px;height:27px}
.fb-head h1{font-size:22px;font-weight:800;letter-spacing:-.01em}
.fb-head p{font-size:13px;color:var(--color-muted);margin-top:3px}

/* تب‌های فیلتر */
.fb-tabs{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
.fb-tab{font-family:inherit;text-align:start;cursor:pointer;background:var(--color-surface);border:1.5px solid var(--color-border);border-radius:16px;
  padding:14px 16px;box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:12px;transition:border-color .2s,box-shadow .25s,transform .15s var(--ease)}
.fb-tab:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.fb-tab.active{border-color:var(--color-primary)}
.fb-tab-ic{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;flex-shrink:0}
.fb-tab-ic svg{width:21px;height:21px}
.fb-tab b{font-size:21px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums;display:block}
.fb-tab span{font-size:11.5px;color:var(--color-muted);font-weight:600;margin-top:4px;display:block;line-height:1.5}
.ic-all{background:var(--primary-08);color:var(--color-primary)}
.ic-site{background:var(--secondary-12);color:var(--color-secondary-dark)}
.ic-reply{background:var(--violet-12);color:var(--violet)}
.ic-unread{background:var(--danger-12);color:var(--danger)}

/* کارت‌های بازخورد */
.fb-list{display:flex;flex-direction:column;gap:14px}
.fb-card{display:flex;gap:15px;background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);
  box-shadow:var(--shadow-sm);padding:18px 20px;transition:box-shadow .25s var(--ease),border-color .2s;position:relative}
.fb-card:hover{box-shadow:var(--shadow-md)}
.fb-card.unread{border-inline-start:4px solid var(--color-secondary)}
.fb-av{width:46px;height:46px;border-radius:14px;flex-shrink:0;display:grid;place-items:center;color:#fff;font-weight:700;font-size:15px;box-shadow:var(--shadow-sm)}
.fb-body{flex:1;min-width:0}
.fb-top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.fb-meta{display:flex;align-items:center;gap:10px;min-width:0}
.fb-name{font-weight:800;font-size:14.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.fb-badge{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;padding:4px 11px;border-radius:99px;white-space:nowrap}
.fb-badge .dot{width:6px;height:6px;border-radius:50%}
.fb-badge.type-suggestion{background:var(--primary-08);color:var(--color-primary-dark)} .type-suggestion .dot{background:var(--color-primary)}
.fb-badge.type-criticism{background:var(--secondary-12);color:var(--color-secondary-dark)} .type-criticism .dot{background:var(--color-secondary)}
.fb-date{font-size:11.5px;color:var(--color-muted);font-weight:600;white-space:nowrap}
.fb-msg{font-size:13.5px;color:var(--color-text);line-height:1.9;margin-top:10px}
.fb-replies{display:flex;flex-direction:column;gap:9px;margin-top:12px}
.fb-replies:empty{display:none}
.fb-reply-bubble{background:var(--primary-08);border:1px solid var(--color-border);border-inline-start:3px solid var(--color-primary);border-radius:12px;padding:11px 14px}
.fb-reply-bubble .h{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:800;color:var(--color-primary-dark);margin-bottom:5px}
.fb-reply-bubble .h svg{width:13px;height:13px}
.fb-reply-bubble p{font-size:13px;line-height:1.85;color:var(--color-text);word-break:break-word}

.fb-foot{display:flex;align-items:center;gap:9px;margin-top:14px;flex-wrap:wrap}
.fb-status{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700}
.fb-status .d{width:8px;height:8px;border-radius:50%}
.fb-status.is-unread{color:var(--color-secondary-dark)} .fb-status.is-unread .d{background:var(--color-secondary)}
.fb-status.is-read{color:var(--color-muted)} .fb-status.is-read .d{background:var(--color-muted)}
.fb-chip{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:99px}
.fb-chip svg{width:13px;height:13px}
.fb-chip.on-site{background:var(--success-12);color:var(--success)}
.fb-chip.replied{background:var(--violet-12);color:var(--violet)}
.fb-spacer{flex:1;min-width:8px}
.fb-btn{font-family:inherit;font-weight:700;font-size:12px;border:none;cursor:pointer;padding:7px 13px;border-radius:10px;
  display:inline-flex;align-items:center;gap:6px;transition:filter .2s,transform .14s}
.fb-btn svg{width:14px;height:14px}
.fb-btn:active{transform:scale(.96)}
.fb-btn-read{background:var(--primary-08);color:var(--color-primary-dark)}
.fb-btn-site{background:var(--secondary-12);color:var(--color-secondary-dark)}
.fb-btn-reply{background:var(--color-bg);color:var(--color-muted);border:1px solid var(--color-border)}
.fb-btn-remove{background:var(--danger-12);color:var(--danger)}
.fb-btn:hover{filter:brightness(.97)}
.fb-reply{margin-top:14px;padding-top:14px;border-top:1px dashed var(--color-border)}
.fb-reply[hidden]{display:none}
.fb-reply-input{width:100%;font-family:inherit;font-size:13.5px;color:var(--color-text);background:var(--color-bg);
  border:1.5px solid var(--color-border);border-radius:var(--radius-sm);padding:11px 13px;line-height:1.9;resize:vertical;min-height:82px;
  transition:border-color .2s,box-shadow .2s,background .2s}
.fb-reply-input:focus{outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:var(--color-surface)}
.fb-reply-actions{display:flex;gap:9px;margin-top:10px;justify-content:flex-end}
.fb-btn-send{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff}
.fb-btn-cancel{background:var(--color-bg);color:var(--color-muted);border:1px solid var(--color-border)}

.fb-empty{text-align:center;padding:50px 20px;color:var(--color-muted);font-weight:600;background:var(--color-surface);
  border:1.5px dashed var(--color-border);border-radius:var(--radius)}

@media (max-width:680px){
  body{padding:18px 14px}
  .fb-tabs{grid-template-columns:repeat(2,1fr)}
  .fb-head h1{font-size:19px}
}
</style>
</head>
<body>

<main class="fb-wrap">
  <header class="fb-head">
    <div class="fb-head-ic">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/></svg>
    </div>
    <div>
      <h1>انتقادات و پیشنهادات</h1>
      <p>پیام‌های دریافتی از مردم را مرور و مدیریت کنید.</p>
    </div>
  </header>

  <section class="fb-tabs">
    <button type="button" class="fb-tab active" data-cat="all" onclick="fbTab('all')">
      <div class="fb-tab-ic ic-all"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.5 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.5A2 2 0 0 0 16.8 4H7.2a2 2 0 0 0-1.7 1.5Z"/></svg></div>
      <div><b><?= fb_fa($total) ?></b><span>همهٔ پیام‌ها</span></div>
    </button>
    <button type="button" class="fb-tab" data-cat="approved" onclick="fbTab('approved')">
      <div class="fb-tab-ic ic-site"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></div>
      <div><b><?= fb_fa($approved) ?></b><span>تأییدشده برای سایت</span></div>
    </button>
    <button type="button" class="fb-tab" data-cat="replied" onclick="fbTab('replied')">
      <div class="fb-tab-ic ic-reply"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg></div>
      <div><b><?= fb_fa($replied) ?></b><span>پاسخ‌داده‌شده</span></div>
    </button>
    <button type="button" class="fb-tab" data-cat="unread" onclick="fbTab('unread')">
      <div class="fb-tab-ic ic-unread"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg></div>
      <div><b><?= fb_fa($unread) ?></b><span>خوانده‌نشده</span></div>
    </button>
  </section>

  <div class="fb-list" id="fbList">
    <?php foreach($items as $it):
      $isSug = $it['type'] === 'suggestion';
      $isUnread = empty($it['read']);
    ?>
    <article class="fb-card <?= $isUnread ? 'unread' : '' ?>"
             data-read="<?= $isUnread ? '0' : '1' ?>"
             data-approved="<?= !empty($it['approved']) ? '1' : '0' ?>"
             data-replied="<?= !empty($it['replied']) ? '1' : '0' ?>">
      <div class="fb-av" style="background:linear-gradient(135deg,<?= htmlspecialchars($it['color']) ?>,<?= htmlspecialchars($it['color']) ?>cc)"><?= htmlspecialchars(fb_initials($it['name'])) ?></div>
      <div class="fb-body">
        <div class="fb-top">
          <div class="fb-meta">
            <span class="fb-name"><?= htmlspecialchars($it['name']) ?></span>
          </div>
          <span class="fb-date"><?= htmlspecialchars($it['date']) ?></span>
        </div>
        <p class="fb-msg"><?= htmlspecialchars($it['message']) ?></p>

        <div class="fb-replies" data-role="replies"><?php if(!empty($it['reply_text'])): ?>
          <div class="fb-reply-bubble">
            <div class="h"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>پاسخِ مدیر</div>
            <p><?= htmlspecialchars($it['reply_text']) ?></p>
          </div>
        <?php endif; ?></div>

        <div class="fb-foot">
          <span class="fb-status <?= $isUnread ? 'is-unread' : 'is-read' ?>"><span class="d"></span><?= $isUnread ? 'خوانده‌نشده' : 'خوانده‌شده' ?></span>

          <span class="fb-chip on-site" data-role="on-site" style="<?= !empty($it['approved']) ? '' : 'display:none' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            نمایش در سایت
          </span>
          <span class="fb-chip replied" data-role="replied" style="<?= !empty($it['replied']) ? '' : 'display:none' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
            پاسخ داده شد
          </span>

          <span class="fb-spacer"></span>

          <?php if($isUnread): ?>
          <button type="button" class="fb-btn fb-btn-read" onclick="fbMarkRead(this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            خوانده شد
          </button>
          <?php endif; ?>

          <button type="button" class="fb-btn fb-btn-site" data-role="approve-btn" onclick="fbApprove(this)" style="<?= !empty($it['approved']) ? 'display:none' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            انتخاب جهت نمایش در سایت
          </button>
          <button type="button" class="fb-btn fb-btn-remove" data-role="remove-btn" onclick="fbRemoveFromSite(this)" style="<?= !empty($it['approved']) ? '' : 'display:none' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
            حذف از سایت
          </button>

          <button type="button" class="fb-btn fb-btn-reply" onclick="fbToggleReply(this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
            پاسخ
          </button>
        </div>

        <div class="fb-reply" hidden>
          <textarea class="fb-reply-input" rows="3" placeholder="پاسخِ خود را بنویسید..."></textarea>
          <div class="fb-reply-actions">
            <button type="button" class="fb-btn fb-btn-cancel" onclick="fbToggleReply(this)">انصراف</button>
            <button type="button" class="fb-btn fb-btn-send" onclick="fbSendReply(this)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              ارسال پاسخ
            </button>
          </div>
        </div>
      </div>
    </article>
    <?php endforeach; ?>

    <div id="fbEmpty" class="fb-empty" style="display:none">پیامی در این دسته وجود ندارد.</div>
  </div>
</main>

<script>
(function(){
  "use strict";
  var list = document.getElementById('fbList');
  var emptyBox = document.getElementById('fbEmpty');

  /* فیلترِ تب‌ها */
  window.fbTab = function(cat){
    document.querySelectorAll('.fb-tab').forEach(function(t){ t.classList.toggle('active', t.getAttribute('data-cat')===cat); });
    var any = false;
    list.querySelectorAll('.fb-card').forEach(function(c){
      var show =
        cat==='all' ? true :
        cat==='unread'   ? (c.getAttribute('data-read')==='0') :
        cat==='approved' ? (c.getAttribute('data-approved')==='1') :
        cat==='replied'  ? (c.getAttribute('data-replied')==='1') : true;
      c.style.display = show ? '' : 'none';
      if(show) any = true;
    });
    emptyBox.style.display = any ? 'none' : '';
  };

  function faNum(n){ return String(n).replace(/\d/g, function(d){ return '۰۱۲۳۴۵۶۷۸۹'[d]; }); }
  function tabCount(cat){ return document.querySelector('.fb-tab[data-cat="'+cat+'"] b'); }
  function fbUpdateCounts(){
    var c={all:0,unread:0,approved:0,replied:0};
    list.querySelectorAll('.fb-card').forEach(function(card){
      c.all++;
      if(card.getAttribute('data-read')==='0') c.unread++;
      if(card.getAttribute('data-approved')==='1') c.approved++;
      if(card.getAttribute('data-replied')==='1') c.replied++;
    });
    Object.keys(c).forEach(function(k){ var b=tabCount(k); if(b) b.textContent=faNum(c[k]); });
  }
  function fbReapply(){ var a=document.querySelector('.fb-tab.active'); if(a) window.fbTab(a.getAttribute('data-cat')); }

  /* علامتِ «خوانده شد» (فعلاً ظاهری؛ بعداً به بک‌اند وصل می‌شود) */
  window.fbMarkRead = function(btn){
    var card = btn.closest('.fb-card');
    card.classList.remove('unread');
    card.setAttribute('data-read','1');
    var st = card.querySelector('.fb-status');
    if(st){ st.classList.remove('is-unread'); st.classList.add('is-read'); st.innerHTML = '<span class="d"></span>خوانده‌شده'; }
    btn.remove();
    fbUpdateCounts(); fbReapply();
  };

  /* تأیید برای نمایش در سایت ↔ حذف از سایت (فعلاً ظاهری) */
  window.fbApprove = function(btn){
    var card = btn.closest('.fb-card');
    card.setAttribute('data-approved','1');
    var chip = card.querySelector('[data-role="on-site"]'); if(chip) chip.style.display = '';
    var rem  = card.querySelector('[data-role="remove-btn"]'); if(rem) rem.style.display = '';
    btn.style.display = 'none';
    fbUpdateCounts(); fbReapply();
  };
  window.fbRemoveFromSite = function(btn){
    var card = btn.closest('.fb-card');
    card.setAttribute('data-approved','0');
    var chip = card.querySelector('[data-role="on-site"]'); if(chip) chip.style.display = 'none';
    var app  = card.querySelector('[data-role="approve-btn"]'); if(app) app.style.display = '';
    btn.style.display = 'none';
    fbUpdateCounts(); fbReapply();
  };

  /* باز/بستهٔ کادرِ پاسخ */
  window.fbToggleReply = function(btn){
    var card = btn.closest('.fb-card');
    var panel = card.querySelector('.fb-reply');
    if(!panel) return;
    if(panel.hasAttribute('hidden')){ panel.removeAttribute('hidden'); var ta=panel.querySelector('.fb-reply-input'); if(ta) ta.focus(); }
    else panel.setAttribute('hidden','');
  };

  /* ارسالِ پاسخ (فعلاً ظاهری؛ بعداً به بک‌اند وصل می‌شود) */
  window.fbSendReply = function(btn){
    var card = btn.closest('.fb-card');
    var panel = card.querySelector('.fb-reply');
    var ta = panel ? panel.querySelector('.fb-reply-input') : null;
    var val = ta ? ta.value.trim() : '';
    if(!val){ if(ta) ta.focus(); return; }
    // افزودنِ حبابِ پاسخ زیرِ پیام
    var box = card.querySelector('[data-role="replies"]');
    if(box){
      var b = document.createElement('div'); b.className = 'fb-reply-bubble';
      var h = document.createElement('div'); h.className = 'h';
      h.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>پاسخِ مدیر';
      var p = document.createElement('p'); p.textContent = val;   // textContent برای جلوگیری از تزریقِ HTML
      b.appendChild(h); b.appendChild(p); box.appendChild(b);
    }
    card.setAttribute('data-replied','1');
    var chip = card.querySelector('[data-role="replied"]'); if(chip) chip.style.display = '';
    if(ta) ta.value = '';
    if(panel) panel.setAttribute('hidden','');
    fbUpdateCounts(); fbReapply();
  };

  /* تم (دارک/لایت) از «داشبورد مدیریت» کنترل می‌شود — کلید مشترک: maxa-theme */
  function applyMaxaTheme(){
    var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
    if(d) document.documentElement.setAttribute('data-theme','dark'); else document.documentElement.removeAttribute('data-theme');
  }
  applyMaxaTheme();
  window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });
})();
</script>
</body>
</html>
