<?php
/* ============================================================================
 *  مدیریت تیکت‌های پشتیبانی — پنل مکسا
 *  تیکت‌های ثبت‌شده توسط حامیان/کاربران را نمایش می‌دهد و امکان پاسخ‌دهی و
 *  تغییر وضعیت را فراهم می‌کند. (وصل به دیتابیس از طریق ticket-db.php)
 * ========================================================================== */
require __DIR__ . '/ticket-db.php';

$tickets    = [];
$repliesMap = [];
if ($pdo) {
  try {
    $tickets    = ticket_all($pdo);
    $repliesMap = ticket_replies_for($pdo, array_column($tickets, 'id'));
  } catch (Throwable $e) {
    $dbError = $dbError ?? $e->getMessage();
  }
}

$total   = count($tickets);
$open    = 0; $answered = 0; $closed = 0; $unread = 0;
foreach ($tickets as $t) {
  if ($t['status'] === 'open')     $open++;
  if ($t['status'] === 'answered') $answered++;
  if ($t['status'] === 'closed')   $closed++;
  if (!empty($t['admin_unread']))  $unread++;
}

if (!function_exists('tk_initials')) {
  function tk_initials($n){ $p=preg_split('/\s+/',trim((string)$n)); $a=mb_substr($p[0]??'',0,1,'UTF-8'); $b=isset($p[1])?mb_substr($p[1],0,1,'UTF-8'):''; return $a.$b ?: '؟'; }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تیکت‌های پشتیبانی | پنل مکسا</title>
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

.tk-wrap{max-width:920px;margin:0 auto}

.tk-head{display:flex;align-items:center;gap:16px;margin-bottom:20px}
.tk-head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.tk-head-ic svg{width:27px;height:27px}
.tk-head h1{font-size:22px;font-weight:800;letter-spacing:-.01em}
.tk-head p{font-size:13px;color:var(--color-muted);margin-top:3px}

/* تب‌های فیلتر */
.tk-tabs{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:22px}
.tk-tab{font-family:inherit;text-align:start;cursor:pointer;background:var(--color-surface);border:1.5px solid var(--color-border);border-radius:16px;
  padding:14px 16px;box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:12px;transition:border-color .2s,box-shadow .25s,transform .15s var(--ease)}
.tk-tab:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.tk-tab.active{border-color:var(--color-primary)}
.tk-tab-ic{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;flex-shrink:0}
.tk-tab-ic svg{width:21px;height:21px}
.tk-tab b{font-size:21px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums;display:block}
.tk-tab span{font-size:11.5px;color:var(--color-muted);font-weight:600;margin-top:4px;display:block;line-height:1.5}
.ic-all{background:var(--primary-08);color:var(--color-primary)}
.ic-open{background:var(--secondary-12);color:var(--color-secondary-dark)}
.ic-answered{background:var(--violet-12);color:var(--violet)}
.ic-closed{background:var(--success-12);color:var(--success)}
.ic-unread{background:var(--danger-12);color:var(--danger)}

/* کارت‌ها */
.tk-list{display:flex;flex-direction:column;gap:14px}
.tk-card{display:flex;gap:15px;background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);
  box-shadow:var(--shadow-sm);padding:18px 20px;transition:box-shadow .25s var(--ease),border-color .2s;position:relative}
.tk-card:hover{box-shadow:var(--shadow-md)}
.tk-card.unread{border-inline-start:4px solid var(--color-secondary)}
.tk-av{width:46px;height:46px;border-radius:14px;flex-shrink:0;display:grid;place-items:center;color:#fff;font-weight:700;font-size:15px;box-shadow:var(--shadow-sm);
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary))}
.tk-body{flex:1;min-width:0}
.tk-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap}
.tk-subject{font-weight:800;font-size:15px;line-height:1.6}
.tk-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:4px}
.tk-name{font-weight:700;font-size:12.5px;color:var(--color-muted)}
.tk-contact{font-size:12px;color:var(--color-muted)}
.tk-date{font-size:11.5px;color:var(--color-muted);font-weight:600;white-space:nowrap}
.tk-badges{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:10px}
.tk-badge{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;padding:4px 11px;border-radius:99px;white-space:nowrap}
.tk-badge .dot{width:6px;height:6px;border-radius:50%}
.tk-badge.cat{background:var(--primary-08);color:var(--color-primary-dark)} .tk-badge.cat .dot{background:var(--color-primary)}
.tk-badge.prio-low{background:var(--primary-08);color:var(--color-primary-dark)} .prio-low .dot{background:var(--color-primary-light)}
.tk-badge.prio-normal{background:var(--secondary-12);color:var(--color-secondary-dark)} .prio-normal .dot{background:var(--color-secondary)}
.tk-badge.prio-high{background:var(--danger-12);color:var(--danger)} .prio-high .dot{background:var(--danger)}
.tk-badge.st-open{background:var(--secondary-12);color:var(--color-secondary-dark)} .st-open .dot{background:var(--color-secondary)}
.tk-badge.st-answered{background:var(--violet-12);color:var(--violet)} .st-answered .dot{background:var(--violet)}
.tk-badge.st-closed{background:var(--success-12);color:var(--success)} .st-closed .dot{background:var(--success)}
.tk-msg{font-size:13.5px;color:var(--color-text);line-height:1.9;margin-top:12px;white-space:pre-wrap;word-break:break-word}

.tk-thread{display:flex;flex-direction:column;gap:9px;margin-top:12px}
.tk-thread:empty{display:none}
.tk-bubble{border:1px solid var(--color-border);border-radius:12px;padding:11px 14px}
.tk-bubble.admin{background:var(--primary-08);border-inline-start:3px solid var(--color-primary)}
.tk-bubble.user{background:var(--secondary-12);border-inline-start:3px solid var(--color-secondary)}
.tk-bubble .h{display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:11.5px;font-weight:800;margin-bottom:5px}
.tk-bubble.admin .h{color:var(--color-primary-dark)}
.tk-bubble.user .h{color:var(--color-secondary-dark)}
.tk-bubble .h svg{width:13px;height:13px}
.tk-bubble .h .when{font-weight:600;color:var(--color-muted)}
.tk-bubble p{font-size:13px;line-height:1.85;color:var(--color-text);word-break:break-word;white-space:pre-wrap}

.tk-foot{display:flex;align-items:center;gap:9px;margin-top:14px;flex-wrap:wrap}
.tk-spacer{flex:1;min-width:8px}
.tk-btn{font-family:inherit;font-weight:700;font-size:12px;border:none;cursor:pointer;padding:7px 13px;border-radius:10px;
  display:inline-flex;align-items:center;gap:6px;transition:filter .2s,transform .14s}
.tk-btn svg{width:14px;height:14px}
.tk-btn:active{transform:scale(.96)}
.tk-btn:hover{filter:brightness(.97)}
.tk-btn-read{background:var(--primary-08);color:var(--color-primary-dark)}
.tk-btn-reply{background:var(--color-bg);color:var(--color-muted);border:1px solid var(--color-border)}
.tk-btn-close{background:var(--success-12);color:var(--success)}
.tk-btn-reopen{background:var(--secondary-12);color:var(--color-secondary-dark)}

.tk-reply{margin-top:14px;padding-top:14px;border-top:1px dashed var(--color-border)}
.tk-reply[hidden]{display:none}
.tk-reply-input{width:100%;font-family:inherit;font-size:13.5px;color:var(--color-text);background:var(--color-bg);
  border:1.5px solid var(--color-border);border-radius:var(--radius-sm);padding:11px 13px;line-height:1.9;resize:vertical;min-height:82px;
  transition:border-color .2s,box-shadow .2s,background .2s}
.tk-reply-input:focus{outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:var(--color-surface)}
.tk-reply-actions{display:flex;gap:9px;margin-top:10px;justify-content:flex-end}
.tk-btn-send{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff}
.tk-btn-cancel{background:var(--color-bg);color:var(--color-muted);border:1px solid var(--color-border)}

.tk-empty{text-align:center;padding:50px 20px;color:var(--color-muted);font-weight:600;background:var(--color-surface);
  border:1.5px dashed var(--color-border);border-radius:var(--radius)}
.tk-err{background:var(--danger-12);color:var(--danger);border:1px solid var(--danger);border-radius:var(--radius-sm);padding:14px 16px;margin-bottom:18px;font-weight:600}

@media (max-width:760px){
  body{padding:18px 14px}
  .tk-tabs{grid-template-columns:repeat(2,1fr)}
  .tk-head h1{font-size:19px}
}
</style>
</head>
<body>

<main class="tk-wrap">
  <header class="tk-head">
    <div class="tk-head-ic">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v1a2 2 0 0 0 0 4v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1a2 2 0 0 0 0-4Z"/><path d="M13 7v10"/></svg>
    </div>
    <div>
      <h1>تیکت‌های پشتیبانی</h1>
      <p>تیکت‌های ثبت‌شده توسط حامیان و کاربران را بررسی، پاسخ‌دهی و مدیریت کنید.</p>
    </div>
  </header>

  <?php if (!empty($dbError)): ?>
    <div class="tk-err">خطا در ارتباط با دیتابیس: <?= e($dbError) ?></div>
  <?php endif; ?>

  <section class="tk-tabs">
    <button type="button" class="tk-tab active" data-cat="all" onclick="tkTab('all')">
      <div class="tk-tab-ic ic-all"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><circle cx="3" cy="6" r="1"/><circle cx="3" cy="12" r="1"/><circle cx="3" cy="18" r="1"/></svg></div>
      <div><b><?= fa_digits($total) ?></b><span>همهٔ تیکت‌ها</span></div>
    </button>
    <button type="button" class="tk-tab" data-cat="open" onclick="tkTab('open')">
      <div class="tk-tab-ic ic-open"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
      <div><b><?= fa_digits($open) ?></b><span>باز</span></div>
    </button>
    <button type="button" class="tk-tab" data-cat="answered" onclick="tkTab('answered')">
      <div class="tk-tab-ic ic-answered"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg></div>
      <div><b><?= fa_digits($answered) ?></b><span>پاسخ‌داده‌شده</span></div>
    </button>
    <button type="button" class="tk-tab" data-cat="closed" onclick="tkTab('closed')">
      <div class="tk-tab-ic ic-closed"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div><b><?= fa_digits($closed) ?></b><span>بسته‌شده</span></div>
    </button>
    <button type="button" class="tk-tab" data-cat="unread" onclick="tkTab('unread')">
      <div class="tk-tab-ic ic-unread"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg></div>
      <div><b><?= fa_digits($unread) ?></b><span>خوانده‌نشده</span></div>
    </button>
  </section>

  <div class="tk-list" id="tkList">
    <?php foreach ($tickets as $t):
      $tid = (int)$t['id'];
      $isUnread = !empty($t['admin_unread']);
      $reps = $repliesMap[$tid] ?? [];
    ?>
    <article class="tk-card <?= $isUnread ? 'unread' : '' ?>"
             data-id="<?= $tid ?>"
             data-status="<?= e($t['status']) ?>"
             data-read="<?= $isUnread ? '0' : '1' ?>">
      <div class="tk-av"><?= e(tk_initials($t['name'])) ?></div>
      <div class="tk-body">
        <div class="tk-top">
          <div>
            <div class="tk-subject"><?= e($t['subject']) ?></div>
            <div class="tk-meta">
              <span class="tk-name">از: <?= e($t['name']) ?></span>
              <?php if (!empty($t['contact'])): ?><span class="tk-contact">• <?= e($t['contact']) ?></span><?php endif; ?>
            </div>
          </div>
          <span class="tk-date"><?= jalali_datetime($t['created_at']) ?></span>
        </div>

        <div class="tk-badges">
          <span class="tk-badge cat"><span class="dot"></span><?= e(TICKET_CATEGORY_FA[$t['category']] ?? $t['category']) ?></span>
          <span class="tk-badge prio-<?= e($t['priority']) ?>"><span class="dot"></span>اولویت: <?= e(TICKET_PRIORITY_FA[$t['priority']] ?? $t['priority']) ?></span>
          <span class="tk-badge st-<?= e($t['status']) ?>" data-role="status-badge"><span class="dot"></span><?= e(TICKET_STATUS_FA[$t['status']] ?? $t['status']) ?></span>
        </div>

        <p class="tk-msg"><?= e($t['message']) ?></p>

        <div class="tk-thread" data-role="thread">
          <?php foreach ($reps as $r):
            $who = $r['author'] === 'admin' ? 'پاسخِ مدیر' : 'پیامِ کاربر';
          ?>
          <div class="tk-bubble <?= e($r['author']) ?>">
            <div class="h"><span><?= $who ?></span><span class="when"><?= jalali_datetime($r['created_at']) ?></span></div>
            <p><?= e($r['message']) ?></p>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="tk-foot">
          <?php if ($isUnread): ?>
          <button type="button" class="tk-btn tk-btn-read" onclick="tkMarkRead(this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            خوانده شد
          </button>
          <?php endif; ?>

          <span class="tk-spacer"></span>

          <button type="button" class="tk-btn tk-btn-reply" onclick="tkToggleReply(this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
            پاسخ
          </button>

          <button type="button" class="tk-btn tk-btn-close" data-role="close-btn" onclick="tkSetStatus(this,'closed')" style="<?= $t['status']==='closed' ? 'display:none' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            بستن تیکت
          </button>
          <button type="button" class="tk-btn tk-btn-reopen" data-role="reopen-btn" onclick="tkSetStatus(this,'open')" style="<?= $t['status']==='closed' ? '' : 'display:none' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
            بازگشایی
          </button>
        </div>

        <div class="tk-reply" hidden>
          <textarea class="tk-reply-input" rows="3" placeholder="پاسخِ خود را بنویسید..."></textarea>
          <div class="tk-reply-actions">
            <button type="button" class="tk-btn tk-btn-cancel" onclick="tkToggleReply(this)">انصراف</button>
            <button type="button" class="tk-btn tk-btn-send" onclick="tkSendReply(this)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              ارسال پاسخ
            </button>
          </div>
        </div>
      </div>
    </article>
    <?php endforeach; ?>

    <div id="tkEmpty" class="tk-empty" style="<?= $total ? 'display:none' : '' ?>">هنوز تیکتی ثبت نشده است.</div>
  </div>
</main>

<script>
(function(){
  "use strict";
  var list = document.getElementById('tkList');
  var emptyBox = document.getElementById('tkEmpty');

  function faNum(n){ return String(n).replace(/\d/g, function(d){ return '۰۱۲۳۴۵۶۷۸۹'[d]; }); }

  /* ---- فیلتر تب‌ها ---- */
  window.tkTab = function(cat){
    document.querySelectorAll('.tk-tab').forEach(function(t){ t.classList.toggle('active', t.getAttribute('data-cat')===cat); });
    var any = false;
    list.querySelectorAll('.tk-card').forEach(function(c){
      var st = c.getAttribute('data-status');
      var show =
        cat==='all'    ? true :
        cat==='unread' ? (c.getAttribute('data-read')==='0') :
        (st===cat);
      c.style.display = show ? '' : 'none';
      if(show) any = true;
    });
    emptyBox.style.display = any ? 'none' : '';
    emptyBox.textContent = any ? '' : 'تیکتی در این دسته وجود ندارد.';
  };

  function tabCount(cat){ return document.querySelector('.tk-tab[data-cat="'+cat+'"] b'); }
  function tkUpdateCounts(){
    var c={all:0,open:0,answered:0,closed:0,unread:0};
    list.querySelectorAll('.tk-card').forEach(function(card){
      c.all++;
      var st=card.getAttribute('data-status'); if(c[st]!==undefined) c[st]++;
      if(card.getAttribute('data-read')==='0') c.unread++;
    });
    Object.keys(c).forEach(function(k){ var b=tabCount(k); if(b) b.textContent=faNum(c[k]); });
  }
  function tkReapply(){ var a=document.querySelector('.tk-tab.active'); if(a) window.tkTab(a.getAttribute('data-cat')); }

  function post(url, data){
    var body = new URLSearchParams(data);
    return fetch(url, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body:body, credentials:'same-origin'})
      .then(function(r){ return r.json().catch(function(){ return {status:'error', message:'پاسخ نامعتبر از سرور.'}; }); });
  }

  function markCardRead(card){
    if(card.getAttribute('data-read')==='1') return;
    card.classList.remove('unread');
    card.setAttribute('data-read','1');
    var b = card.querySelector('.tk-btn-read'); if(b) b.remove();
  }

  /* ---- علامتِ خوانده‌شده ---- */
  window.tkMarkRead = function(btn){
    var card = btn.closest('.tk-card');
    post('ticket-status.php', {id:card.getAttribute('data-id'), action:'mark_read'}).then(function(res){
      if(res.status==='success'){ markCardRead(card); tkUpdateCounts(); tkReapply(); }
      else alert(res.message||'خطا');
    });
  };

  /* ---- باز/بستهٔ کادرِ پاسخ ---- */
  window.tkToggleReply = function(btn){
    var card = btn.closest('.tk-card');
    var panel = card.querySelector('.tk-reply');
    if(!panel) return;
    if(panel.hasAttribute('hidden')){ panel.removeAttribute('hidden'); var ta=panel.querySelector('.tk-reply-input'); if(ta) ta.focus(); }
    else panel.setAttribute('hidden','');
  };

  function setStatusBadge(card, status){
    card.setAttribute('data-status', status);
    var badge = card.querySelector('[data-role="status-badge"]');
    var map = {open:'باز', answered:'پاسخ داده شد', closed:'بسته‌شده'};
    if(badge){ badge.className = 'tk-badge st-'+status; badge.innerHTML = '<span class="dot"></span>'+(map[status]||status); }
    var cl = card.querySelector('[data-role="close-btn"]');
    var ro = card.querySelector('[data-role="reopen-btn"]');
    if(cl) cl.style.display = status==='closed' ? 'none' : '';
    if(ro) ro.style.display = status==='closed' ? '' : 'none';
  }

  /* ---- ارسالِ پاسخِ مدیر ---- */
  window.tkSendReply = function(btn){
    var card = btn.closest('.tk-card');
    var panel = card.querySelector('.tk-reply');
    var ta = panel ? panel.querySelector('.tk-reply-input') : null;
    var val = ta ? ta.value.trim() : '';
    if(!val){ if(ta) ta.focus(); return; }
    btn.disabled = true;
    post('ticket-reply.php', {id:card.getAttribute('data-id'), message:val}).then(function(res){
      btn.disabled = false;
      if(res.status!=='success'){ alert(res.message||'خطا در ثبت پاسخ'); return; }
      var thread = card.querySelector('[data-role="thread"]');
      if(thread){
        var b = document.createElement('div'); b.className = 'tk-bubble admin';
        var h = document.createElement('div'); h.className = 'h';
        var who = document.createElement('span'); who.textContent = 'پاسخِ مدیر';
        var when = document.createElement('span'); when.className='when'; when.textContent = res.created_fa || '';
        h.appendChild(who); h.appendChild(when);
        var p = document.createElement('p'); p.textContent = val;
        b.appendChild(h); b.appendChild(p); thread.appendChild(b);
      }
      markCardRead(card);
      setStatusBadge(card, res.new_status || 'answered');
      if(ta) ta.value=''; if(panel) panel.setAttribute('hidden','');
      tkUpdateCounts(); tkReapply();
    });
  };

  /* ---- تغییر وضعیت (بستن/بازگشایی) ---- */
  window.tkSetStatus = function(btn, status){
    var card = btn.closest('.tk-card');
    post('ticket-status.php', {id:card.getAttribute('data-id'), status:status}).then(function(res){
      if(res.status!=='success'){ alert(res.message||'خطا'); return; }
      setStatusBadge(card, res.new_status || status);
      tkUpdateCounts(); tkReapply();
    });
  };

  /* ---- همگام‌سازی تم با داشبورد ---- */
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
