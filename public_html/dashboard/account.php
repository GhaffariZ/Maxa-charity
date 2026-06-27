<?php
require_once __DIR__ . '/_guard.php';
/* ============================================================================
 *  صفحهٔ حساب کاربری — پنل مکسا
 *  اطلاعاتِ واقعیِ کاربرِ واردشده را از dash_user() می‌خواند و تغییر رمز را به
 *  account-password.php (با CSRF) می‌سپارد. جدولِ dashboard_users ستونِ ایمیل/تلفن
 *  ندارد، پس این فیلدها نمایش داده نمی‌شوند.
 * ========================================================================== */
$__u = dash_user() ?? [];
$acc_name     = ($__u['full_name'] ?? '') !== '' ? $__u['full_name'] : ($__u['username'] ?? 'کاربر');
$acc_username = $__u['username'] ?? '';
$acc_branch   = (dash_load_branch((int)($__u['branch_id'] ?? 0))['name'] ?? '');

// برچسبِ نقش: سوپرادمین / مدیر شعبه / نامِ نقش
if (!empty($__u['is_super'])) {
    $acc_role = 'مدیر مرکزی';
} elseif (!empty($__u['is_branch_admin'])) {
    $acc_role = 'مدیر شعبه';
} else {
    $acc_role = 'کاربر شعبه';
    if (!empty($__u['role_id'])) {
        try {
            $rs = dash_pdo()->prepare('SELECT name FROM dashboard_roles WHERE id = ? LIMIT 1');
            $rs->execute([(int)$__u['role_id']]);
            $rr = $rs->fetch();
            if ($rr && $rr['name']) { $acc_role = $rr['name']; }
        } catch (Throwable $e) { /* بی‌اثر */ }
    }
}

$acc_pw_msg = $_GET['pw'] ?? '';   // نتیجه‌ی تغییر رمز (از account-password.php)

if(!function_exists('acc_initials')){
  function acc_initials($n){
    $p = preg_split('/\s+/', trim($n));
    $a = mb_substr($p[0] ?? '', 0, 1, 'UTF-8');
    $b = isset($p[1]) ? mb_substr($p[1], 0, 1, 'UTF-8') : '';
    return $a . $b;
  }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>حساب کاربری | پنل مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-secondary-dark:#b9760a; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b;
  --primary-08:rgba(0,123,122,.08); --primary-12:rgba(0,123,122,.12);
  --secondary-12:rgba(244,166,30,.16); --danger-12:rgba(224,85,107,.12);
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

.ac-wrap{max-width:820px;margin:0 auto}

.ac-head{display:flex;align-items:center;gap:16px;margin-bottom:22px}
.ac-head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.ac-head-ic svg{width:27px;height:27px}
.ac-head h1{font-size:22px;font-weight:800;letter-spacing:-.01em}
.ac-head p{font-size:13px;color:var(--color-muted);margin-top:3px}

/* کارت پروفایل — سطحِ معمولی با لهجه‌ی طلایی (بدون بخش سبز) */
.ac-profile{display:flex;align-items:center;gap:18px;background:var(--color-surface);border:1px solid var(--color-border);
  border-radius:var(--radius-lg);padding:24px 26px;box-shadow:var(--shadow-sm);margin-bottom:22px;position:relative;overflow:hidden}
.ac-profile::before{content:'';position:absolute;inset-inline-start:0;top:0;bottom:0;width:6px;background:linear-gradient(180deg,#ffc24d,var(--color-secondary),#e08e12)}
.ac-profile::after{content:'';position:absolute;inset-inline-end:-40px;top:-60px;width:190px;height:190px;border-radius:50%;
  background:radial-gradient(circle,var(--secondary-12),transparent 70%);pointer-events:none}
.ac-avatar{width:78px;height:78px;border-radius:22px;flex-shrink:0;display:grid;place-items:center;font-size:28px;font-weight:800;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 26px -10px rgba(0,123,122,.6);position:relative;z-index:1}
.ac-profile-info{position:relative;z-index:1;min-width:0}
.ac-profile-info h2{font-size:20px;font-weight:800;letter-spacing:-.01em}
.ac-role{display:inline-block;font-size:12px;font-weight:800;color:var(--color-secondary-dark);background:var(--secondary-12);
  padding:5px 13px;border-radius:99px;margin-top:7px}
.ac-mail{font-size:13px;color:var(--color-muted);margin-top:9px;direction:ltr;text-align:right;font-weight:600}

/* کارت‌ها */
.ac-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);box-shadow:var(--shadow-sm);padding:24px;margin-bottom:20px}
.ac-card-head{display:flex;align-items:center;gap:11px;margin-bottom:18px}
.ac-card-ic{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;flex-shrink:0;
  background:var(--secondary-12);color:var(--color-secondary-dark)}
.ac-card-ic.teal{background:var(--primary-08);color:var(--color-primary)}
.ac-card-ic.red{background:var(--danger-12);color:var(--danger)}
.ac-card-ic svg{width:19px;height:19px}
.ac-card-head h3{font-size:16px;font-weight:800;letter-spacing:-.01em}
.ac-card-head span{font-size:12px;color:var(--color-muted);display:block;margin-top:2px}

/* فرم‌ها (ویرایشِ درون‌صفحه‌ای) */
.ac-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.ac-col-2{grid-column:1 / -1}
.ac-field{display:flex;flex-direction:column;gap:7px;min-width:0}
.ac-field label{font-size:12.5px;font-weight:700;color:var(--color-text)}
.ac-field input{font-family:inherit;font-size:13.5px;color:var(--color-text);background:var(--color-bg);
  border:1.5px solid var(--color-border);border-radius:var(--radius-sm);padding:11px 13px;width:100%;transition:border-color .2s,box-shadow .2s,background .2s}
.ac-field input:focus{outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:var(--color-surface)}
.ac-field input[readonly]{opacity:.65;cursor:not-allowed}
.ac-field input[dir="ltr"]{text-align:right}
/* فرمِ اطلاعات، فوکوسِ طلایی برای تنوعِ رنگی */
.ac-form-gold input:focus{border-color:var(--color-secondary);box-shadow:0 0 0 4px var(--secondary-12)}

.ac-foot{display:flex;justify-content:flex-end;margin-top:18px}

.btn{display:inline-flex;align-items:center;gap:9px;font-family:inherit;font-weight:800;font-size:14px;border:none;cursor:pointer;
  padding:12px 24px;border-radius:13px;transition:transform .15s var(--ease),box-shadow .25s,filter .2s;text-decoration:none}
.btn svg{width:18px;height:18px}
.btn:active{transform:scale(.97)}
.btn-gold{color:#5c3d00;background:linear-gradient(135deg,#ffc24d,var(--color-secondary));box-shadow:0 10px 24px -10px rgba(244,166,30,.75)}
.btn-gold:hover{transform:translateY(-2px);box-shadow:0 16px 30px -10px rgba(244,166,30,.9)}
.btn-primary{color:#fff;background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));box-shadow:0 10px 24px -10px rgba(0,123,122,.7)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 16px 30px -10px rgba(0,123,122,.8)}
.btn-danger{color:var(--danger);background:var(--danger-12)}
.btn-danger:hover{filter:brightness(.97)}

.ac-logout-card{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.ac-logout-card .ac-card-head{margin-bottom:0}

@media (max-width:640px){
  body{padding:18px 14px}
  .ac-grid{grid-template-columns:1fr}
  .ac-profile{flex-direction:column;text-align:center}
  .ac-mail{text-align:center}
  .ac-head h1{font-size:19px}
}
</style>
</head>
<body>

<main class="ac-wrap">
  <header class="ac-head">
    <div class="ac-head-ic">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-1.5a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4V21"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <div>
      <h1>حساب کاربری</h1>
      <p>اطلاعات حساب و امنیت خود را مدیریت کنید.</p>
    </div>
  </header>

  <!-- کارت پروفایل (بدون بخش سبز، با لهجه‌ی طلایی) -->
  <section class="ac-profile">
    <div class="ac-avatar"><?= htmlspecialchars(acc_initials($acc_name)) ?></div>
    <div class="ac-profile-info">
      <h2><?= htmlspecialchars($acc_name) ?></h2>
      <span class="ac-role"><?= htmlspecialchars($acc_role) ?></span>
      <div class="ac-mail" dir="ltr">@<?= htmlspecialchars($acc_username) ?></div>
    </div>
  </section>

  <!-- اطلاعات حساب (فقط نمایش — جدول کاربران ستونِ ایمیل/تلفن ندارد) -->
  <section class="ac-card">
    <div class="ac-card-head">
      <div class="ac-card-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-1.5a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4V21"/><circle cx="12" cy="7" r="4"/></svg></div>
      <div><h3>اطلاعات حساب</h3><span>مشخصاتِ حسابِ شما در پنل</span></div>
    </div>

    <div class="ac-grid">
      <div class="ac-field ac-col-2">
        <label>نام و نام خانوادگی</label>
        <input type="text" value="<?= htmlspecialchars($acc_name) ?>" readonly>
      </div>
      <div class="ac-field">
        <label>نام کاربری</label>
        <input type="text" dir="ltr" value="<?= htmlspecialchars($acc_username) ?>" readonly>
      </div>
      <div class="ac-field">
        <label>شعبه</label>
        <input type="text" value="<?= htmlspecialchars($acc_branch) ?>" readonly>
      </div>
      <div class="ac-field ac-col-2">
        <label>نقش</label>
        <input type="text" value="<?= htmlspecialchars($acc_role) ?>" readonly>
      </div>
    </div>
  </section>

  <!-- تغییر رمز عبور (ویرایش در همین صفحه) -->
  <section class="ac-card">
    <div class="ac-card-head">
      <div class="ac-card-ic teal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2.5"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
      <div><h3>تغییر رمز عبور</h3><span>برای امنیت بیشتر، رمز را دوره‌ای تغییر دهید</span></div>
    </div>

    <?php if ($acc_pw_msg === 'ok'): ?>
      <div style="background:rgba(22,163,122,.10);color:#0f7a5b;border:1px solid rgba(22,163,122,.22);border-radius:12px;padding:11px 14px;font-size:13px;font-weight:700;margin-bottom:14px;">رمز عبور با موفقیت تغییر کرد.</div>
    <?php elseif ($acc_pw_msg === 'wrong'): ?>
      <div style="background:rgba(224,85,107,.10);color:#c33850;border:1px solid rgba(224,85,107,.22);border-radius:12px;padding:11px 14px;font-size:13px;font-weight:700;margin-bottom:14px;">رمز عبور فعلی نادرست است.</div>
    <?php elseif ($acc_pw_msg === 'short'): ?>
      <div style="background:rgba(224,85,107,.10);color:#c33850;border:1px solid rgba(224,85,107,.22);border-radius:12px;padding:11px 14px;font-size:13px;font-weight:700;margin-bottom:14px;">رمز جدید باید حداقل ۸ کاراکتر و با تکرارش یکسان باشد.</div>
    <?php endif; ?>

    <form action="account-password.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
      <div class="ac-grid">
        <div class="ac-field ac-col-2">
          <label>رمز عبور فعلی</label>
          <input type="password" name="current_password" dir="ltr" placeholder="••••••••">
        </div>
        <div class="ac-field">
          <label>رمز عبور جدید</label>
          <input type="password" name="new_password" dir="ltr" placeholder="••••••••">
        </div>
        <div class="ac-field">
          <label>تکرار رمز عبور جدید</label>
          <input type="password" name="confirm_password" dir="ltr" placeholder="••••••••">
        </div>
      </div>
      <div class="ac-foot">
        <button type="submit" class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2.5"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          تغییر رمز عبور
        </button>
      </div>
    </form>
  </section>

  <!-- خروج از حساب -->
  <section class="ac-card ac-logout-card">
    <div class="ac-card-head">
      <div class="ac-card-ic red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></div>
      <div><h3>خروج از حساب</h3><span>از حساب کاربری خود خارج می‌شوید</span></div>
    </div>
    <a href="logout.php" target="_top" class="btn btn-danger">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      خروج از حساب
    </a>
  </section>
</main>

<script>
/* تم (دارک/لایت) از «داشبورد مدیریت» کنترل می‌شود — کلید مشترک: maxa-theme */
(function(){
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
