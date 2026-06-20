<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ØªÛŒÚ©Øªâ€ŒÙ‡Ø§ÛŒ Ù¾Ø´ØªÛŒØ¨Ø§Ù†ÛŒ | Ù¾Ù†Ù„ Ø­Ø§Ù…ÛŒØ§Ù† Ù…Ú©Ø³Ø§</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* â”€â”€ Design tokens (same palette as admin tickets.php) â”€â”€ */
:root{
  --pr:#007b7a; --pr-d:#006665; --pr-l:#4fb2b0;
  --sec:#f4a61e; --txt:#2f3437; --muted:#9d9d9d;
  --border:#e6e8ea; --bg:#f8f9fa; --surf:#ffffff;
  --ok:#16a37a; --err:#e0556b; --violet:#7c4ddb;
  --pr-08:rgba(0,123,122,.08); --pr-12:rgba(0,123,122,.12);
  --err-12:rgba(224,85,107,.12); --ok-12:rgba(22,163,122,.14);
  --sec-12:rgba(244,166,30,.16); --vi-12:rgba(124,77,219,.14);
  --r-sm:12px; --r:18px; --r-lg:24px;
  --sh-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --sh-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --sh-lg:0 20px 44px -14px rgba(0,102,101,.20),0 8px 20px -10px rgba(16,40,40,.12);
  --ease:cubic-bezier(.4,0,.2,1);
}
[data-theme="dark"]{
  --txt:#e7ecee; --muted:#8e989d; --border:#2a343a;
  --bg:#0f1518; --surf:#19232a;
  --pr-08:rgba(79,178,176,.10); --pr-12:rgba(79,178,176,.16);
  --err-12:rgba(224,85,107,.16); --ok-12:rgba(22,163,122,.18);
  --sec-12:rgba(244,166,30,.18); --vi-12:rgba(124,77,219,.20);
  --sh-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --sh-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  --sh-lg:0 24px 48px -16px rgba(0,0,0,.62),0 10px 24px -12px rgba(0,0,0,.5);
  color-scheme:dark;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--bg);color:var(--txt);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;transition:background .3s,color .3s}

/* â”€â”€ Scrollbar â”€â”€ */
*::-webkit-scrollbar{width:8px}
*::-webkit-scrollbar-thumb{background:rgba(0,123,122,.2);border-radius:99px;border:2px solid transparent;background-clip:padding-box}
[data-theme="dark"] *::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);background-clip:padding-box}

/* â”€â”€ Topbar â”€â”€ */
.topbar{position:sticky;top:0;z-index:100;background:var(--surf);border-bottom:1px solid var(--border);padding:0 24px;height:64px;display:flex;align-items:center;gap:12px;box-shadow:var(--sh-sm);transition:background .3s,border-color .3s}
.topbar-logo{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--txt)}
.topbar-logo-ic{width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,var(--pr-l),var(--pr));display:grid;place-items:center;flex-shrink:0}
.topbar-logo-ic svg{width:20px;height:20px;color:#fff}
.topbar-title{font-weight:700;font-size:15px}
.topbar-sep{flex:1}
.topbar-back{display:flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;background:var(--pr-08);color:var(--pr);font-size:13px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:background .2s}
.topbar-back:hover{background:var(--pr-12)}
.topbar-back svg{width:16px;height:16px}
.topbar-theme{width:38px;height:38px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--muted);cursor:pointer;display:grid;place-items:center;transition:background .2s,color .2s}
.topbar-theme:hover{background:var(--pr-08);color:var(--pr)}
.topbar-theme svg{width:18px;height:18px}

/* â”€â”€ Layout â”€â”€ */
.page{max-width:860px;margin:0 auto;padding:32px 20px 60px}

/* â”€â”€ Page header â”€â”€ */
.ph{display:flex;align-items:center;gap:14px;margin-bottom:28px}
.ph-ic{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,var(--pr-l),var(--pr));display:grid;place-items:center;box-shadow:0 10px 24px -8px rgba(0,123,122,.55);flex-shrink:0}
.ph-ic svg{width:26px;height:26px;color:#fff}
.ph-title{font-size:22px;font-weight:800;color:var(--txt)}
.ph-sub{font-size:13px;color:var(--muted);margin-top:2px}
.ph-sep{flex:1}
.btn-new{display:flex;align-items:center;gap:8px;padding:10px 22px;border-radius:12px;background:linear-gradient(135deg,var(--pr-l),var(--pr));color:#fff;font-family:inherit;font-size:14px;font-weight:700;border:none;cursor:pointer;box-shadow:0 8px 20px -6px rgba(0,123,122,.5);transition:filter .2s,transform .1s}
.btn-new:hover{filter:brightness(1.08)}
.btn-new:active{transform:scale(.97)}
.btn-new svg{width:16px;height:16px}

/* â”€â”€ Auth guard â”€â”€ */
.auth-wall{text-align:center;padding:60px 20px}
.auth-wall-ic{width:64px;height:64px;border-radius:20px;background:var(--sec-12);display:inline-grid;place-items:center;margin-bottom:20px}
.auth-wall-ic svg{width:32px;height:32px;color:var(--sec)}
.auth-wall h2{font-size:20px;font-weight:700;margin-bottom:8px}
.auth-wall p{color:var(--muted);margin-bottom:24px;max-width:340px;margin-inline:auto}
.btn-login{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:12px;background:linear-gradient(135deg,var(--pr-l),var(--pr));color:#fff;font-family:inherit;font-size:14px;font-weight:700;text-decoration:none;box-shadow:0 8px 20px -6px rgba(0,123,122,.5)}

/* â”€â”€ Loading â”€â”€ */
.loader-wrap{text-align:center;padding:80px 20px;color:var(--muted)}
.spinner{width:40px;height:40px;border:3px solid var(--border);border-top-color:var(--pr);border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 16px}
@keyframes spin{to{transform:rotate(360deg)}}

/* â”€â”€ Sections â”€â”€ */
.section{margin-bottom:32px}

/* â”€â”€ Card: new ticket form â”€â”€ */
.form-card{background:var(--surf);border-radius:var(--r-lg);border:1px solid var(--border);box-shadow:var(--sh-md);overflow:hidden;transition:box-shadow .25s}
.form-card-head{padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.form-card-head-ic{width:36px;height:36px;border-radius:10px;background:var(--pr-08);display:grid;place-items:center}
.form-card-head-ic svg{width:18px;height:18px;color:var(--pr)}
.form-card-head-title{font-size:15px;font-weight:700}
.form-card-body{padding:24px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.form-row.full{grid-template-columns:1fr}
@media(max-width:520px){.form-row{grid-template-columns:1fr}}
.form-group label{display:block;font-size:13px;font-weight:600;color:var(--txt);margin-bottom:6px}
.form-group label span{color:var(--err);margin-right:2px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:10px;background:var(--bg);color:var(--txt);font-family:inherit;font-size:13px;outline:none;transition:border-color .2s,background .2s}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--pr);background:var(--surf)}
.form-group textarea{min-height:130px;resize:vertical;line-height:1.6}
[data-theme="dark"] .form-group input,[data-theme="dark"] .form-group select,[data-theme="dark"] .form-group textarea{background:#111d22;border-color:var(--border);color:var(--txt)}
[data-theme="dark"] .form-group input:focus,[data-theme="dark"] .form-group select:focus,[data-theme="dark"] .form-group textarea:focus{background:#152128}
.form-footer{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:4px}
.btn-submit{display:flex;align-items:center;gap:8px;padding:11px 26px;border-radius:12px;background:linear-gradient(135deg,var(--pr-l),var(--pr));color:#fff;font-family:inherit;font-size:14px;font-weight:700;border:none;cursor:pointer;box-shadow:0 6px 18px -6px rgba(0,123,122,.45);transition:filter .2s,transform .1s}
.btn-submit:hover{filter:brightness(1.08)}
.btn-submit:active{transform:scale(.97)}
.btn-submit:disabled{opacity:.6;cursor:not-allowed}
.btn-submit svg{width:16px;height:16px}

/* â”€â”€ Toast â”€â”€ */
.toast-wrap{position:fixed;bottom:28px;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;gap:10px;align-items:center;pointer-events:none}
.toast{padding:12px 22px;border-radius:12px;font-size:13px;font-weight:600;color:#fff;box-shadow:var(--sh-lg);opacity:0;transform:translateY(12px);transition:opacity .3s,transform .3s;pointer-events:none;max-width:360px;text-align:center}
.toast.show{opacity:1;transform:translateY(0);pointer-events:auto}
.toast.ok{background:linear-gradient(135deg,#1aad84,var(--ok))}
.toast.fail{background:linear-gradient(135deg,#e86279,var(--err))}

/* â”€â”€ Tickets list â”€â”€ */
.tk-list{display:flex;flex-direction:column;gap:14px}
.tk-empty{text-align:center;padding:52px 20px;color:var(--muted)}
.tk-empty-ic{width:56px;height:56px;border-radius:18px;background:var(--pr-08);display:inline-grid;place-items:center;margin-bottom:16px}
.tk-empty-ic svg{width:28px;height:28px;color:var(--pr)}
.tk-empty p{font-size:14px}

/* â”€â”€ Ticket card â”€â”€ */
.tk-card{background:var(--surf);border-radius:var(--r);border:1.5px solid var(--border);box-shadow:var(--sh-sm);overflow:hidden;transition:border-color .25s,box-shadow .25s,transform .2s}
.tk-card:hover{border-color:var(--pr-l);box-shadow:var(--sh-md)}
.tk-card.has-unread{border-color:var(--pr);box-shadow:0 0 0 3px var(--pr-08),var(--sh-md)}
.tk-card-head{padding:16px 20px;display:flex;align-items:flex-start;gap:14px;cursor:pointer;user-select:none}
.tk-avatar{width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,var(--pr-l),var(--pr));color:#fff;font-size:18px;font-weight:700;display:grid;place-items:center;flex-shrink:0}
.tk-meta{flex:1;min-width:0}
.tk-subject{font-size:15px;font-weight:700;color:var(--txt);margin-bottom:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;align-items:center;gap:8px}
.tk-unread-dot{width:8px;height:8px;border-radius:50%;background:var(--pr);flex-shrink:0;animation:pulse 1.6s ease-in-out infinite}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(0,123,122,.6)}60%{box-shadow:0 0 0 6px rgba(0,123,122,0)}}
.tk-badges{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:6px}
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700;letter-spacing:.3px}
.badge-open{background:var(--pr-08);color:var(--pr)}
.badge-answered{background:var(--ok-12);color:var(--ok)}
.badge-closed{background:rgba(0,0,0,.08);color:var(--muted)}
[data-theme="dark"] .badge-closed{background:rgba(255,255,255,.1)}
.badge-cat{background:var(--vi-12);color:var(--violet)}
.badge-low{background:var(--ok-12);color:var(--ok)}
.badge-normal{background:var(--sec-12);color:#b27908}
.badge-high{background:var(--err-12);color:var(--err)}
.tk-date{font-size:11px;color:var(--muted)}
.tk-chevron{flex-shrink:0;color:var(--muted);transition:transform .25s var(--ease)}
.tk-chevron svg{width:18px;height:18px;display:block}
.tk-card.expanded .tk-chevron{transform:rotate(180deg)}

/* â”€â”€ Thread â”€â”€ */
.tk-thread{border-top:1px solid var(--border);display:none;flex-direction:column;gap:0}
.tk-card.expanded .tk-thread{display:flex}
.tk-msg-orig{padding:16px 20px 12px;background:var(--pr-08)}
.tk-msg-orig-label{font-size:11px;font-weight:700;color:var(--pr);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px}
.tk-msg-orig p{font-size:13px;color:var(--txt);line-height:1.7;white-space:pre-wrap;word-break:break-word}
.reply-list{display:flex;flex-direction:column;gap:0}
.reply-item{padding:14px 20px;border-top:1px solid var(--border);display:flex;gap:12px}
.reply-item.admin{background:var(--ok-12)}
.reply-item.user{background:transparent}
.reply-av{width:32px;height:32px;border-radius:10px;display:grid;place-items:center;flex-shrink:0;font-size:12px;font-weight:700;color:#fff}
.reply-av.admin{background:linear-gradient(135deg,#1aad84,var(--ok))}
.reply-av.user{background:linear-gradient(135deg,var(--pr-l),var(--pr))}
.reply-content{flex:1;min-width:0}
.reply-author{font-size:11px;font-weight:700;margin-bottom:4px}
.reply-item.admin .reply-author{color:var(--ok)}
.reply-item.user .reply-author{color:var(--pr)}
.reply-msg{font-size:13px;color:var(--txt);line-height:1.7;white-space:pre-wrap;word-break:break-word}
.reply-date{font-size:11px;color:var(--muted);margin-top:4px}

/* â”€â”€ Reply form inside thread â”€â”€ */
.reply-form{padding:14px 20px 16px;border-top:1px solid var(--border);background:var(--bg)}
.reply-form textarea{width:100%;padding:10px 13px;border:1.5px solid var(--border);border-radius:10px;background:var(--surf);color:var(--txt);font-family:inherit;font-size:13px;min-height:90px;resize:vertical;outline:none;transition:border-color .2s}
.reply-form textarea:focus{border-color:var(--pr)}
.reply-form-footer{display:flex;justify-content:flex-end;gap:10px;margin-top:10px}
.btn-reply-send{display:flex;align-items:center;gap:7px;padding:9px 20px;border-radius:10px;background:linear-gradient(135deg,var(--pr-l),var(--pr));color:#fff;font-family:inherit;font-size:13px;font-weight:700;border:none;cursor:pointer;box-shadow:0 5px 14px -5px rgba(0,123,122,.4);transition:filter .2s}
.btn-reply-send:hover{filter:brightness(1.08)}
.btn-reply-send:disabled{opacity:.6;cursor:not-allowed}
.btn-reply-send svg{width:14px;height:14px}
.btn-reply-cancel{padding:9px 16px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--muted);font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;transition:background .2s}
.btn-reply-cancel:hover{background:var(--pr-08);color:var(--pr)}
.tk-status-closed-note{text-align:center;padding:12px;font-size:12px;color:var(--muted);font-style:italic}
</style>
</head>
<body>

<!-- â”€â”€ Topbar â”€â”€ -->
<div class="topbar">
  <a href="/benefactor-dashboard/" class="topbar-logo">
    <div class="topbar-logo-ic">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 21C12 21 3 14.5 3 8.5a5.5 5.5 0 0 1 9-4.2A5.5 5.5 0 0 1 21 8.5C21 14.5 12 21 12 21z"/></svg>
    </div>
    <span class="topbar-title">Ù…Ú©Ø³Ø§ â€” Ù¾Ù†Ù„ Ø­Ø§Ù…ÛŒØ§Ù†</span>
  </a>
  <div class="topbar-sep"></div>
  <a href="/benefactor-dashboard/" class="topbar-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    Ø¨Ø§Ø²Ú¯Ø´Øª Ø¨Ù‡ Ù¾Ù†Ù„
  </a>
  <button class="topbar-theme" id="themeToggle" title="ØªØºÛŒÛŒØ± ØªÙ…">
    <svg id="themeIcDark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    <svg id="themeIcLight" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
  </button>
</div>

<!-- â”€â”€ Main content â”€â”€ -->
<div class="page" id="mainPage">

  <!-- Auth wall (shown until JWT confirmed) -->
  <div class="auth-wall" id="authWall" style="display:none">
    <div class="auth-wall-ic">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>
    <h2>Ø§Ø¨ØªØ¯Ø§ ÙˆØ§Ø±Ø¯ Ø´ÙˆÛŒØ¯</h2>
    <p>Ø¨Ø±Ø§ÛŒ Ø«Ø¨Øª ÛŒØ§ Ù…Ø´Ø§Ù‡Ø¯Ù‡â€ŒÛŒ ØªÛŒÚ©Øªâ€ŒÙ‡Ø§ÛŒ Ù¾Ø´ØªÛŒØ¨Ø§Ù†ÛŒ Ù„Ø·ÙØ§Ù‹ Ø¨Ù‡ Ù¾Ù†Ù„ Ø­Ø§Ù…ÛŒØ§Ù† ÙˆØ§Ø±Ø¯ Ø´ÙˆÛŒØ¯.</p>
    <a href="/benefactor-dashboard/" class="btn-login">ÙˆØ±ÙˆØ¯ Ø¨Ù‡ Ù¾Ù†Ù„ Ø­Ø§Ù…ÛŒØ§Ù†</a>
  </div>

  <!-- Loader -->
  <div class="loader-wrap" id="pageLoader">
    <div class="spinner"></div>
    <div>Ø¯Ø± Ø­Ø§Ù„ Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒâ€¦</div>
  </div>

  <!-- Authenticated UI -->
  <div id="ticketUI" style="display:none">

    <!-- Page header -->
    <div class="ph">
      <div class="ph-ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </div>
      <div>
        <div class="ph-title">ØªÛŒÚ©Øªâ€ŒÙ‡Ø§ÛŒ Ù¾Ø´ØªÛŒØ¨Ø§Ù†ÛŒ</div>
        <div class="ph-sub">Ø«Ø¨Øª Ø¯Ø±Ø®ÙˆØ§Ø³Øª ÛŒØ§ Ù…Ø´Ø§Ù‡Ø¯Ù‡â€ŒÛŒ Ù¾Ø§Ø³Ø®â€ŒÙ‡Ø§ÛŒ Ù‚Ø¨Ù„ÛŒ</div>
      </div>
      <div class="ph-sep"></div>
      <button class="btn-new" id="btnNewTicket">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        ØªÛŒÚ©Øª Ø¬Ø¯ÛŒØ¯
      </button>
    </div>

    <!-- â”€â”€ New ticket form (hidden by default) â”€â”€ -->
    <div class="section" id="formSection" style="display:none">
      <div class="form-card" id="formCard">
        <div class="form-card-head">
          <div class="form-card-head-ic">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          </div>
          <div class="form-card-head-title">Ø«Ø¨Øª ØªÛŒÚ©Øª Ø¬Ø¯ÛŒØ¯</div>
        </div>
        <div class="form-card-body">
          <form id="ticketForm" autocomplete="off">
            <div class="form-row">
              <div class="form-group">
                <label>Ù…ÙˆØ¶ÙˆØ¹ <span>*</span></label>
                <input type="text" name="subject" placeholder="Ù…ÙˆØ¶ÙˆØ¹ ØªÛŒÚ©Øª Ø®ÙˆØ¯ Ø±Ø§ Ø¨Ù†ÙˆÛŒØ³ÛŒØ¯â€¦" required minlength="3" maxlength="200">
              </div>
              <div class="form-group">
                <label>Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ</label>
                <select name="category">
                  <option value="general">Ø¹Ù…ÙˆÙ…ÛŒ</option>
                  <option value="donation">Ú©Ù…Ú©â€ŒÙ‡Ø§ Ùˆ Ù¾Ø±Ø¯Ø§Ø®Øª</option>
                  <option value="technical">Ù…Ø´Ú©Ù„ ÙÙ†ÛŒ</option>
                  <option value="campaign">Ú©Ù…Ù¾ÛŒÙ†â€ŒÙ‡Ø§</option>
                  <option value="other">Ø³Ø§ÛŒØ±</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Ø§ÙˆÙ„ÙˆÛŒØª</label>
                <select name="priority">
                  <option value="normal">Ø¹Ø§Ø¯ÛŒ</option>
                  <option value="low">Ú©Ù…</option>
                  <option value="high">ÙÙˆØ±ÛŒ</option>
                </select>
              </div>
              <div class="form-group">
                <label>Ø±Ø§Ù‡ Ø§Ø±ØªØ¨Ø§Ø·ÛŒ (Ø§Ø®ØªÛŒØ§Ø±ÛŒ)</label>
                <input type="text" name="contact" placeholder="Ø§ÛŒÙ…ÛŒÙ„ ÛŒØ§ Ø´Ù…Ø§Ø±Ù‡ ØªÙ…Ø§Ø³">
              </div>
            </div>
            <div class="form-row full">
              <div class="form-group">
                <label>Ù…ØªÙ† Ù¾ÛŒØ§Ù… <span>*</span></label>
                <textarea name="message" placeholder="Ø¬Ø²Ø¦ÛŒØ§Øª Ù…Ø´Ú©Ù„ ÛŒØ§ Ø¯Ø±Ø®ÙˆØ§Ø³Øª Ø®ÙˆØ¯ Ø±Ø§ Ø´Ø±Ø­ Ø¯Ù‡ÛŒØ¯â€¦" required minlength="5" maxlength="5000"></textarea>
              </div>
            </div>
            <div class="form-footer">
              <button type="submit" class="btn-submit" id="btnSubmit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Ø§Ø±Ø³Ø§Ù„ ØªÛŒÚ©Øª
              </button>
              <button type="button" class="btn-reply-cancel" id="btnCancelForm">Ø§Ù†ØµØ±Ø§Ù</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- â”€â”€ Tickets list â”€â”€ -->
    <div class="section">
      <div class="tk-list" id="tkList">
        <!-- populated by JS -->
      </div>
    </div>

  </div><!-- /ticketUI -->
</div><!-- /page -->

<!-- Toast container -->
<div class="toast-wrap" id="toastWrap"></div>

<!--
  NOTE: this logic lives in an external file (not inline) because the site
  CSP for /benefactor-dashboard sets `script-src 'self'`, which blocks inline
  scripts. An inline <script> here would silently fail and leave the page on
  an endless loading spinner.
-->
<script src="/benefactor-dashboard/tickets.js"></script>

</body>
</html>
