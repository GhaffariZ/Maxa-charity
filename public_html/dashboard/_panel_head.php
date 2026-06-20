<?php
/* ============================================================================
 *  سربرگِ مشترکِ صفحاتِ فرمِ پنل (شعب/کاربران) — هم‌زبانِ طراحی با داشبورد.
 *  این صفحات داخل iframe داشبورد بارگذاری می‌شوند، پس پوسته‌ی سبکِ خود را دارند.
 *  پیش از include، متغیر $PANEL_TITLE را تنظیم کنید.
 * ========================================================================== */
if (!isset($PANEL_TITLE)) { $PANEL_TITLE = 'پنل مکسا'; }
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($PANEL_TITLE) ?> | پنل مکسا</title>
<script>(function(){try{var t=localStorage.getItem('maxa-theme');if(t==='dark'){document.documentElement.setAttribute('data-theme','dark');}}catch(e){}})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --danger:#e0556b; --success:#16a37a; --warning:#f4a61e;
  --primary-08:rgba(0,123,122,.08); --secondary-12:rgba(244,166,30,.14);
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --shadow-lg:0 20px 44px -14px rgba(0,102,101,.20),0 8px 20px -10px rgba(16,40,40,.12);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a; --primary-08:rgba(79,178,176,.10);
  --secondary-12:rgba(244,166,30,.18);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-lg:0 24px 48px -16px rgba(0,0,0,.62),0 10px 24px -12px rgba(0,0,0,.5);
  color-scheme:dark;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;padding:26px}
a{color:inherit;text-decoration:none}
.wrap{max-width:920px;margin:0 auto}
.page-head{display:flex;align-items:center;gap:14px;margin-bottom:22px}
.page-head .ph-ic{width:48px;height:48px;border-radius:15px;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));box-shadow:0 12px 24px -12px rgba(0,102,101,.85);flex-shrink:0}
.page-head .ph-ic .ic{width:24px;height:24px}
.page-head h1{font-size:20px;font-weight:800;letter-spacing:-.01em}
.page-head p{font-size:12.5px;color:var(--color-muted)}
.card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);box-shadow:var(--shadow-sm);padding:24px;margin-bottom:18px}
.card h2{font-size:15px;font-weight:800;margin-bottom:4px}
.card .hint{font-size:12px;color:var(--color-muted);margin-bottom:16px}
.field{margin-bottom:16px}
.field label{display:block;font-size:12.5px;font-weight:700;margin-bottom:7px;color:#5b6469}
.field input[type=text],.field input[type=password],.field select{width:100%;height:46px;border:1px solid var(--color-border);
  background:var(--color-bg);border-radius:12px;padding:0 14px;font-family:inherit;font-size:14px;color:var(--color-text);transition:border-color .2s,box-shadow .2s,background .2s}
.field input:focus,.field select:focus{outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:var(--color-surface)}
.field .sub{font-size:11px;color:var(--color-muted);margin-top:5px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:640px){.grid2{grid-template-columns:1fr}.page-head{flex-wrap:wrap}}
.checks{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
@media(max-width:640px){.checks{grid-template-columns:1fr 1fr}}
.check{display:flex;align-items:center;gap:10px;padding:11px 13px;border:1.5px solid var(--color-border);border-radius:13px;cursor:pointer;font-weight:600;font-size:12.5px;transition:border-color .2s,background .2s;user-select:none}
.check:hover{border-color:var(--color-primary-light)}
.check input{accent-color:var(--color-primary);width:17px;height:17px;flex-shrink:0}
.check.on{border-color:var(--color-primary);background:var(--primary-08)}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:48px;padding:0 22px;border:none;border-radius:13px;font-family:inherit;font-weight:800;font-size:14px;cursor:pointer;transition:transform .15s var(--ease),box-shadow .22s}
.btn .ic{width:18px;height:18px}
.btn-primary{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff;box-shadow:0 12px 24px -12px rgba(0,123,122,.8)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 18px 30px -12px rgba(0,123,122,.9)}
.btn-ghost{background:var(--color-bg);color:var(--color-text);border:1px solid var(--color-border)}
.msg{border-radius:12px;padding:12px 15px;font-size:13px;font-weight:600;margin-bottom:18px;line-height:1.8}
.msg.err{background:rgba(224,85,107,.10);color:#c33850;border:1px solid rgba(224,85,107,.22)}
.msg.ok{background:rgba(22,163,122,.10);color:#0f7a5b;border:1px solid rgba(22,163,122,.22)}
table.tbl{width:100%;border-collapse:collapse;font-size:13px}
table.tbl th{text-align:right;font-size:11.5px;font-weight:700;color:var(--color-muted);padding:0 12px 12px;border-bottom:1px solid var(--color-border);white-space:nowrap}
table.tbl td{padding:13px 12px;border-bottom:1px solid var(--color-border);vertical-align:middle}
table.tbl tr:last-child td{border-bottom:none}
.badge{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:99px;font-size:11.5px;font-weight:700}
.badge.ok{background:rgba(22,163,122,.12);color:var(--success)}
.badge.off{background:rgba(224,85,107,.12);color:var(--danger)}
.badge.hq{background:var(--secondary-12);color:#b9760a}
.badge.tag{background:var(--primary-08);color:var(--color-primary-dark)}
.tbtn{display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 12px;border:1px solid var(--color-border);background:var(--color-surface);border-radius:10px;font-family:inherit;font-weight:700;font-size:12px;color:var(--color-text);cursor:pointer;transition:background .2s,border-color .2s}
.tbtn:hover{border-color:var(--color-primary-light)}
.tbtn.danger{color:var(--danger)}
.tbtn.danger:hover{border-color:var(--danger);background:rgba(224,85,107,.06)}
.actions{display:flex;gap:12px;margin-top:8px;flex-wrap:wrap}

/* ---------- ورودی رمز + دکمه‌ی تولید ---------- */
.pass-row{display:flex;gap:10px;align-items:stretch}
.pass-row input{flex:1}
.btn-gen{display:inline-flex;align-items:center;gap:7px;height:46px;padding:0 16px;border:1px solid var(--color-border);
  background:var(--color-bg);border-radius:12px;font-family:inherit;font-weight:700;font-size:12.5px;color:var(--color-primary-dark);
  cursor:pointer;white-space:nowrap;transition:border-color .2s,background .2s}
.btn-gen:hover{border-color:var(--color-primary-light);background:var(--primary-08)}
.btn-gen .ic{width:16px;height:16px}

/* ---------- مودال (کارت پروفایلِ شعبه‌ی ساخته‌شده) ---------- */
.modal-overlay{position:fixed;inset:0;background:rgba(16,40,40,.46);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);
  display:flex;align-items:center;justify-content:center;padding:20px;z-index:120;
  opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}
.modal-overlay.show{opacity:1;visibility:visible}
.modal-card{width:100%;max-width:min(420px,92vw);background:var(--color-surface);border:1px solid var(--color-border);
  border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);overflow:hidden;
  transform:translateY(14px) scale(.97);transition:transform .3s var(--ease)}
.modal-overlay.show .modal-card{transform:none}
.modal-top{padding:22px 22px 16px;text-align:center;position:relative;
  background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff}
.modal-top .m-ic{width:54px;height:54px;border-radius:16px;display:grid;place-items:center;margin:0 auto 10px;
  background:rgba(255,255,255,.18)}
.modal-top .m-ic .ic{width:28px;height:28px}
.modal-top h3{font-size:16px;font-weight:800}
.modal-top p{font-size:12px;opacity:.9;margin-top:3px}
.modal-body{padding:18px 22px 22px}
.profile-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 0;border-bottom:1px dashed var(--color-border)}
.profile-row:last-of-type{border-bottom:none}
.profile-row .pk{font-size:12px;color:var(--color-muted);font-weight:700;flex-shrink:0}
.profile-row .pv{font-size:13.5px;font-weight:800;text-align:left;word-break:break-all}
.profile-row .pv.ltr{direction:ltr}
.modal-foot{margin-top:14px}
.modal-foot .btn{width:100%;justify-content:center}

/* ---------- توست ---------- */
.toast{position:fixed;left:50%;bottom:26px;transform:translateX(-50%) translateY(20px);
  background:var(--color-text);color:var(--color-surface);padding:12px 20px;border-radius:12px;
  font-size:13px;font-weight:700;box-shadow:var(--shadow-lg);z-index:140;
  opacity:0;visibility:hidden;transition:opacity .25s,transform .3s var(--ease),visibility .25s;display:flex;align-items:center;gap:9px}
.toast.show{opacity:1;visibility:visible;transform:translateX(-50%) translateY(0)}
.toast .ic{width:17px;height:17px;color:var(--success)}

/* ---------- ریسپانسیو: جدول‌های مدیریتی روی موبایل به کارت تبدیل می‌شوند ---------- */
@media(max-width:640px){
  body{padding:16px}
  .card{padding:16px}
  table.tbl thead{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
  table.tbl,table.tbl tbody,table.tbl tr,table.tbl td{display:block;width:100%}
  table.tbl tr{border:1px solid var(--color-border);border-radius:14px;padding:6px 12px;margin-bottom:12px;background:var(--color-surface)}
  table.tbl tr:last-child{margin-bottom:0}
  table.tbl td{display:flex;align-items:center;justify-content:space-between;gap:14px;
    padding:9px 0;border-bottom:1px dashed var(--color-border);text-align:left!important}
  table.tbl tr td:last-child{border-bottom:none}
  table.tbl td::before{content:attr(data-label);font-size:11.5px;font-weight:700;color:var(--color-muted);flex-shrink:0;text-align:right}
  table.tbl td:empty{display:none}
  .btn{width:100%}
}
</style>
</head>
<body>
<div class="wrap">
