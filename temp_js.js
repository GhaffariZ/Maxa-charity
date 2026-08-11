
(function(){
  function applyMaxaTheme(){
    var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
    if(d){ document.documentElement.setAttribute('data-theme','dark'); if(document.body) document.body.setAttribute('data-theme','dark'); }
    else { document.documentElement.removeAttribute('data-theme'); if(document.body) document.body.removeAttribute('data-theme'); }
  }
  applyMaxaTheme();
  window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });
})();
</script>

<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
<!-- TinyMCE 7 Enterprise Rich Text Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.3.0/tinymce.min.js" referrerpolicy="origin"></script>

<style>
:root {
    /* Ù¾Ø§Ù„Øª Ø±Ù†Ú¯ÛŒ Ù„Ø§ÛŒØª Ù…ÙˆØ¯ (Ø¨Ø±Ú¯Ø±ÙØªÙ‡ Ø§Ø² Ù„ÙˆÚ¯Ùˆ Ù…Ú©Ø³Ø§) */
    --primary-color: #007D75;       /* Ø³Ø¨Ø²-Ø¢Ø¨ÛŒ Ù…Ú©Ø³Ø§ */
    --primary-dark: #006159;
    --secondary-color: #F79F1F;     /* Ù†Ø§Ø±Ù†Ø¬ÛŒ Ù…Ú©Ø³Ø§ */
    --bg-color: #f4f7f6;
    --text-color: #333333;
    --muted-color: #6b7674;
    --panel-bg: #ffffff;
    --surface-2: #f7faf9;
    --border-color: #e3e9e8;
    --input-bg: #ffffff;
    --header-text: #007D75;
    --btn-hover-opacity: 0.92;
    --modal-overlay: rgba(0,0,0,0.6);
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
    --shadow-md: 0 8px 24px rgba(0,0,0,0.07);
    --radius: 16px;
    --radius-sm: 10px;
    --anim-fast: 220ms;
    --anim-mid: 420ms;
    --anim-slow: 700ms;
}

[data-theme="dark"] {
    /* Ù¾Ø§Ù„Øª Ø±Ù†Ú¯ÛŒ Ø¯Ø§Ø±Ú© Ù…ÙˆØ¯ */
    --primary-color: #00a89d;
    --primary-dark: #00897e;
    --secondary-color: #ffb142;
    --bg-color: #121212;
    --text-color: #e0e0e0;
    --muted-color: #9aa3a1;
    --panel-bg: #1e1e1e;
    --surface-2: #262626;
    --border-color: #383838;
    --input-bg: #2d2d2d;
    --header-text: #00a89d;
    --modal-overlay: rgba(0,0,0,0.8);
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.35);
    --shadow-md: 0 8px 24px rgba(0,0,0,0.45);
}

* { box-sizing: border-box; }

body {
    background-color: var(--bg-color);
    color: var(--text-color);
    font-family: 'Vazirmatn', Tahoma, Arial, sans-serif;
    transition: background-color 0.3s, color 0.3s;
    margin: 0;
    padding: 0;
    min-height: 100vh;
}

.container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 16px 40px;
    animation: pageRise var(--anim-slow) cubic-bezier(.2,.8,.2,1) both;
}

/* ===== Ù‡Ø¯Ø± Ú†Ø³Ø¨Ø§Ù† ÙˆÛŒØ±Ø§ÛŒØ´Ú¯Ø± ===== */
.editor-header {
    position: sticky;
    top: 0;
    z-index: 30;
    background: color-mix(in srgb, var(--bg-color) 88%, transparent);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border-color);
    margin: 0 -16px 24px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.eh-title {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.ph-ic {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    box-shadow: var(--shadow-sm);
}

.ph-ic svg { width: 22px; height: 22px; }

.eh-title h1 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--header-text);
}

.eh-title p {
    margin: 2px 0 0;
    font-size: 12px;
    color: var(--muted-color);
}

.draft-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    padding: 5px 12px;
    border-radius: 999px;
    background: var(--surface-2);
    color: var(--muted-color);
    border: 1px solid var(--border-color);
    white-space: nowrap;
}

.eh-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* ===== Ø´Ø¨Ú©Ù‡ Ø§ØµÙ„ÛŒ ===== */
.layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    align-items: start;
}

.col-main { display: flex; flex-direction: column; gap: 24px; min-width: 0; }
.col-side { display: flex; flex-direction: column; gap: 24px; }

/* ===== Ú©Ø§Ø±Øªâ€ŒÙ‡Ø§ ===== */
.card {
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
    animation: cardPop var(--anim-mid) cubic-bezier(.2,.8,.2,1) both;
}

.card-pad { padding: 20px; }

.card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
    font-weight: 800;
    color: var(--header-text);
    margin: 0 0 16px;
}

.card-title svg { width: 19px; height: 19px; color: var(--primary-color); }

/* ===== Ø¹Ù†ÙˆØ§Ù† Ø®Ø¨Ø± ===== */
.title-card { padding: 20px; transition: box-shadow var(--anim-fast), border-color var(--anim-fast); }
.title-card:focus-within { border-color: color-mix(in srgb, var(--primary-color) 55%, var(--border-color)); box-shadow: 0 0 0 4px rgba(0,125,117,0.08); }

#title {
    width: 100%;
    border: none;
    background: transparent;
    color: var(--text-color);
    font-family: inherit;
    font-size: 26px;
    font-weight: 800;
    padding: 0;
    line-height: 1.5;
}
#title:focus { outline: none; }
#title::placeholder { color: var(--muted-color); opacity: 0.55; }

.title-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid var(--border-color);
    font-size: 12px;
    color: var(--muted-color);
}
#titleCounter.over { color: #e74c3c; font-weight: 700; }

/* ===== Ø²ÛŒØ±Ø¹Ù†ÙˆØ§Ù† Ø®Ø¨Ø± ===== */
.subtitle-card {
    background: var(--panel-bg);
    border: 1px dashed var(--border-color);
    border-radius: var(--radius);
    padding: 20px;
    margin-top: 16px;
    box-shadow: var(--shadow-sm);
    transition: all var(--anim-fast);
}
.subtitle-card:focus-within {
    border-color: var(--primary-color);
    background: var(--panel-bg);
    box-shadow: 0 0 0 4px rgba(0, 125, 117, 0.08);
}
#subtitle {
    width: 100%;
    border: none;
    background: transparent;
    color: var(--text-color);
    font-family: inherit;
    font-size: 16px;
    font-weight: 600;
    padding: 0;
    line-height: 1.6;
    resize: none;
}
#subtitle:focus {
    outline: none;
}
#subtitle::placeholder {
    color: var(--muted-color);
    opacity: 0.65;
}
.subtitle-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid var(--border-color);
    font-size: 11px;
    color: var(--muted-color);
}
#subtitleCounter.over { color: #e74c3c; font-weight: 700; }

/* ===== Ø¨Ø±Ú†Ø³Ø¨â€ŒÙ‡Ø§ÛŒ Ø®Ø¨Ø± (Ú†Ù†Ø¯ Ø§Ù†ØªØ®Ø§Ø¨ÛŒ) ===== */
.tag-checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 999px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all var(--anim-fast);
    user-select: none;
    color: var(--text-color);
}
.tag-checkbox-label:hover {
    border-color: var(--primary-color);
    background: rgba(0, 125, 117, 0.04);
}
.tag-checkbox-label input[type="checkbox"] {
    accent-color: var(--primary-color);
    cursor: pointer;
    width: 15px;
    height: 15px;
    margin: 0;
}
.tag-checkbox-label:has(input[type="checkbox"]:checked) {
    background: rgba(0, 125, 117, 0.08);
    border-color: var(--primary-color);
    color: var(--primary-color);
}

/* ===== Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯ Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ Ù‡ÙˆØ´Ù…Ù†Ø¯ ===== */
.suggestion-chip {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    transition: all var(--anim-fast);
    color: var(--text-color);
    user-select: none;
}
.suggestion-chip:hover {
    background: var(--primary-color);
    color: #fff;
    border-color: var(--primary-color);
    transform: translateY(-1.5px);
    box-shadow: 0 4px 8px rgba(0, 125, 117, 0.15);
}

/* ===== ÙÛŒÙ„Ø¯Ù‡Ø§ÛŒ Ø¹Ù…ÙˆÙ…ÛŒ ===== */
.input-group { margin-bottom: 18px; }
.input-group:last-child { margin-bottom: 0; }

label.field-label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 700;
    color: var(--primary-color);
}
label.field-label svg { width: 16px; height: 16px; }

.input {
    width: 100%;
    background: var(--input-bg);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 11px 12px;
    font-size: 15px;
    font-family: inherit;
    transition: border-color var(--anim-fast), box-shadow var(--anim-fast);
}
.input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.14);
}
select.input { appearance: none; cursor: pointer; }

/* ===== Ø¯Ø±Ø§Ù¾â€ŒØ¯Ø§ÙˆÙ† Ø³ÙØ§Ø±Ø´ÛŒ Ù…Ø¯Ø±Ù† ===== */
.msel { position: relative; width: 100%; font-family: inherit; }
.msel-trigger {
    width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 8px;
    background: var(--input-bg); color: var(--text-color);
    border: 1px solid var(--border-color); border-radius: var(--radius-sm);
    padding: 11px 12px; font-size: 15px; font-family: inherit; cursor: pointer; text-align: right;
    transition: border-color var(--anim-fast), box-shadow var(--anim-fast);
}
.msel-trigger:hover { border-color: color-mix(in srgb, var(--primary-color) 45%, var(--border-color)); }
.msel.open .msel-trigger { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(0,125,117,0.14); }
.msel-trigger .msel-value { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msel-trigger.is-placeholder .msel-value { color: var(--muted-color); }
.msel-caret { color: var(--muted-color); transition: transform var(--anim-fast); flex-shrink: 0; display: flex; }
.msel-caret svg { width: 18px; height: 18px; }
.msel.open .msel-caret { transform: rotate(180deg); color: var(--primary-color); }

.msel-menu {
    position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 50;
    background: var(--panel-bg); border: 1px solid var(--border-color);
    border-radius: var(--radius-sm); box-shadow: var(--shadow-md);
    padding: 6px; max-height: 240px; overflow-y: auto;
    opacity: 0; visibility: hidden; transform: translateY(-6px);
    transition: opacity var(--anim-fast), transform var(--anim-fast), visibility var(--anim-fast);
}
.msel.open .msel-menu { opacity: 1; visibility: visible; transform: translateY(0); }
.msel-opt {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    padding: 10px 11px; border-radius: 8px; cursor: pointer; font-size: 14px; color: var(--text-color);
    transition: background var(--anim-fast); user-select: none;
}
.msel-opt:hover, .msel-opt.is-active { background: rgba(0,125,117,0.10); }
.msel-opt.is-selected { color: var(--primary-color); font-weight: 700; }
.msel-opt .msel-check { opacity: 0; color: var(--primary-color); display: flex; flex-shrink: 0; }
.msel-opt .msel-check svg { width: 16px; height: 16px; }
.msel-opt.is-selected .msel-check { opacity: 1; }
.msel.field-invalid .msel-trigger { border-color: var(--danger) !important; box-shadow: 0 0 0 3px rgba(231,76,60,0.14) !important; }

/* ===== Ø­Ø§Ù„Øª Ø®Ø·Ø§ÛŒ ÙÛŒÙ„Ø¯Ù‡Ø§ ===== */
.input.field-invalid,
.title-card.field-invalid,
.editor-shell.field-invalid #editor {
    border-color: var(--danger, #e74c3c) !important;
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.14) !important;
}
.title-card.field-invalid { animation: fieldShake 0.32s ease; }
.input.field-invalid { animation: fieldShake 0.32s ease; }
/* Ù„ÛŒØ¨Ù„ ÙÛŒÙ„Ø¯Ù Ø®Ø·Ø§Ø¯Ø§Ø± Ù‚Ø±Ù…Ø² Ø´ÙˆØ¯ ØªØ§ Ø³Ø±ÛŒØ¹ Ø¯ÛŒØ¯Ù‡ Ø´ÙˆØ¯ */
.input-group.has-error label.field-label { color: var(--danger, #e74c3c); }

.field-error {
    display: none;
    align-items: center;
    gap: 5px;
    margin-top: 7px;
    font-size: 12px;
    font-weight: 600;
    color: var(--danger, #e74c3c);
}
.field-error.show { display: flex; animation: toastSlide 0.28s ease; }
.field-error svg { width: 14px; height: 14px; flex-shrink: 0; }

@keyframes fieldShake {
    0%,100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    75% { transform: translateX(4px); }
}

/* ===== Ø§Ø¯ÛŒØªÙˆØ± ===== */
.editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    padding-bottom: 16px;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}

.tb-btn {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    color: var(--text-color);
    border-radius: 8px;
    cursor: pointer;
    padding: 7px 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--anim-fast);
}
.tb-btn:hover {
    background: var(--primary-color);
    color: #fff;
    border-color: var(--primary-color);
    transform: translateY(-2px);
}
.tb-btn svg { stroke: currentColor; }
.tb-btn:hover svg { stroke: #fff; }
.tb-btn svg circle, .tb-btn svg path[fill="#333"] { fill: currentColor; stroke: none; }
.tb-btn:hover svg circle, .tb-btn:hover svg path[fill="#333"] { fill: #fff; }

.tb-sep { width: 1px; height: 24px; background: var(--border-color); margin: 0 2px; }

.tb-select {
    background: var(--surface-2);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 7px 6px;
    font-family: inherit;
    cursor: pointer;
}
.tb-select.small { width: 72px; }

.color-row { display: flex; gap: 5px; margin-right: auto; }
.color-box {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    cursor: pointer;
    border: 1px solid rgba(0,0,0,0.12);
    transition: transform var(--anim-fast);
}
.color-box:hover { transform: scale(1.15); }

#editor {
    min-height: 380px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 16px;
    background: var(--input-bg);
    color: var(--text-color);
    overflow-y: auto;
    line-height: 1.9;
    font-size: 16px;
}
#editor:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(0,125,117,0.10); }
#editor:empty:before { content: attr(data-placeholder); color: var(--muted-color); opacity: 0.55; }
#editor img { max-width: 100%; height: auto; border-radius: 10px; }

/* ===== Ø¯Ú©Ù…Ù‡â€ŒÙ‡Ø§ ===== */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 999px;
    cursor: pointer;
    font-weight: 700;
    font-size: 14px;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: transform var(--anim-fast), box-shadow var(--anim-fast), opacity var(--anim-fast), background var(--anim-fast);
}
.btn svg { width: 17px; height: 17px; }
.btn:hover { transform: translateY(-2px); }
.btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.btn-save {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: #fff;
    box-shadow: var(--shadow-sm);
}
.btn-save:hover { box-shadow: var(--shadow-md); }

.btn-ghost {
    background: transparent;
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
}
.btn-ghost:hover { background: rgba(0,125,117,0.06); }

.btn-insert {
    background: transparent;
    border: 1px dashed var(--primary-color);
    color: var(--primary-color);
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    transition: all var(--anim-fast);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-insert:hover { background: var(--primary-color); color: #fff; }

/* ===== Ø¢Ù¾Ù„ÙˆØ¯ ØªØµÙˆÛŒØ± Ø´Ø§Ø®Øµ (Dropzone) ===== */
.dropzone {
    position: relative;
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-sm);
    background: var(--surface-2);
    min-height: 190px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 20px;
    cursor: pointer;
    transition: all var(--anim-fast);
    overflow: hidden;
}
.dropzone:hover, .dropzone.drag-over {
    border-color: var(--primary-color);
    background: rgba(0,125,117,0.05);
}
.dropzone .dz-icon { color: var(--muted-color); transition: color var(--anim-fast); }
.dropzone:hover .dz-icon { color: var(--primary-color); }
.dropzone .dz-icon svg { width: 38px; height: 38px; }
.dropzone .dz-main { font-size: 14px; font-weight: 600; color: var(--text-color); margin-top: 8px; }
.dropzone .dz-hint { font-size: 12px; color: var(--muted-color); margin-top: 4px; }

.dz-preview {
    position: absolute;
    inset: 0;
    display: none;
}
.dz-preview.show { display: block; }
.dz-preview img { width: 100%; height: 100%; object-fit: cover; }
.dz-remove {
    position: absolute;
    top: 8px;
    left: 8px;
    background: rgba(231,76,60,0.92);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}
.dz-remove svg { width: 16px; height: 16px; }

/* ===== ÙˆÛŒØ¬Øª Ø§Ù…ØªÛŒØ§Ø² Ø³Ø¦Ùˆ ===== */
.seo-widget { display: flex; align-items: center; gap: 16px; }
.seo-ring { position: relative; width: 64px; height: 64px; flex-shrink: 0; }
.seo-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.seo-ring .ring-track { stroke: var(--border-color); }
.seo-ring .ring-fill { transition: stroke-dasharray var(--anim-mid) ease, stroke var(--anim-mid) ease; }
.seo-ring .ring-num {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; font-weight: 800; color: var(--text-color);
}
.seo-info { flex: 1; min-width: 0; }
.seo-state { font-size: 14px; color: var(--text-color); }
.seo-state b { font-weight: 800; }
.seo-tip { font-size: 12px; color: var(--muted-color); margin-top: 4px; }

/* ===== Ú†ÛŒÙ¾ ØªÚ¯â€ŒÙ‡Ø§ ===== */
.chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    background: var(--surface-2);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: 999px;
    font-size: 12px;
}
.chip button {
    background: none; border: none; cursor: pointer;
    color: var(--muted-color); padding: 0; display: flex;
    line-height: 1;
}
.chip button:hover { color: #e74c3c; }
.chip button svg { width: 13px; height: 13px; }

/* Ø¨Ø®Ø´â€ŒØ¨Ù†Ø¯ÛŒ Ú©Ø§Ø±Øª ØªÙ†Ø¸ÛŒÙ…Ø§Øª */
.settings-card { overflow: visible; }   /* ØªØ§ Ù…Ù†ÙˆÛŒ Ø¯Ø±Ø§Ù¾â€ŒØ¯Ø§ÙˆÙ† Ø³ÙØ§Ø±Ø´ÛŒ Ø¨Ø±ÛŒØ¯Ù‡ Ù†Ø´ÙˆØ¯ */
.settings-card .input-group { padding: 16px; margin: 0; border-bottom: 1px solid var(--border-color); position: relative; }
.settings-card .input-group:last-child { border-bottom: none; }

.publish-date-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.publish-date-row .input { flex: 1; min-width: 140px; }

/* ===== ØªÙˆØ³Øª ÙˆØ¶Ø¹ÛŒØª (Ù…ÙˆÙÙ‚ÛŒØª / Ø®Ø·Ø§ / Ø¯Ø± Ø­Ø§Ù„ Ø§Ù†Ø¬Ø§Ù…) ===== */
.toast {
    position: fixed;
    bottom: 22px;
    left: 22px;
    z-index: 10001;
    width: min(380px, calc(100vw - 44px));
    display: none;
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}
.toast.show { display: block; animation: toastSlide 360ms cubic-bezier(.2,.8,.2,1) both; }
.toast.hide { animation: toastOut 260ms ease both; }

/* Ù†ÙˆØ§Ø± Ø±Ù†Ú¯ÛŒ Ú©Ù†Ø§Ø±ÛŒ Ø¨Ø± Ø§Ø³Ø§Ø³ Ù†ÙˆØ¹ Ù¾ÛŒØ§Ù… */
.toast::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;     /* Ø¯Ø± RTL Ø³Ù…Øª Ú†Ù¾ Ú©Ø§Ø±Øª */
    width: 5px;
    background: var(--primary-color);
}
.toast.is-error::before  { background: var(--danger, #e74c3c); }
.toast.is-success::before { background: #00b894; }
.toast.is-info::before    { background: var(--secondary-color); }

.toast-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
}
.toast-ic {
    flex-shrink: 0;
    width: 38px; height: 38px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
}
.toast-ic svg { width: 20px; height: 20px; }
.toast.is-error  .toast-ic { background: var(--danger, #e74c3c); }
.toast.is-success .toast-ic { background: #00b894; }
.toast.is-info    .toast-ic { background: var(--secondary-color); }
/* Ø§Ø³Ù¾ÛŒÙ†Ø± Ú†Ø±Ø®Ø§Ù† Ø¨Ø±Ø§ÛŒ Ø­Ø§Ù„Øª Ø¯Ø± Ø­Ø§Ù„ Ø§Ù†Ø¬Ø§Ù… */
.toast.is-info .toast-ic.spin svg { animation: toastSpin 0.9s linear infinite; }

.toast-body { flex: 1; min-width: 0; padding-top: 1px; }
.toast-title { font-size: 14px; font-weight: 800; color: var(--text-color); margin: 0 0 2px; }
.toast-msg { font-size: 13px; color: var(--muted-color); line-height: 1.7; word-break: break-word; }

.toast-close {
    flex-shrink: 0;
    background: transparent;
    border: none;
    color: var(--muted-color);
    cursor: pointer;
    width: 26px; height: 26px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    transition: all var(--anim-fast);
}
.toast-close:hover { background: var(--surface-2); color: var(--text-color); }
.toast-close svg { width: 16px; height: 16px; }

/* Ù†ÙˆØ§Ø± Ù¾ÛŒØ´Ø±ÙØª Ø¯Ø§Ø®Ù„ ØªÙˆØ³Øª */
.toast-progress { display: none; padding: 0 16px 14px; }
.toast-progress.active { display: block; }
.toast-progress .bar-track { width: 100%; height: 8px; background: var(--border-color); border-radius: 999px; overflow: hidden; }
.toast-progress .bar-fill {
    height: 100%; width: 0%; border-radius: 999px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    transition: width 0.15s ease;
}

@keyframes toastSlide { from { opacity: 0; transform: translateY(16px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes toastOut   { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(16px); } }
@keyframes toastSpin  { to { transform: rotate(360deg); } }

/* ===== Ù…ÙˆØ¯Ø§Ù„â€ŒÙ‡Ø§ ===== */
.date-modal {
    position: fixed; inset: 0;
    background: var(--modal-overlay);
    display: none; align-items: center; justify-content: center;
    z-index: 10000; padding: 12px;
}
.date-modal.show { display: flex; }
.date-modal.show .date-modal-box { transform: translateY(0) scale(1); opacity: 1; }
.date-modal-box {
    background: var(--panel-bg);
    color: var(--text-color);
    width: 100%; max-width: 380px;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 16px;
    box-shadow: var(--shadow-md);
    transform: translateY(10px) scale(0.98);
    opacity: 0;
    transition: transform var(--anim-mid) ease, opacity var(--anim-mid) ease;
}
.date-modal-box { max-width: 360px; }
.date-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.date-modal-title { margin: 0; color: var(--primary-color); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 7px; }
.date-modal-title svg { width: 18px; height: 18px; }
.dp-close {
    width: 32px; height: 32px; border-radius: 9px; border: 1px solid var(--border-color);
    background: var(--surface-2); color: var(--muted-color); cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all var(--anim-fast);
}
.dp-close:hover { background: var(--danger); color: #fff; border-color: var(--danger); }
.dp-close svg { width: 15px; height: 15px; }

/* ===== ØªÙ‚ÙˆÛŒÙ… Ú¯Ø±ÛŒØ¯ ===== */
.dp-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 8px; }
.dp-nav-btn {
    width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--border-color);
    background: var(--surface-2); color: var(--text-color); cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all var(--anim-fast); flex-shrink: 0;
}
.dp-nav-btn:hover { background: var(--primary-color); color: #fff; border-color: var(--primary-color); }
.dp-nav-btn svg { width: 17px; height: 17px; }
.dp-month-label { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; }
.dp-label-btn {
    border: none; background: transparent; color: var(--text-color); cursor: pointer;
    font-family: inherit; font-size: 14px; font-weight: 800; padding: 5px 10px; border-radius: 8px;
    transition: background var(--anim-fast), color var(--anim-fast);
}
.dp-label-btn:hover { background: rgba(0,125,117,0.10); color: var(--primary-color); }

/* Ù¾Ù†Ù„ Ø§Ù†ØªØ®Ø§Ø¨ Ù…Ø§Ù‡/Ø³Ø§Ù„ */
.dp-grid-pick { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; padding: 4px 0; max-height: 252px; overflow-y: auto; }
.dp-pick-item {
    border: 1px solid var(--border-color); background: var(--surface-2); color: var(--text-color);
    border-radius: 10px; padding: 12px 6px; cursor: pointer; font-family: inherit; font-size: 13px; font-weight: 700;
    transition: all var(--anim-fast);
}
.dp-pick-item:hover { background: rgba(0,125,117,0.10); border-color: var(--primary-color); }
.dp-pick-item.is-selected { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: #fff; border-color: transparent; }
.dp-pick-item.is-today { box-shadow: inset 0 0 0 1.5px var(--secondary-color); }

.dp-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 4px; }
.dp-weekdays span { text-align: center; font-size: 11px; font-weight: 700; color: var(--muted-color); padding: 4px 0; }
.dp-days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
.dp-day {
    aspect-ratio: 1; border: none; background: transparent; color: var(--text-color);
    border-radius: 10px; cursor: pointer; font-family: inherit; font-size: 13px; font-weight: 600;
    display: flex; align-items: center; justify-content: center; transition: background var(--anim-fast), color var(--anim-fast), transform var(--anim-fast);
}
.dp-day:hover { background: rgba(0,125,117,0.10); }
.dp-day.is-empty { background: transparent; cursor: default; pointer-events: none; }
.dp-day.is-today { box-shadow: inset 0 0 0 1.5px var(--secondary-color); color: var(--secondary-color); }
.dp-day.is-selected { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: #fff; box-shadow: var(--shadow-sm); transform: scale(1.04); }

/* ===== Ø§Ù†ØªØ®Ø§Ø¨ Ø²Ù…Ø§Ù† ===== */
.dp-time { margin-top: 14px; border-top: 1px solid var(--border-color); padding-top: 14px; }
.dp-time-head { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--primary-color); margin-bottom: 10px; }
.dp-time-head svg { width: 15px; height: 15px; }
.dp-time-row { display: flex; align-items: center; justify-content: center; gap: 10px; }
.dp-stepper { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.dp-step-btn {
    width: 30px; height: 24px; border-radius: 7px; border: 1px solid var(--border-color);
    background: var(--surface-2); color: var(--text-color); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all var(--anim-fast);
}
.dp-step-btn:hover { background: var(--primary-color); color: #fff; border-color: var(--primary-color); }
.dp-step-btn svg { width: 14px; height: 14px; }
.dp-time-val {
    width: 56px; text-align: center; font-size: 22px; font-weight: 800; color: var(--text-color);
    background: var(--surface-2); border: 1px solid var(--border-color); border-radius: 10px; padding: 6px 0; font-family: inherit;
}
.dp-time-colon { font-size: 22px; font-weight: 800; color: var(--primary-color); }

.date-actions { margin-top: 16px; display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap; }

#previewModal {
    position: fixed; inset: 0;
    background: var(--modal-overlay);
    display: none; z-index: 9999; overflow-y: auto;
    padding: 24px 16px;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
.modal-content {
    max-width: 880px; margin: 0 auto;
    background: var(--panel-bg); color: var(--text-color);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    position: relative;
    overflow: hidden;
    transform: translateY(14px) scale(0.98);
    opacity: 0;
    transition: transform var(--anim-mid) ease, opacity var(--anim-mid) ease;
}
#previewModal.show .modal-content { transform: translateY(0) scale(1); opacity: 1; }

/* Ù†ÙˆØ§Ø± Ø¨Ø§Ù„Ø§ÛŒ Ù…ÙˆØ¯Ø§Ù„ Ù¾ÛŒØ´â€ŒÙ†Ù…Ø§ÛŒØ´ */
.preview-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border-color);
    background: var(--surface-2);
    position: sticky;
    top: 0;
    z-index: 2;
}
.preview-bar-title {
    display: flex; align-items: center; gap: 10px;
    font-size: 15px; font-weight: 800; color: var(--header-text);
}
.preview-bar-title .ph-ic { width: 34px; height: 34px; border-radius: 10px; }
.preview-bar-title .ph-ic svg { width: 17px; height: 17px; }
.preview-close {
    background: transparent;
    color: var(--muted-color);
    border: 1px solid var(--border-color);
    width: 34px; height: 34px;
    border-radius: 10px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all var(--anim-fast);
    font-family: inherit;
}
.preview-close:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; }
.preview-close svg { width: 18px; height: 18px; }

.preview-scroll { padding: 22px; }

/* Ù…Ù‚Ø§Ù„Ù‡ Ù¾ÛŒØ´â€ŒÙ†Ù…Ø§ÛŒØ´ */
.pv-article { animation: toastIn 380ms ease both; }
.pv-hero {
    position: relative;
    width: 100%;
    height: 280px;
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 20px;
    background: var(--surface-2);
}
.pv-hero img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pv-hero::after {
    content: ""; position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.55), rgba(0,0,0,0) 55%);
}
.pv-hero-cat {
    position: absolute; top: 14px; right: 14px; z-index: 2;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: #fff; font-size: 12px; font-weight: 700;
    padding: 5px 12px; border-radius: 999px;
    box-shadow: var(--shadow-sm);
}
.pv-hero-title {
    position: absolute; right: 18px; left: 18px; bottom: 16px; z-index: 2;
    color: #fff; font-size: 26px; font-weight: 800; line-height: 1.5;
    text-shadow: 0 2px 12px rgba(0,0,0,0.4);
    margin: 0;
}
.pv-title-fallback {
    font-size: 26px; font-weight: 800; color: var(--header-text);
    margin: 0 0 14px; line-height: 1.5;
}
.pv-meta {
    display: flex; flex-wrap: wrap; gap: 8px;
    margin-bottom: 20px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--border-color);
}
.pv-meta .chip { background: var(--surface-2); }
.pv-meta .chip svg { width: 14px; height: 14px; color: var(--primary-color); }
.pv-body {
    line-height: 2.1; font-size: 16px; color: var(--text-color);
}
.pv-body img { max-width: 100%; height: auto; border-radius: 10px; }
.pv-body .gallery-row img { border-radius: 8px; }
.pv-empty { color: var(--muted-color); font-style: italic; }

/* Ù¾Ù†Ù„ Ø®Ù„Ø§ØµÙ‡ Ø³Ø¦Ùˆ Ø¯Ø± Ù¾ÛŒØ´â€ŒÙ†Ù…Ø§ÛŒØ´ */
.pv-seo {
    margin-top: 26px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    background: var(--surface-2);
    padding: 18px;
}
.pv-seo-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; margin-bottom: 14px; flex-wrap: wrap;
}
.pv-seo-head .card-title { margin: 0; }
.pv-seo-badge {
    font-size: 13px; font-weight: 800;
    padding: 4px 12px; border-radius: 999px; color: #fff;
}
.pv-seo-bar-track {
    height: 12px; background: var(--border-color);
    border-radius: 999px; overflow: hidden;
}
.pv-seo-bar-fill { height: 100%; border-radius: 999px; transition: width .5s ease, background .5s ease; }
.pv-seo-foot {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 8px; font-size: 13px; color: var(--muted-color);
}
.pv-seo-foot b { color: var(--text-color); }
.pv-checks { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px; margin-top: 14px; }
.pv-check {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: var(--text-color);
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 8px 10px;
}
.pv-check svg { width: 16px; height: 16px; flex-shrink: 0; }
.pv-check.ok svg { color: #00b894; }
.pv-check.no { color: var(--muted-color); }
.pv-check.no svg { color: var(--muted-color); opacity: 0.7; }

/* ===== Ø±ÛŒØ³Ù¾Ø§Ù†Ø³ÛŒÙˆ ===== */
@media (max-width: 980px) {
    .layout { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    #title { font-size: 22px; }
    .eh-actions { width: 100%; }
    .eh-actions .btn { flex: 1; }
    .tb-btn { flex: 1 1 calc(16.66% - 6px); }
}

/* ===== Ø§Ù†ÛŒÙ…ÛŒØ´Ù†â€ŒÙ‡Ø§ ===== */
.col-side > .card { animation-delay: 90ms; }
.col-side > .card:nth-child(2) { animation-delay: 150ms; }
.col-side > .card:nth-child(3) { animation-delay: 210ms; }

@keyframes pageRise { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes cardPop { from { opacity: 0; transform: translateY(16px) scale(0.985); } to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes toastIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation: none !important; transition: none !important; }
}
</style>
</head>

<body>

<div class="container">

    <!-- Ù‡Ø¯Ø± Ú†Ø³Ø¨Ø§Ù† -->
    <div class="editor-header">
        <div class="eh-title">
            <span class="ph-ic">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </span>
            <div>
                <h1><?= $id > 0 ? 'ÙˆÛŒØ±Ø§ÛŒØ´ Ø®Ø¨Ø±' : 'Ø§ÛŒØ¬Ø§Ø¯ Ø®Ø¨Ø± Ø¬Ø¯ÛŒØ¯' ?></h1>
                <p>Ù…Ø¯ÛŒØ±ÛŒØª Ù…Ø­ØªÙˆØ§ÛŒ Ø¯ÛŒØ¬ÛŒØªØ§Ù„</p>
            </div>
            <span class="draft-badge">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Ù¾ÛŒØ´â€ŒÙ†ÙˆÛŒØ³ Ø°Ø®ÛŒØ±Ù‡ Ù†Ø´Ø¯Ù‡
            </span>
        </div>
        <div class="eh-actions">
            <button type="button" class="btn btn-ghost" onclick="openPreview()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Ù¾ÛŒØ´â€ŒÙ†Ù…Ø§ÛŒØ´
            </button>
            <button type="button" class="btn btn-save" onclick="saveNews()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <?= $id > 0 ? 'Ø°Ø®ÛŒØ±Ù‡ ÙˆÛŒØ±Ø§ÛŒØ´' : 'Ø«Ø¨Øª Ø®Ø¨Ø± Ù†Ù‡Ø§ÛŒÛŒ' ?>
            </button>
        </div>
    </div>

    <div class="layout">

        <!-- Ø³ØªÙˆÙ† Ø§ØµÙ„ÛŒ: Ø¹Ù†ÙˆØ§Ù† + Ø§Ø¯ÛŒØªÙˆØ± -->
        <div class="col-main">

            <!-- Ø¹Ù†ÙˆØ§Ù† Ø®Ø¨Ø± -->
            <div class="card title-card" id="titleCard">
                <input type="text" class="" id="title" maxlength="120" placeholder="Ø¹Ù†ÙˆØ§Ù† Ø®Ø¨Ø± Ø±Ø§ Ø§ÛŒÙ†Ø¬Ø§ Ø¨Ù†ÙˆÛŒØ³ÛŒØ¯..." value="<?= $news_data ? htmlspecialchars($news_data['title']) : '' ?>">
                <div class="title-meta">
                    <span>Ø­Ø¯Ø§Ú©Ø«Ø± Û±Û²Û° Ú©Ø§Ø±Ø§Ú©ØªØ±</span>
                    <span id="titleCounter">0/120</span>
                </div>
                <div class="field-error" id="titleError">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>ÙˆØ§Ø±Ø¯ Ú©Ø±Ø¯Ù† Ø¹Ù†ÙˆØ§Ù† Ø®Ø¨Ø± Ø§Ù„Ø²Ø§Ù…ÛŒ Ø§Ø³Øª.</span>
                </div>
            </div>

            <!-- Ø²ÛŒØ±Ø¹Ù†ÙˆØ§Ù† Ø®Ø¨Ø± -->
            <div class="subtitle-card" id="subtitleCard">
                <textarea id="subtitle" maxlength="200" rows="2" placeholder="Ø²ÛŒØ±Ø¹Ù†ÙˆØ§Ù† (ØªÙˆØ¶ÛŒØ­ Ú©ÙˆØªØ§Ù‡) Ø®Ø¨Ø± Ø±Ø§ Ø§ÛŒÙ†Ø¬Ø§ Ø¨Ù†ÙˆÛŒØ³ÛŒØ¯..."><?= $news_data ? htmlspecialchars($news_data['subtitle'] ?? '') : '' ?></textarea>
                <div class="subtitle-meta">
                    <span>Ø­Ø¯Ø§Ú©Ø«Ø± Û²Û°Û° Ú©Ø§Ø±Ø§Ú©ØªØ±</span>
                    <span id="subtitleCounter">0/200</span>
                </div>
            </div>

            <!-- Ù…ØªÙ† Ø®Ø¨Ø± -->
            <div class="card card-pad">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                    Ù…ØªÙ† Ø®Ø¨Ø±
                </h3>

                <div class="editor-shell" id="editorShell" style="margin-top: 12px;">
                    <textarea id="editor" name="content" style="width:100%; min-height:480px;"><?= $news_data ? htmlspecialchars($news_data['content']) : '' ?></textarea>
                </div>
                <div class="field-error" id="contentError">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>Ù†ÙˆØ´ØªÙ† Ù…ØªÙ† Ø®Ø¨Ø± Ø§Ù„Ø²Ø§Ù…ÛŒ Ø§Ø³Øª.</span>
                </div>

                <!-- Ø¯Ú©Ù…Ù‡â€ŒÙ‡Ø§ÛŒ Ø¯Ø±Ø¬ Ø³Ø±ÛŒØ¹ ØªØµÙˆÛŒØ± Ùˆ Ú¯Ø§Ù„Ø±ÛŒ -->
                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:14px;">
                    <button type="button" class="btn-insert" onclick="uploadSingleImage()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        Ø¯Ø±Ø¬ Ø³Ø±ÛŒØ¹ ØªØµÙˆÛŒØ± (Ø¢Ù¾Ù„ÙˆØ¯ / Drag & Drop)
                    </button>
                    <button type="button" class="btn-insert" onclick="uploadGalleryInline()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Ø¯Ø±Ø¬ Ú¯Ø§Ù„Ø±ÛŒ ØªØµØ§ÙˆÛŒØ± Ø´Ø¨Ú©Ù‡
                    </button>
                    <span style="font-size:12px;color:var(--muted-color);align-self:center;">Ø´Ù…Ø§ Ù‡Ù…Ú†Ù†ÛŒÙ† Ù…ÛŒâ€ŒØªÙˆØ§Ù†ÛŒØ¯ ØªØµØ§ÙˆÛŒØ± Ø±Ø§ Ù…Ø³ØªÙ‚ÛŒÙ…Ø§Ù‹ Ø¨Ú©Ø´ÛŒØ¯ Ùˆ Ø¯Ø§Ø®Ù„ Ù…ØªÙ† Ø±Ù‡Ø§ Ú©Ù†ÛŒØ¯ (Drag & Drop) ÛŒØ§ Copy-Paste Ú©Ù†ÛŒØ¯.</span>
                </div>

            </div>

        </div>

        <!-- Ø³ØªÙˆÙ† Ú©Ù†Ø§Ø±ÛŒ: Ù…ØªØ§Ø¯ÛŒØªØ§ -->
        <div class="col-side">

            <!-- ØªØµÙˆÛŒØ± Ø´Ø§Ø®Øµ -->
            <div class="card card-pad">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    ØªØµÙˆÛŒØ± Ø´Ø§Ø®Øµ
                </h3>

                <input type="file" id="featured_image" name="featured_image" accept="image/*" style="display:none;">
                <input type="hidden" id="remove_featured_flag" name="remove_featured_flag" value="0">

                <div class="dropzone" id="dropzone">
                    <div class="dz-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    </div>
                    <div class="dz-main">ØªØµÙˆÛŒØ± Ø±Ø§ Ø§ÛŒÙ†Ø¬Ø§ Ø¨Ú©Ø´ÛŒØ¯ ÛŒØ§ Ú©Ù„ÛŒÚ© Ú©Ù†ÛŒØ¯</div>
                    <div class="dz-hint">PNG, JPG Ø­Ø¯Ø§Ú©Ø«Ø± Ûµ Ù…Ú¯Ø§Ø¨Ø§ÛŒØª</div>

                    <div class="dz-preview <?= !empty($existing_image_url) ? 'show' : '' ?>" id="dzPreview">
                        <img id="dzImg" src="<?= htmlspecialchars($existing_image_url) ?>" alt="Ù¾ÛŒØ´â€ŒÙ†Ù…Ø§ÛŒØ´ ØªØµÙˆÛŒØ±">
                        <button class="dz-remove" type="button" onclick="removeFeatured(event)" title="Ø­Ø°Ù ØªØµÙˆÛŒØ±">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ø§Ù…ØªÛŒØ§Ø² Ø³Ø¦Ùˆ -->
            <div class="card card-pad">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Ø§Ù…ØªÛŒØ§Ø² Ø³Ø¦Ùˆ
                </h3>
                <div class="seo-widget">
                    <div class="seo-ring">
                        <svg viewBox="0 0 36 36">
                            <path class="ring-track" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke-width="3"/>
                            <path class="ring-fill" id="seoRingFill" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#F79F1F" stroke-dasharray="0, 100" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        <span class="ring-num" id="seoRingNum">Û°</span>
                    </div>
                    <div class="seo-info">
                        <div class="seo-state">ÙˆØ¶Ø¹ÛŒØª: <b id="seoState" style="color:var(--muted-color)">â€”</b></div>
                        <div class="seo-tip" id="seoTip">Ø¨Ø§ Ù¾Ø± Ú©Ø±Ø¯Ù† ÙÛŒÙ„Ø¯Ù‡Ø§ Ø§Ù…ØªÛŒØ§Ø² Ù…Ø­Ø§Ø³Ø¨Ù‡ Ù…ÛŒâ€ŒØ´ÙˆØ¯.</div>
                    </div>
                </div>
            </div>

            <!-- ØªÙ†Ø¸ÛŒÙ…Ø§Øª -->
            <div class="card settings-card">

                <div class="input-group">
                    <label class="field-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        ØªØ§Ø±ÛŒØ® Ùˆ Ø²Ù…Ø§Ù† Ø§Ù†ØªØ´Ø§Ø±
                    </label>
                    <div class="publish-date-row">
                        <input type="hidden" id="publish_date" name="publish_date" value="<?= htmlspecialchars($news_data['publish_date'] ?? '') ?>">
                        <input type="text" id="publish_date_display" class="input" readonly placeholder="ØªØ§Ø±ÛŒØ® Ùˆ Ø²Ù…Ø§Ù† Ø´Ù…Ø³ÛŒ Ø±Ø§ Ø§Ù†ØªØ®Ø§Ø¨ Ú©Ù†ÛŒØ¯">
                        <button type="button" class="btn-insert" onclick="openDatePicker()">ðŸ“… Ø§Ù†ØªØ®Ø§Ø¨</button>
                        <button type="button" class="btn-insert now-btn" onclick="setNow()">Ù‡Ù…ÛŒÙ† Ø§Ù„Ø§Ù†</button>
                    </div>
                </div>

                <div class="input-group" id="categoryGroup">
                    <label class="field-label" for="category_id">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        Ø¯Ø³ØªÙ‡ Ø®Ø¨Ø±
                    </label>
                    <select id="category_id" name="category_id" class="input" required>
                        <option value="">Ø§Ù†ØªØ®Ø§Ø¨ Ø¯Ø³ØªÙ‡</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int)$category['id'] ?>" <?= $selectedCategoryId === (int)$category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="field-error" id="categoryError">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>Ø§Ù†ØªØ®Ø§Ø¨ Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ Ø§Ù„Ø²Ø§Ù…ÛŒ Ø§Ø³Øª.</span>
                    </div>
                </div>

                <div class="input-group">
                    <label class="field-label" for="author">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Ù†ÙˆÛŒØ³Ù†Ø¯Ù‡
                    </label>
                    <input type="text" class="input" id="author" value="<?= $news_data ? htmlspecialchars($news_data['author']) : '' ?>" placeholder="Ù†Ø§Ù… Ù†ÙˆÛŒØ³Ù†Ø¯Ù‡">
                </div>

                <div class="input-group">
                    <label class="field-label" for="keywords">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><circle cx="12" cy="12" r="2"/></svg>
                        Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ
                    </label>
                    <input type="text" class="input" id="keywords" value="<?= $news_data ? htmlspecialchars($news_data['keywords']) : '' ?>" placeholder="Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯">
                </div>

                <!-- Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯ Ù‡ÙˆØ´Ù…Ù†Ø¯ Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ -->
                <div class="input-group" id="suggestion-container" style="margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--primary-color); display: inline-flex; align-items: center; gap: 4px;">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯ÛŒ Ø§Ø² Ù…ØªÙ†
                        </span>
                        <button type="button" onclick="generateSuggestions()" class="btn-insert" style="border-style: solid; padding: 4px 10px; border-radius: 999px; font-size: 11px; cursor: pointer;">
                            âœ¨ Ø§Ø³Ú©Ù† Ùˆ Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯
                        </button>
                    </div>
                    <div id="suggested-chips-list" style="display: flex; flex-wrap: wrap; gap: 6px; padding: 8px; border: 1px dashed var(--border-color); border-radius: var(--radius-sm); min-height: 44px; align-items: center; background: var(--surface-2);">
                        <span style="font-size: 11.5px; color: var(--muted-color); font-style: italic; padding: 0 4px;">Ø¨Ø±Ø§ÛŒ Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒØŒ Ø±ÙˆÛŒ Ø¯Ú©Ù…Ù‡ Ø§Ø³Ú©Ù† Ú©Ù„ÛŒÚ© Ú©Ù†ÛŒØ¯.</span>
                    </div>
                </div>

                <div class="input-group">
                    <label class="field-label" for="tags">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        ØªÚ¯â€ŒÙ‡Ø§ÛŒ Ø³Ø¦Ùˆ
                    </label>
                    <input type="text" class="input" id="tags" value="<?= $news_data ? htmlspecialchars($news_data['tags']) : '' ?>" placeholder="ØªÚ¯â€ŒÙ‡Ø§ Ø±Ø§ Ø¨Ø§ Ú©Ø§Ù…Ø§ Ø¬Ø¯Ø§ Ú©Ù†ÛŒØ¯">
                    <div class="chips" id="tagsChips"></div>
                </div>

                <div class="input-group">
                    <label class="field-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        Ø¨Ø±Ú†Ø³Ø¨â€ŒÙ‡Ø§ÛŒ Ù…ÙˆØ¶ÙˆØ¹ÛŒ Ø®Ø¨Ø± (Ú†Ù†Ø¯ Ø§Ù†ØªØ®Ø§Ø¨ÛŒ)
                    </label>
                    <div class="tags-checklist" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
                        <?php foreach ($all_tags as $t): ?>
                            <label class="tag-checkbox-label">
                                <input type="checkbox" name="tag_ids[]" value="<?= $t['id'] ?>" <?= in_array($t['id'], $selected_tag_ids) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($t['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<!-- ØªÙˆØ³Øª ÙˆØ¶Ø¹ÛŒØª (Ù…ÙˆÙÙ‚ÛŒØª / Ø®Ø·Ø§ / Ø¯Ø± Ø­Ø§Ù„ Ø§Ù†Ø¬Ø§Ù…) -->
<div id="statusToast" class="toast" role="status" aria-live="polite">
    <div class="toast-row">
        <div class="toast-ic" id="toastIcon"></div>
        <div class="toast-body">
            <p class="toast-title" id="toastTitle"></p>
            <div class="toast-msg" id="toastMsg"></div>
        </div>
        <button type="button" class="toast-close" onclick="hideStatus()" aria-label="Ø¨Ø³ØªÙ†">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div id="uploadProgress" class="toast-progress">
        <div class="bar-track"><div id="uploadBarFill" class="bar-fill"></div></div>
    </div>
</div>

<!-- Ù…ÙˆØ¯Ø§Ù„ Ù¾ÛŒØ´â€ŒÙ†Ù…Ø§ÛŒØ´ -->
<div id="previewModal">
    <div class="modal-content">
        <div class="preview-bar">
            <div class="preview-bar-title">
                <span class="ph-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </span>
                Ù¾ÛŒØ´â€ŒÙ†Ù…Ø§ÛŒØ´ Ø®Ø¨Ø±
            </div>
            <button onclick="closePreview()" class="preview-close" title="Ø¨Ø³ØªÙ†">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="preview-scroll">
            <div id="previewContent"></div>
        </div>
    </div>
</div>

<!-- Ù…ÙˆØ¯Ø§Ù„ Ø§Ù†ØªØ®Ø§Ø¨ ØªØ§Ø±ÛŒØ® (ØªÙ‚ÙˆÛŒÙ… Ø´Ù…Ø³ÛŒ Ù…Ø¯Ø±Ù†) -->
<div id="dateModal" class="date-modal" onclick="if(event.target===this) closeDatePicker()">
    <div class="date-modal-box">
        <div class="date-header">
            <h4 class="date-modal-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                ØªÙ‚ÙˆÛŒÙ… Ø´Ù…Ø³ÛŒ
            </h4>
            <button type="button" class="dp-close" onclick="closeDatePicker()" aria-label="Ø¨Ø³ØªÙ†">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <!-- Ù†Ø§ÙˆØ¨Ø±ÛŒ Ù…Ø§Ù‡ -->
        <div class="dp-nav">
            <button type="button" class="dp-nav-btn" onclick="dpChangeMonth(1)" aria-label="Ù…Ø§Ù‡ Ø¨Ø¹Ø¯">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div class="dp-month-label">
                <button type="button" class="dp-label-btn" id="dpMonthBtn" onclick="dpToggleView('months')">â€”</button>
                <button type="button" class="dp-label-btn" id="dpYearBtn" onclick="dpToggleView('years')">â€”</button>
            </div>
            <button type="button" class="dp-nav-btn" onclick="dpChangeMonth(-1)" aria-label="Ù…Ø§Ù‡ Ù‚Ø¨Ù„">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>

        <!-- Ù†Ù…Ø§ÛŒ Ø±ÙˆØ² -->
        <div class="dp-view" id="dpDayView">
            <div class="dp-weekdays">
                <span>Ø´</span><span>ÛŒ</span><span>Ø¯</span><span>Ø³</span><span>Ú†</span><span>Ù¾</span><span>Ø¬</span>
            </div>
            <div class="dp-days" id="dpDays"></div>
        </div>
        <!-- Ù†Ù…Ø§ÛŒ Ù…Ø§Ù‡ -->
        <div class="dp-view dp-grid-pick" id="dpMonthView" style="display:none;"></div>
        <!-- Ù†Ù…Ø§ÛŒ Ø³Ø§Ù„ -->
        <div class="dp-view dp-grid-pick" id="dpYearView" style="display:none;"></div>

        <!-- Ø§Ù†ØªØ®Ø§Ø¨ Ø²Ù…Ø§Ù† -->
        <div class="dp-time">
            <div class="dp-time-head">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Ø§Ù†ØªØ®Ø§Ø¨ Ø²Ù…Ø§Ù†
            </div>
            <div class="dp-time-row">
                <div class="dp-stepper">
                    <button type="button" class="dp-step-btn" onclick="dpStepTime('hh', 1)" aria-label="Ø³Ø§Ø¹Øª Ø¨ÛŒØ´ØªØ±"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></button>
                    <input type="text" class="dp-time-val" id="dpHourVal" value="Û°Û°" readonly inputmode="numeric">
                    <button type="button" class="dp-step-btn" onclick="dpStepTime('hh', -1)" aria-label="Ø³Ø§Ø¹Øª Ú©Ù…ØªØ±"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>
                </div>
                <span class="dp-time-colon">:</span>
                <div class="dp-stepper">
                    <button type="button" class="dp-step-btn" onclick="dpStepTime('mm', 1)" aria-label="Ø¯Ù‚ÛŒÙ‚Ù‡ Ø¨ÛŒØ´ØªØ±"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></button>
                    <input type="text" class="dp-time-val" id="dpMinuteVal" value="Û°Û°" readonly inputmode="numeric">
                    <button type="button" class="dp-step-btn" onclick="dpStepTime('mm', -1)" aria-label="Ø¯Ù‚ÛŒÙ‚Ù‡ Ú©Ù…ØªØ±"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>
                </div>
            </div>
        </div>

        <div class="date-actions">
            <button type="button" class="btn-insert" onclick="setPickerNow()">Ø§Ú©Ù†ÙˆÙ†</button>
            <button type="button" class="btn-insert" onclick="closeDatePicker()">Ø§Ù†ØµØ±Ø§Ù</button>
            <button type="button" class="btn btn-save" style="padding:10px 16px;" onclick="applyDatePicker()">ØªØ§ÛŒÛŒØ¯</button>
        </div>

        <!-- state Ù…Ø®ÙÛŒ Ú©Ù‡ ØªÙˆØ§Ø¨Ø¹ ØªØ¨Ø¯ÛŒÙ„ Ø§Ø² Ø¢Ù† Ù…ÛŒâ€ŒØ®ÙˆØ§Ù†Ù†Ø¯/Ù…ÛŒâ€ŒÙ†ÙˆÛŒØ³Ù†Ø¯ -->
        <input type="hidden" id="jy"><input type="hidden" id="jm"><input type="hidden" id="jd">
        <input type="hidden" id="hh"><input type="hidden" id="mm">
    </div>
</div>

<script>
/* Ø°Ø®ÛŒØ±Ù‡ Ù…Ø­Ø¯ÙˆØ¯Ù‡ Ø§Ù†ØªØ®Ø§Ø¨ */
let savedRange = null;
const editor = document.getElementById("editor");

editor.addEventListener("mouseup", saveSelection);
editor.addEventListener("keyup", saveSelection);
editor.addEventListener("focus", saveSelection);

function saveSelection(){
    const sel = window.getSelection();
    if(sel.rangeCount > 0){
        savedRange = sel.getRangeAt(0);
    }
}

function restoreSelection(){
    if(savedRange){
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(savedRange);
    }
}

/* ===================== Ø§Ø¨Ø²Ø§Ø±Ù‡Ø§ÛŒ Ù¾Ø§ÛŒÙ‡ ===================== */
function format(cmd,val=null){
    editor.focus();
    restoreSelection();
    document.execCommand(cmd,false,val);
    saveSelection();
}

/* ===================== Ø±Ù†Ú¯ Ù…ØªÙ† ===================== */
function applyColor(color){
    restoreSelection();
    document.execCommand("foreColor", false, color);
    saveSelection();
}

/* ===================== Ù‡Ø¯ÛŒÙ†Ú¯ ===================== */
function setHeading(selectEl){
    editor.focus();
    restoreSelection();
    const tag = selectEl.value;
    if(tag==="p"){
        document.execCommand("formatBlock",false,"p");
    }else{
        document.execCommand("formatBlock",false,tag);
    }
    saveSelection();
}

/* ===================== ÙÙˆÙ†Øª ===================== */
function setFont(selectEl){
    const font = selectEl.value;
    if(!font) return;
    editor.focus();
    restoreSelection();
    document.execCommand("fontName",false,font);
    selectEl.value="";
    saveSelection();
}

/* ===================== Ø³Ø§ÛŒØ² ÙÙˆÙ†Øª ===================== */
function setFontSize(selectEl){
    const size = selectEl.value;
    if(!size) return;
    editor.focus();
    restoreSelection();
    document.execCommand("fontSize",false,"7");
    const fonts = editor.querySelectorAll("font[size='7']");
    fonts.forEach(f=>{
        f.removeAttribute("size");
        f.style.fontSize = size + "px";
    });
    selectEl.value="";
    saveSelection();
}

/* ===================== Ù„ÛŒÙ†Ú© ===================== */
function insertLink(){
    editor.focus();
    restoreSelection();
    const url = prompt("Ø¢Ø¯Ø±Ø³ Ù„ÛŒÙ†Ú©:");
    if(url){
        document.execCommand("createLink",false,url);
    }
    saveSelection();
}

/* ===================== Ø­Ø°Ù ÙØ±Ù…Øª ===================== */
function clearFormat(){
    editor.focus();
    restoreSelection();
    document.execCommand("removeFormat");
    saveSelection();
}

/* ===================== Ú†ÛŒÙ†Ø´ Ù…ØªÙ† ===================== */
function alignRight(){ format("justifyRight"); }
function alignLeft(){ format("justifyLeft"); }
function alignCenter(){ format("justifyCenter"); }
function alignJustify(){ format("justifyFull"); }

/* =================== Ø´Ù…Ø§Ø±Ù†Ø¯Ù‡ Ø¹Ù†ÙˆØ§Ù† Ùˆ Ø²ÛŒØ±Ø¹Ù†ÙˆØ§Ù† =================== */
const titleInput = document.getElementById("title");
const titleCounter = document.getElementById("titleCounter");
function updateTitleCounter(){
    const len = titleInput.value.length;
    titleCounter.textContent = len + "/120";
    titleCounter.classList.toggle("over", len > 100);
}
titleInput.addEventListener("input", updateTitleCounter);

const subtitleInput = document.getElementById("subtitle");
const subtitleCounter = document.getElementById("subtitleCounter");
function updateSubtitleCounter(){
    const len = subtitleInput.value.length;
    subtitleCounter.textContent = len + "/200";
    subtitleCounter.classList.toggle("over", len > 180);
}
subtitleInput.addEventListener("input", updateSubtitleCounter);

/* =================== ØªØµÙˆÛŒØ± Ø´Ø§Ø®Øµ (Dropzone) =================== */
const featuredInput = document.getElementById("featured_image");
const dropzone = document.getElementById("dropzone");
const dzPreview = document.getElementById("dzPreview");
const dzImg = document.getElementById("dzImg");

dropzone.addEventListener("click", (e) => {
    if (e.target.closest(".dz-remove")) return;
    featuredInput.click();
});

["dragenter","dragover","dragleave","drop"].forEach(ev => {
    dropzone.addEventListener(ev, (e) => { e.preventDefault(); e.stopPropagation(); });
});
["dragenter","dragover"].forEach(ev => {
    dropzone.addEventListener(ev, () => dropzone.classList.add("drag-over"));
});
["dragleave","drop"].forEach(ev => {
    dropzone.addEventListener(ev, () => dropzone.classList.remove("drag-over"));
});
dropzone.addEventListener("drop", (e) => {
    const files = e.dataTransfer.files;
    if (files && files[0]) {
        featuredInput.files = files;
        showFeaturedPreview(files[0]);
    }
});

featuredInput.onchange = function(){
    document.getElementById("remove_featured_flag").value = "0";
    if (this.files[0]) showFeaturedPreview(this.files[0]);
};

function showFeaturedPreview(file){
    document.getElementById("remove_featured_flag").value = "0";
    const reader = new FileReader();
    reader.onload = e => {
        dzImg.src = e.target.result;
        dzPreview.classList.add("show");
    };
    reader.readAsDataURL(file);
}

function removeFeatured(e){
    if (e) e.stopPropagation();
    featuredInput.value = "";
    dzImg.src = "";
    dzPreview.classList.remove("show");
    document.getElementById("remove_featured_flag").value = "1";
}

/* ============== Ø¯Ø±Ø¬ ØªØµÙˆÛŒØ± Ø¯Ø§Ø®Ù„ Ù…ØªÙ† ============== */
function uploadSingleImage(){
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";

    input.onchange = ()=>{
        const file = input.files[0];
        if (!file) return;

        const fd = new FormData();
        fd.append("image", file);

        fetch("upload-inline-image.php", {method:"POST", body:fd})
        .then(r=>r.json())
        .then(d=>{
            if (d.url) insertSingleImage(d.url);
        });
    };
    input.click();
}

function insertSingleImage(url){
    const img = document.createElement("img");
    img.src = url;

    restoreSelection();
    const sel = window.getSelection();
    if (sel.rangeCount){
        const range = sel.getRangeAt(0);
        range.insertNode(img);
    }
    saveSelection();
}

function uploadGalleryInline(){
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";
    input.multiple = true;

    input.onchange = ()=>{
        const files = Array.from(input.files);
        if (!files.length) return;

        let urls = [];

        Promise.all(files.map(file=>{
            const fd = new FormData();
            fd.append("image", file);
            return fetch("upload-inline-image.php", {method:"POST", body:fd})
                .then(r=>r.json())
                .then(d=>{ if (d.url) urls.push(d.url); });
        })).then(()=> insertGalleryRow(urls));
    };
    input.click();
}

function insertGalleryRow(urls){
    const row = document.createElement("div");
    row.className = "gallery-row";
    row.style.cssText = "display:flex;gap:10px;margin:10px 0;flex-wrap:wrap;";

    urls.slice(0,3).forEach(u=>{
        const img = document.createElement("img");
        img.src = u;
        img.style.cssText = "flex:1;min-width:30%;max-width:100%;border-radius:8px;object-fit:cover;";
        row.appendChild(img);
    });

    restoreSelection();
    const sel = window.getSelection();
    if (sel.rangeCount){
        sel.getRangeAt(0).insertNode(row);
    }
    saveSelection();
}

/* =================== Ú†ÛŒÙ¾ ØªÚ¯â€ŒÙ‡Ø§ (Ù†Ù…Ø§ÛŒØ´ÛŒ) =================== */
const tagsInput = document.getElementById("tags");
const tagsChips = document.getElementById("tagsChips");
function renderTagChips(){
    const parts = tagsInput.value.split("ØŒ").join(",").split(",").map(t=>t.trim()).filter(Boolean);
    tagsChips.innerHTML = "";
    parts.forEach((t, idx) => {
        const chip = document.createElement("span");
        chip.className = "chip";
        chip.textContent = t;
        const btn = document.createElement("button");
        btn.type = "button";
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
        btn.onclick = () => { parts.splice(idx,1); tagsInput.value = parts.join("ØŒ "); renderTagChips(); };
        chip.appendChild(btn);
        tagsChips.appendChild(chip);
    });
}
tagsInput.addEventListener("input", renderTagChips);

/* =================== ØªÙˆØ³Øª ÙˆØ¶Ø¹ÛŒØª (Ù…ÙˆÙÙ‚ÛŒØª / Ø®Ø·Ø§ / Ø¯Ø± Ø­Ø§Ù„ Ø§Ù†Ø¬Ø§Ù…) =================== */
const TOAST_ICONS = {
    success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
    error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/></svg>'
};
const TOAST_TITLES = { success: "Ø§Ù†Ø¬Ø§Ù… Ø´Ø¯", error: "Ø®Ø·Ø§", info: "Ø¯Ø± Ø­Ø§Ù„ Ø§Ù†Ø¬Ø§Ù…" };

let toastHideTimer = null;

/*
 * Ù†Ù…Ø§ÛŒØ´ ØªÙˆØ³Øª ÙˆØ¶Ø¹ÛŒØª.
 *   showStatus(msg)               â†’ Ø§Ø·Ù„Ø§Ø¹â€ŒØ±Ø³Ø§Ù†ÛŒ (info)
 *   showStatus(msg, true)         â†’ Ù…ÙˆÙÙ‚ÛŒØª (success)
 *   showStatus(msg, false)        â†’ Ø®Ø·Ø§ (error)
 *   showStatus(msg, "info")       â†’ Ø­Ø§Ù„Øª Ø¯Ø± Ø­Ø§Ù„ Ø§Ù†Ø¬Ø§Ù… Ø¨Ø§ Ø§Ø³Ù¾ÛŒÙ†Ø±
 * Ø¨Ø±Ø§ÛŒ Ø³Ø§Ø²Ú¯Ø§Ø±ÛŒ Ø¨Ø§ ÙØ±Ø§Ø®ÙˆØ§Ù†ÛŒâ€ŒÙ‡Ø§ÛŒ Ù‚Ø¨Ù„ÛŒØŒ Ø§ÛŒÙ…ÙˆØ¬ÛŒâ€ŒÙ‡Ø§ÛŒ Ø§Ø¨ØªØ¯Ø§ÛŒ Ù¾ÛŒØ§Ù… Ø­Ø°Ù Ù…ÛŒâ€ŒØ´ÙˆÙ†Ø¯.
 */
function showStatus(msg, type){
    let kind = "info";
    if (type === true) kind = "success";
    else if (type === false) kind = "error";
    else if (typeof type === "string") kind = type;

    const cleanMsg = String(msg).replace(/^[âœ…âŒâ³âš¡ðŸ—œðŸ”¼â¬†\s]+/u, "").trim();

    const toast = document.getElementById("statusToast");
    const iconEl = document.getElementById("toastIcon");
    toast.classList.remove("is-success", "is-error", "is-info", "hide");
    toast.classList.add("is-" + kind, "show");

    iconEl.className = "toast-ic" + (kind === "info" ? " spin" : "");
    iconEl.innerHTML = TOAST_ICONS[kind];
    document.getElementById("toastTitle").textContent = TOAST_TITLES[kind];
    document.getElementById("toastMsg").textContent = cleanMsg;

    // Ù†ÙˆØ§Ø± Ù¾ÛŒØ´Ø±ÙØª ÙÙ‚Ø· Ø¯Ø± Ø­Ø§Ù„ØªÙ Â«Ø¯Ø± Ø­Ø§Ù„ Ø§Ù†Ø¬Ø§Ù…Â» Ù…Ø¹Ù†Ø§ Ø¯Ø§Ø±Ø¯
    if (kind !== "info") {
        document.getElementById("uploadProgress").classList.remove("active");
    }

    // Ù¾ÛŒØ§Ù… Ù…ÙˆÙÙ‚ÛŒØª/Ø®Ø·Ø§ Ù¾Ø³ Ø§Ø² Ú†Ù†Ø¯ Ø«Ø§Ù†ÛŒÙ‡ Ø®ÙˆØ¯Ú©Ø§Ø± Ø¨Ø³ØªÙ‡ Ù…ÛŒâ€ŒØ´ÙˆØ¯Ø› Ø­Ø§Ù„Øª Ø¯Ø± Ø­Ø§Ù„ Ø§Ù†Ø¬Ø§Ù… Ø¨Ø§Ø² Ù…ÛŒâ€ŒÙ…Ø§Ù†Ø¯.
    clearTimeout(toastHideTimer);
    if (kind !== "info") {
        toastHideTimer = setTimeout(hideStatus, kind === "error" ? 6000 : 3500);
    }
}

function hideStatus(){
    const toast = document.getElementById("statusToast");
    if (!toast.classList.contains("show")) return;
    clearTimeout(toastHideTimer);
    toast.classList.add("hide");
    setTimeout(() => {
        toast.classList.remove("show", "hide", "is-success", "is-error", "is-info");
        hideUploadProgress();
    }, 250);
}

/* ØªÙˆØ§Ø¨Ø¹ Ù…Ø±Ø¨ÙˆØ· Ø¨Ù‡ Ø²Ù…Ø§Ù†â€ŒØ¨Ù†Ø¯ÛŒ */
function setNow() {
    setDateFromGregorianDate(new Date());
}

function toFaDigits(value) {
    return String(value).replace(/[0-9]/g, d => 'Û°Û±Û²Û³Û´ÛµÛ¶Û·Û¸Û¹'[d]);
}

function pad2(num) {
    return String(num).padStart(2, '0');
}

function gregorianToJalali(gy, gm, gd) {
    const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    let jy;
    if (gy > 1600) {
        jy = 979;
        gy -= 1600;
    } else {
        jy = 0;
        gy -= 621;
    }
    const gy2 = gm > 2 ? gy + 1 : gy;
    let days = (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100)
        + Math.floor((gy2 + 399) / 400) - 80 + gd + g_d_m[gm - 1];
    jy += 33 * Math.floor(days / 12053);
    days %= 12053;
    jy += 4 * Math.floor(days / 1461);
    days %= 1461;
    if (days > 365) {
        jy += Math.floor((days - 1) / 365);
        days = (days - 1) % 365;
    }
    let jm, jd;
    if (days < 186) {
        jm = 1 + Math.floor(days / 31);
        jd = 1 + (days % 31);
    } else {
        jm = 7 + Math.floor((days - 186) / 30);
        jd = 1 + ((days - 186) % 30);
    }
    return [jy, jm, jd];
}

function jalaliToGregorian(jy, jm, jd) {
    let gy;
    if (jy > 979) {
        gy = 1600;
        jy -= 979;
    } else {
        gy = 621;
    }
    let days = (365 * jy) + (Math.floor(jy / 33) * 8) + Math.floor(((jy % 33) + 3) / 4) + 78 + jd
        + (jm < 7 ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
    gy += 400 * Math.floor(days / 146097);
    days %= 146097;
    if (days > 36524) {
        gy += 100 * Math.floor(--days / 36524);
        days %= 36524;
        if (days >= 365) days++;
    }
    gy += 4 * Math.floor(days / 1461);
    days %= 1461;
    if (days > 365) {
        gy += Math.floor((days - 1) / 365);
        days = (days - 1) % 365;
    }
    let gd = days + 1;
    const sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    let gm;
    for (gm = 1; gm <= 12; gm++) {
        if (gd <= sal_a[gm]) break;
        gd -= sal_a[gm];
    }
    return [gy, gm, gd];
}

function getJalaliMonthDays(jy, jm) {
    if (jm <= 6) return 31;
    if (jm <= 11) return 30;
    const leap = (((jy + 38) * 682) % 2816) < 682;
    return leap ? 30 : 29;
}

function renderPublishDisplay(jy, jm, jd, hh, mm) {
    const formatted = `${jy}/${pad2(jm)}/${pad2(jd)} - ${pad2(hh)}:${pad2(mm)}`;
    document.getElementById("publish_date_display").value = toFaDigits(formatted);
}

function setDateFromGregorianDate(dateObj) {
    const gy = dateObj.getFullYear();
    const gm = dateObj.getMonth() + 1;
    const gd = dateObj.getDate();
    const hh = dateObj.getHours();
    const mm = dateObj.getMinutes();
    const [jy, jm, jd] = gregorianToJalali(gy, gm, gd);
    document.getElementById("publish_date").value = `${gy}-${pad2(gm)}-${pad2(gd)} ${pad2(hh)}:${pad2(mm)}:00`;
    renderPublishDisplay(jy, jm, jd, hh, mm);
    setPickerValues(jy, jm, jd, hh, mm);
}

/* ===== Ø¯ÛŒØªâ€ŒÙ¾ÛŒÚ©Ø± Ø´Ù…Ø³ÛŒ Ù…Ø¯Ø±Ù† (ØªÙ‚ÙˆÛŒÙ… Ú¯Ø±ÛŒØ¯) ===== */
const PERSIAN_MONTHS = [
    "ÙØ±ÙˆØ±Ø¯ÛŒÙ†", "Ø§Ø±Ø¯ÛŒØ¨Ù‡Ø´Øª", "Ø®Ø±Ø¯Ø§Ø¯", "ØªÛŒØ±", "Ù…Ø±Ø¯Ø§Ø¯", "Ø´Ù‡Ø±ÛŒÙˆØ±",
    "Ù…Ù‡Ø±", "Ø¢Ø¨Ø§Ù†", "Ø¢Ø°Ø±", "Ø¯ÛŒ", "Ø¨Ù‡Ù…Ù†", "Ø§Ø³ÙÙ†Ø¯"
];
// Ù…Ø§Ù‡ÛŒ Ú©Ù‡ Ø§Ú©Ù†ÙˆÙ† Ø¯Ø± Ú¯Ø±ÛŒØ¯ Ù†Ù…Ø§ÛŒØ´ Ø¯Ø§Ø¯Ù‡ Ù…ÛŒâ€ŒØ´ÙˆØ¯ (Ù…Ù…Ú©Ù† Ø§Ø³Øª Ø¨Ø§ Ø±ÙˆØ²Ù Ø§Ù†ØªØ®Ø§Ø¨â€ŒØ´Ø¯Ù‡ ÙØ±Ù‚ Ú©Ù†Ø¯)
let dpViewYear = 0, dpViewMonth = 1;

function dpGet(id){ return parseInt(document.getElementById(id).value || "0", 10); }
function dpSet(id, v){ document.getElementById(id).value = String(v); }

// Ø±ÙˆØ²Ù Ù‡ÙØªÙ‡â€ŒÛŒ Ø§ÙˆÙ„Ù Ù…Ø§Ù‡Ù Ø´Ù…Ø³ÛŒ (Ø´Ù†Ø¨Ù‡=0 ... Ø¬Ù…Ø¹Ù‡=6)
function jalaliFirstDow(jy, jm) {
    const [gy, gm, gd] = jalaliToGregorian(jy, jm, 1);
    const js = new Date(gy, gm - 1, gd).getDay(); // ÛŒÚ©Ø´Ù†Ø¨Ù‡=0 ... Ø´Ù†Ø¨Ù‡=6
    return (js + 1) % 7;                          // Ø´Ù†Ø¨Ù‡=0 ... Ø¬Ù…Ø¹Ù‡=6
}

// state Ú©Ø§Ù…Ù„ Ù¾ÛŒÚ©Ø± Ø±Ø§ Ø³Øª Ù…ÛŒâ€ŒÚ©Ù†Ø¯ (Ø±ÙˆØ²Ù Ø§Ù†ØªØ®Ø§Ø¨â€ŒØ´Ø¯Ù‡ + Ø²Ù…Ø§Ù†) Ùˆ Ú¯Ø±ÛŒØ¯ Ø±Ø§ Ø±ÙˆÛŒ Ù‡Ù…Ø§Ù† Ù…Ø§Ù‡ Ù…ÛŒâ€ŒØ¨Ø±Ø¯
function setPickerValues(jy, jm, jd, hh, mm) {
    dpSet("jy", jy); dpSet("jm", jm); dpSet("jd", jd);
    dpSet("hh", hh); dpSet("mm", mm);
    dpViewYear = jy; dpViewMonth = jm;
    dpRenderTime();
    dpRenderCalendar();
}

// Ù†Ù…Ø§ÛŒ ÙØ¹Ø§Ù„Ù Ù¾ÛŒÚ©Ø±: 'days' | 'months' | 'years'
let dpView = 'days';

function dpToggleView(which) {
    dpView = (dpView === which) ? 'days' : which;
    dpApplyView();
}
function dpApplyView() {
    document.getElementById("dpDayView").style.display   = dpView === 'days'   ? '' : 'none';
    document.getElementById("dpMonthView").style.display = dpView === 'months' ? 'grid' : 'none';
    document.getElementById("dpYearView").style.display  = dpView === 'years'  ? 'grid' : 'none';
    if (dpView === 'months') dpRenderMonths();
    else if (dpView === 'years') dpRenderYears();
}

// Ø±Ù†Ø¯Ø±Ù Ø´Ø¨Ú©Ù‡â€ŒÛŒ Ø±ÙˆØ²Ù‡Ø§ Ø¨Ø±Ø§ÛŒ Ù…Ø§Ù‡Ù Ø¬Ø§Ø±ÛŒÙ Ù†Ù…Ø§ÛŒØ´
function dpRenderCalendar() {
    const mBtn = document.getElementById("dpMonthBtn");
    const yBtn = document.getElementById("dpYearBtn");
    if (mBtn) mBtn.textContent = PERSIAN_MONTHS[dpViewMonth - 1];
    if (yBtn) yBtn.textContent = toFaDigits(dpViewYear);

    const grid = document.getElementById("dpDays");
    if (!grid) return;
    grid.innerHTML = "";

    const lead = jalaliFirstDow(dpViewYear, dpViewMonth);
    const days = getJalaliMonthDays(dpViewYear, dpViewMonth);

    // Ø±ÙˆØ²Ù Ø§Ù…Ø±ÙˆØ² (Ø´Ù…Ø³ÛŒ) Ø¨Ø±Ø§ÛŒ Ù‡Ø§ÛŒÙ„Ø§ÛŒØª
    const now = new Date();
    const [tjy, tjm, tjd] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());

    const selJy = dpGet("jy"), selJm = dpGet("jm"), selJd = dpGet("jd");

    // Ø®Ø§Ù†Ù‡â€ŒÙ‡Ø§ÛŒ Ø®Ø§Ù„ÛŒÙ Ø§Ø¨ØªØ¯Ø§ÛŒ Ù…Ø§Ù‡
    for (let i = 0; i < lead; i++) {
        const sp = document.createElement("span");
        sp.className = "dp-day is-empty";
        grid.appendChild(sp);
    }
    for (let d = 1; d <= days; d++) {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "dp-day";
        btn.textContent = toFaDigits(d);
        if (dpViewYear === tjy && dpViewMonth === tjm && d === tjd) btn.classList.add("is-today");
        if (dpViewYear === selJy && dpViewMonth === selJm && d === selJd) btn.classList.add("is-selected");
        btn.addEventListener("click", () => dpSelectDay(d));
        grid.appendChild(btn);
    }
}

function dpSelectDay(d) {
    dpSet("jy", dpViewYear);
    dpSet("jm", dpViewMonth);
    dpSet("jd", d);
    dpRenderCalendar();
}

// Ø´Ø¨Ú©Ù‡â€ŒÛŒ Û±Û² Ù…Ø§Ù‡ Ø¨Ø±Ø§ÛŒ Ø§Ù†ØªØ®Ø§Ø¨Ù Ø³Ø±ÛŒØ¹
function dpRenderMonths() {
    const wrap = document.getElementById("dpMonthView");
    if (!wrap) return;
    wrap.innerHTML = "";
    const now = new Date();
    const [tjy, tjm] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
    for (let m = 1; m <= 12; m++) {
        const b = document.createElement("button");
        b.type = "button"; b.className = "dp-pick-item"; b.textContent = PERSIAN_MONTHS[m - 1];
        if (m === dpViewMonth) b.classList.add("is-selected");
        if (dpViewYear === tjy && m === tjm) b.classList.add("is-today");
        b.addEventListener("click", () => dpPickMonth(m));
        wrap.appendChild(b);
    }
}

// Ø´Ø¨Ú©Ù‡â€ŒÛŒ Ø³Ø§Ù„â€ŒÙ‡Ø§ (Û±Û° Ø³Ø§Ù„ Ù‚Ø¨Ù„ ØªØ§ Û±Û° Ø³Ø§Ù„ Ø¨Ø¹Ø¯Ù Ø³Ø§Ù„Ù Ù†Ù…Ø§ÛŒØ´)
function dpRenderYears() {
    const wrap = document.getElementById("dpYearView");
    if (!wrap) return;
    wrap.innerHTML = "";
    const now = new Date();
    const [tjy] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
    const start = dpViewYear - 10, end = dpViewYear + 10;
    for (let y = start; y <= end; y++) {
        const b = document.createElement("button");
        b.type = "button"; b.className = "dp-pick-item"; b.textContent = toFaDigits(y);
        if (y === dpViewYear) b.classList.add("is-selected");
        if (y === tjy) b.classList.add("is-today");
        b.addEventListener("click", () => dpPickYear(y));
        wrap.appendChild(b);
    }
}

function dpPickMonth(m) {
    dpViewMonth = m;
    dpView = 'days'; dpApplyView();
    dpRenderCalendar();
}
function dpPickYear(y) {
    dpViewYear = y;
    dpView = 'months'; dpApplyView();   // Ù¾Ø³ Ø§Ø² Ø³Ø§Ù„ØŒ Ø¨Ù‡ Ø§Ù†ØªØ®Ø§Ø¨Ù Ù…Ø§Ù‡ Ø¨Ø±Ùˆ
    dpRenderCalendar();
}

function dpChangeMonth(dir) {
    // dir=+1 ÛŒØ¹Ù†ÛŒ Ù…Ø§Ù‡Ù Ø¨Ø¹Ø¯ØŒ dir=-1 ÛŒØ¹Ù†ÛŒ Ù…Ø§Ù‡Ù Ù‚Ø¨Ù„
    dpViewMonth += dir;
    if (dpViewMonth > 12) { dpViewMonth = 1; dpViewYear++; }
    else if (dpViewMonth < 1) { dpViewMonth = 12; dpViewYear--; }
    dpRenderCalendar();
}

function dpRenderTime() {
    document.getElementById("dpHourVal").value = toFaDigits(pad2(dpGet("hh")));
    document.getElementById("dpMinuteVal").value = toFaDigits(pad2(dpGet("mm")));
}

function dpStepTime(unit, dir) {
    let v = dpGet(unit) + dir;
    const max = unit === "hh" ? 24 : 60;
    v = (v + max) % max;            // Ú†Ø±Ø®Ø´ÛŒ
    dpSet(unit, v);
    dpRenderTime();
}

function openDatePicker() {
    dpView = 'days'; dpApplyView();   // Ù‡Ù…ÛŒØ´Ù‡ Ø¨Ø§ Ù†Ù…Ø§ÛŒ Ø±ÙˆØ² Ø¨Ø§Ø² Ø´ÙˆØ¯
    document.getElementById("dateModal").classList.add("show");
}

function closeDatePicker() {
    document.getElementById("dateModal").classList.remove("show");
}

function setPickerNow() {
    const now = new Date();
    const [jy, jm, jd] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
    setPickerValues(jy, jm, jd, now.getHours(), now.getMinutes());
}

function applyDatePicker() {
    const jy = dpGet("jy"), jm = dpGet("jm"), jd = dpGet("jd");
    const hh = dpGet("hh"), mm = dpGet("mm");
    if (!jy || !jm || !jd) { closeDatePicker(); return; }
    const [gy, gm, gd] = jalaliToGregorian(jy, jm, jd);
    document.getElementById("publish_date").value = `${gy}-${pad2(gm)}-${pad2(gd)} ${pad2(hh)}:${pad2(mm)}:00`;
    renderPublishDisplay(jy, jm, jd, hh, mm);
    closeDatePicker();
}

function initDatePicker() {
    const now = new Date();
    const savedGregorian = `<?= $pub_val ? htmlspecialchars(str_replace('T', ' ', $pub_val) . ':00') : '' ?>`;
    if (savedGregorian) {
        const parsed = new Date(savedGregorian.replace(' ', 'T'));
        setDateFromGregorianDate(Number.isNaN(parsed.getTime()) ? now : parsed);
    } else {
        setDateFromGregorianDate(now);
    }
}

/* ===== Ø§Ø±ØªÙ‚Ø§ÛŒ <select> Ø¨Ù‡ Ø¯Ø±Ø§Ù¾â€ŒØ¯Ø§ÙˆÙ† Ø³ÙØ§Ø±Ø´ÛŒ Ù…Ø¯Ø±Ù† ===== */
const MSEL_CARET = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
const MSEL_CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

function enhanceSelect(select){
    if (!select || select.dataset.enhanced) return;
    select.dataset.enhanced = "1";

    const placeholderText = (select.options[0] && select.options[0].value === "") ? select.options[0].textContent : "";

    const wrap = document.createElement("div");
    wrap.className = "msel";

    const trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "msel-trigger";
    trigger.innerHTML = '<span class="msel-value"></span><span class="msel-caret">' + MSEL_CARET + '</span>';

    const menu = document.createElement("div");
    menu.className = "msel-menu";
    menu.setAttribute("role", "listbox");

    // Ø³Ø§Ø®ØªÙ Ú¯Ø²ÛŒÙ†Ù‡â€ŒÙ‡Ø§ Ø§Ø² Ø±ÙˆÛŒ <option>Ù‡Ø§
    Array.from(select.options).forEach(opt => {
        if (opt.value === "" && placeholderText) return; // placeholder Ø±Ø§ Ú¯Ø²ÛŒÙ†Ù‡ Ù†Ú©Ù†
        const o = document.createElement("div");
        o.className = "msel-opt";
        o.setAttribute("role", "option");
        o.dataset.value = opt.value;
        o.innerHTML = '<span>' + opt.textContent + '</span><span class="msel-check">' + MSEL_CHECK + '</span>';
        o.addEventListener("click", () => {
            select.value = opt.value;
            select.dispatchEvent(new Event("change", { bubbles: true }));
            syncMsel();
            closeMenu();
        });
        menu.appendChild(o);
    });

    // Ù…Ø®ÙÛŒâ€ŒÚ©Ø±Ø¯Ù†Ù select Ø¨ÙˆÙ…ÛŒ Ùˆ Ø¯Ø±Ø¬ Ú©Ø§Ù…Ù¾ÙˆÙ†Ù†Øª
    select.style.display = "none";
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(trigger);
    wrap.appendChild(menu);
    wrap.appendChild(select); // select Ø¯Ø§Ø®Ù„Ù wrap Ø¨Ù…Ø§Ù†Ø¯ ØªØ§ fieldÙ‡Ø§ Ø³Ø§Ù„Ù… Ø¨Ù…Ø§Ù†Ù†Ø¯

    function syncMsel(){
        const valEl = trigger.querySelector(".msel-value");
        const sel = select.options[select.selectedIndex];
        const isPlaceholder = !select.value && placeholderText;
        valEl.textContent = isPlaceholder ? placeholderText : (sel ? sel.textContent : "");
        trigger.classList.toggle("is-placeholder", !!isPlaceholder);
        menu.querySelectorAll(".msel-opt").forEach(o => {
            o.classList.toggle("is-selected", o.dataset.value === select.value);
        });
        // Ø§Ù†ØªÙ‚Ø§Ù„Ù Ø­Ø§Ù„ØªÙ Ø®Ø·Ø§ Ø§Ø² select Ø¨Ù‡ wrap
        wrap.classList.toggle("field-invalid", select.classList.contains("field-invalid"));
    }

    function openMenu(){ wrap.classList.add("open"); }
    function closeMenu(){ wrap.classList.remove("open"); }

    trigger.addEventListener("click", (e) => {
        e.stopPropagation();
        // Ø¨Ø³ØªÙ†Ù Ø³Ø§ÛŒØ± Ø¯Ø±Ø§Ù¾â€ŒØ¯Ø§ÙˆÙ†â€ŒÙ‡Ø§ÛŒ Ø¨Ø§Ø²
        document.querySelectorAll(".msel.open").forEach(m => { if (m !== wrap) m.classList.remove("open"); });
        wrap.classList.toggle("open");
    });
    // ÙˆÙ‚ØªÛŒ Ø®Ø·Ø§ÛŒ ÙÛŒÙ„Ø¯ Ù¾Ø§Ú©/Ø³Øª Ù…ÛŒâ€ŒØ´ÙˆØ¯ØŒ Ø¸Ø§Ù‡Ø±Ù Ø¯Ø±Ø§Ù¾â€ŒØ¯Ø§ÙˆÙ† Ø±Ø§ Ù‡Ù…Ú¯Ø§Ù… Ú©Ù†
    select.addEventListener("change", syncMsel);
    select._mselSync = syncMsel;

    syncMsel();
}

// Ø¨Ø³ØªÙ†Ù Ø¯Ø±Ø§Ù¾â€ŒØ¯Ø§ÙˆÙ†â€ŒÙ‡Ø§ Ø¨Ø§ Ú©Ù„ÛŒÚ© Ø¨ÛŒØ±ÙˆÙ† ÛŒØ§ Esc
document.addEventListener("click", () => document.querySelectorAll(".msel.open").forEach(m => m.classList.remove("open")));
document.addEventListener("keydown", (e) => { if (e.key === "Escape") document.querySelectorAll(".msel.open").forEach(m => m.classList.remove("open")); });

document.addEventListener("DOMContentLoaded", () => {
    initDatePicker();
    updateTitleCounter();
    updateSubtitleCounter();
    renderTagChips();
    initTinyMCEEditor();
    enhanceSelect(document.getElementById("category_id"));
});
/* ======================== */

/* =================== Ø±Ø§Ù‡â€ŒØ§Ù†Ø¯Ø§Ø²ÛŒ Ø§Ø¯ÛŒØªÙˆØ± Ù¾ÛŒØ´Ø±ÙØªÙ‡ TinyMCE 7 =================== */
function initTinyMCEEditor() {
    if (typeof tinymce === 'undefined') {
        console.error("TinyMCE not loaded!");
        return;
    }

    tinymce.init({
        selector: '#editor',
        directionality: 'rtl',
        language: 'fa',
        language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24.7.29/langs/fa.js',
        min_height: 520,
        max_height: 850,
        autoresize_bottom_margin: 20,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'directionality'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | ltr rtl | bullist numlist outdent indent | table image media link | removeformat fullscreen code',
        content_style: `
            @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700;800&display=swap');
            body {
                font-family: 'Vazirmatn', Tahoma, sans-serif;
                font-size: 16px;
                line-height: 1.95;
                color: #333333;
                direction: rtl;
                text-align: right;
                padding: 16px;
                margin: 0;
            }
            body[data-theme="dark"] {
                background-color: #1e1e1e;
                color: #e0e0e0;
            }
            img { max-width: 100%; height: auto; border-radius: 10px; display: block; margin: 18px auto; box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
            figure { display: table; margin: 20px auto; max-width: 100%; text-align: center; }
            figure img { margin: 0 auto; }
            figcaption { font-size: 13.5px; color: #666; margin-top: 8px; font-style: italic; font-weight: 600; }
            table { border-collapse: collapse; width: 100%; margin: 20px 0; font-size: 15px; }
            table th, table td { border: 1px solid #e2e8f0; padding: 10px 14px; text-align: right; }
            table th { background: #f8fafc; font-weight: 700; color: #007D75; }
            blockquote { border-right: 4px solid #007D75; margin: 20px 0; padding: 14px 20px; background: rgba(0,125,117,0.05); border-radius: 0 10px 10px 0; font-style: italic; font-size: 16.5px; line-height: 2; }
            iframe { max-width: 100%; border-radius: 12px; margin: 18px auto; display: block; }
        `,
        image_title: true,
        automatic_uploads: true,
        images_upload_url: '/dashboard/upload-inline-image.php',
        images_upload_handler: function (blobInfo, progress) {
            return new Promise(function (resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '/dashboard/upload-inline-image.php');

                xhr.upload.onprogress = function (e) {
                    if (e.lengthComputable) {
                        progress(e.loaded / e.total * 100);
                    }
                };

                xhr.onload = function () {
                    if (xhr.status < 200 || xhr.status >= 300) {
                        reject('Ø®Ø·Ø§ Ø¯Ø± Ø¢Ù¾Ù„ÙˆØ¯ ØªØµÙˆÛŒØ±: HTTP ' + xhr.status);
                        return;
                    }
                    var json;
                    try {
                        json = JSON.parse(xhr.responseText);
                    } catch (err) {
                        reject('Ù¾Ø§Ø³Ø® Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø³Ø±ÙˆØ±: ' + xhr.responseText);
                        return;
                    }
                    if (!json || typeof json.location !== 'string') {
                        reject(json && json.message ? json.message : 'Ù¾Ø§Ø³Ø® Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø² Ø³Ø±ÙˆØ±.');
                        return;
                    }
                    resolve(json.location);
                };

                xhr.onerror = function () {
                    reject('Ø®Ø·Ø§ÛŒ Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ± Ø¯Ø± Ù‡Ù†Ú¯Ø§Ù… Ø¢Ù¾Ù„ÙˆØ¯ ØªØµÙˆÛŒØ±.');
                };

                var formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            });
        },
        setup: function (editor) {
            editor.on('init', function () {
                updateSeoWidget();
            });
            editor.on('change input keyup NodeChange SetContent', function () {
                editor.save();
                updateSeoWidget();
                setFieldError("editorShell", "contentError", false);
            });
        }
    });
}

function getEditorHTML() {
    if (window.tinymce && tinymce.get("editor")) {
        tinymce.get("editor").save();
        return tinymce.get("editor").getContent();
    }
    const el = document.getElementById("editor");
    return el ? el.value : "";
}

function getEditorText() {
    if (window.tinymce && tinymce.get("editor")) {
        return tinymce.get("editor").getContent({ format: "text" });
    }
    const el = document.getElementById("editor");
    return el ? (el.value || el.innerText || "") : "";
}

function uploadSingleImage() {
    if (window.tinymce && tinymce.get("editor")) {
        tinymce.get("editor").execCommand('mceImage');
    }
}

function uploadGalleryInline() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.multiple = true;
    input.onchange = async function () {
        if (!this.files || this.files.length === 0) return;
        const files = Array.from(this.files).slice(0, 6);
        showStatus("Ø¯Ø± Ø­Ø§Ù„ Ø¢Ù¾Ù„ÙˆØ¯ Ú¯Ø§Ù„Ø±ÛŒ ØªØµØ§ÙˆÛŒØ±...", true);
        const imgUrls = [];
        for (const file of files) {
            const fd = new FormData();
            fd.append('file', file);
            try {
                const res = await fetch('/dashboard/upload-inline-image.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data && data.location) imgUrls.push(data.location);
            } catch(e) {}
        }
        if (imgUrls.length > 0) {
            const galleryHtml = `
                <div class="news-gallery" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin: 24px 0;">
                    ${imgUrls.map((url, i) => `<figure style="margin:0;"><img src="${url}" alt="ØªØµÙˆÛŒØ± ${i+1}" style="width:100%;height:auto;border-radius:10px;"><figcaption style="text-align:center;font-size:12.5px;color:#666;margin-top:4px;">ØªØµÙˆÛŒØ± ${i+1}</figcaption></figure>`).join('')}
                </div><p></p>
            `;
            if (window.tinymce && tinymce.get("editor")) {
                tinymce.get("editor").insertContent(galleryHtml);
                tinymce.get("editor").save();
                updateSeoWidget();
            }
            showStatus("Ú¯Ø§Ù„Ø±ÛŒ ØªØµØ§ÙˆÛŒØ± Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª Ø¯Ø± Ù…ØªÙ† Ø¯Ø±Ø¬ Ø´Ø¯.", true);
        }
    };
    input.click();
}

/* ====== ÙØ´Ø±Ø¯Ù‡â€ŒØ³Ø§Ø²ÛŒ Ù‡ÙˆØ´Ù…Ù†Ø¯ ØªØµÙˆÛŒØ± Ø´Ø§Ø®Øµ (Ø­ÙØ¸ Ú©ÛŒÙÛŒØªØŒ Ú©Ø§Ù‡Ø´ Ø­Ø¬Ù…) ====== */
// ÙÙ‚Ø· Ø¯Ø± ØµÙˆØ±Øª Ù†ÛŒØ§Ø² ÙØ´Ø±Ø¯Ù‡ Ù…ÛŒâ€ŒÚ©Ù†Ø¯: Ø§Ú¯Ø± ÙØ§ÛŒÙ„ Ú©ÙˆÚ†Ú© Ø¨Ø§Ø´Ø¯ Ø¯Ø³Øªâ€ŒÙ†Ø®ÙˆØ±Ø¯Ù‡ Ø¨Ø±Ù…ÛŒâ€ŒÚ¯Ø±Ø¯Ø¯.
async function compressFeaturedImage(file) {
    const MAX_DIMENSION = 1920;   // Ø­Ø¯Ø§Ú©Ø«Ø± Ø¹Ø±Ø¶/Ø§Ø±ØªÙØ§Ø¹
    const QUALITY = 0.85;         // Ú©ÛŒÙÛŒØª Ø¨Ø§Ù„Ø§
    const SIZE_THRESHOLD = 1.5 * 1024 * 1024; // Ø²ÛŒØ± Û±.Ûµ Ù…Ú¯ Ù†ÛŒØ§Ø²ÛŒ Ø¨Ù‡ ÙØ´Ø±Ø¯Ù‡â€ŒØ³Ø§Ø²ÛŒ Ù†ÛŒØ³Øª

    // ÙØ§ÛŒÙ„â€ŒÙ‡Ø§ÛŒ ØºÛŒØ±ØªØµÙˆÛŒØ±ÛŒ ÛŒØ§ PNG Ø´ÙØ§Ù Ø±Ø§ Ø¯Ø³Øªâ€ŒÙ†Ø®ÙˆØ±Ø¯Ù‡ Ø¨Ø±Ù…ÛŒâ€ŒÚ¯Ø±Ø¯Ø§Ù†ÛŒÙ… ØªØ§ Ú©ÛŒÙÛŒØª/Ø´ÙØ§ÙÛŒØª Ø­ÙØ¸ Ø´ÙˆØ¯
    if (!file.type.startsWith("image/")) return file;
    if (file.size <= SIZE_THRESHOLD) return file;

    try {
        const dataUrl = await new Promise((res, rej) => {
            const r = new FileReader();
            r.onload = () => res(r.result);
            r.onerror = rej;
            r.readAsDataURL(file);
        });

        const img = await new Promise((res, rej) => {
            const i = new Image();
            i.onload = () => res(i);
            i.onerror = rej;
            i.src = dataUrl;
        });

        let { width, height } = img;
        if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
            const scale = MAX_DIMENSION / Math.max(width, height);
            width = Math.round(width * scale);
            height = Math.round(height * scale);
        }

        const canvas = document.createElement("canvas");
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, width, height);

        const blob = await new Promise(res => canvas.toBlob(res, "image/jpeg", QUALITY));
        if (!blob || blob.size >= file.size) return file; // Ø§Ú¯Ø± ÙØ´Ø±Ø¯Ù‡â€ŒØ³Ø§Ø²ÛŒ Ø³ÙˆØ¯ Ù†Ø¯Ø§Ø´ØªØŒ Ø§ØµÙ„ Ø±Ø§ Ù†Ú¯Ù‡â€ŒØ¯Ø§Ø±

        const newName = file.name.replace(/\.[^.]+$/, "") + ".jpg";
        return new File([blob], newName, { type: "image/jpeg" });
    } catch (e) {
        console.warn("Image compression skipped:", e);
        return file; // Ø¯Ø± ØµÙˆØ±Øª Ø®Ø·Ø§ØŒ ÙØ§ÛŒÙ„ Ø§ØµÙ„ÛŒ Ø§Ø±Ø³Ø§Ù„ Ù…ÛŒâ€ŒØ´ÙˆØ¯
    }
}

/* ====== Ú©Ù†ØªØ±Ù„ Ù†ÙˆØ§Ø± Ù¾ÛŒØ´Ø±ÙØª Ø¢Ù¾Ù„ÙˆØ¯ (Ø¯Ø§Ø®Ù„ ØªÙˆØ³Øª) ====== */
function setUploadProgress(percent, label) {
    document.getElementById("uploadProgress").classList.add("active");
    document.getElementById("uploadBarFill").style.width = percent + "%";
    // Ù…ØªÙ†Ù Ù¾ÛŒØ´Ø±ÙØª Ø¯Ø± Ù‡Ù…Ø§Ù† ØªÙˆØ³ØªÙ Â«Ø¯Ø± Ø­Ø§Ù„ Ø§Ù†Ø¬Ø§Ù…Â» Ù†Ù…Ø§ÛŒØ´ Ø¯Ø§Ø¯Ù‡ Ù…ÛŒâ€ŒØ´ÙˆØ¯
    if (label) showStatus(label, "info");
}
function hideUploadProgress() {
    document.getElementById("uploadProgress").classList.remove("active");
    document.getElementById("uploadBarFill").style.width = "0%";
}

/* =================== Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ ÙÛŒÙ„Ø¯Ù‡Ø§ (Ø¹Ù„Ø§Ù…Øªâ€ŒÚ¯Ø°Ø§Ø±ÛŒ Ø¨ØµØ±ÛŒ) =================== */
function setFieldError(boxId, errId, on){
    const box = document.getElementById(boxId);
    const err = document.getElementById(errId);
    if (box) box.classList.toggle("field-invalid", on);
    if (err) err.classList.toggle("show", on);
    const grp = box ? box.closest(".input-group") : null;
    if (grp) grp.classList.toggle("has-error", on);
    if (box && box._mselSync) box._mselSync();
}

/* Ø¨Ø§ Ø§ÙˆÙ„ÛŒÙ† ØªØ¹Ø§Ù…Ù„Ù Ú©Ø§Ø±Ø¨Ø±ØŒ Ø®Ø·Ø§ÛŒ Ù‡Ù…Ø§Ù† ÙÛŒÙ„Ø¯ Ù¾Ø§Ú© Ù…ÛŒâ€ŒØ´ÙˆØ¯ */
document.getElementById("title").addEventListener("input", () => setFieldError("titleCard", "titleError", false));
document.getElementById("category_id").addEventListener("change", () => setFieldError("category_id", "categoryError", false));

function validateNewsForm(){
    if (window.tinymce && tinymce.get("editor")) {
        tinymce.get("editor").save();
    }
    const title = document.getElementById("title").value.trim();
    const contentText = getEditorText().trim();
    const categoryVal = document.getElementById("category_id").value;

    const titleBad = !title;
    const contentBad = contentText === "";
    const categoryBad = !categoryVal;

    setFieldError("titleCard", "titleError", titleBad);
    setFieldError("editorShell", "contentError", contentBad);
    setFieldError("category_id", "categoryError", categoryBad);

    let firstBad = null;
    if (titleBad) firstBad = document.getElementById("title");
    else if (contentBad && window.tinymce && tinymce.get("editor")) firstBad = tinymce.get("editor").getContainer();
    else if (categoryBad) firstBad = document.getElementById("category_id");

    if (firstBad) {
        firstBad.scrollIntoView({ behavior: "smooth", block: "center" });
    }

    return !(titleBad || contentBad || categoryBad);
}

async function saveNews() {
    if (window.tinymce && tinymce.get("editor")) {
        tinymce.get("editor").save();
    }
    const title = document.getElementById("title").value.trim();
    const content = getEditorHTML().trim();

    if (!validateNewsForm()) {
        showStatus("Ù„Ø·ÙØ§Ù‹ ÙÛŒÙ„Ø¯Ù‡Ø§ÛŒ Ù…Ø´Ø®Øµâ€ŒØ´Ø¯Ù‡ Ø±Ø§ Ù¾Ø± Ú©Ù†ÛŒØ¯.", false);
        return;
    }

    const saveBtn = document.querySelector('.btn-save');
    const originalBtnText = saveBtn.innerHTML;

    try {
        saveBtn.disabled = true;
        saveBtn.innerHTML = 'Ø¯Ø± Ø­Ø§Ù„ Ø°Ø®ÛŒØ±Ù‡...';
        showStatus("â³ Ø¯Ø± Ø­Ø§Ù„ Ø¢Ù…Ø§Ø¯Ù‡â€ŒØ³Ø§Ø²ÛŒ Ø§Ø·Ù„Ø§Ø¹Ø§Øª...", true);

        const fd = new FormData();
        fd.append("id", "<?= $id ?>");
        fd.append("title", title);
        fd.append("subtitle", document.getElementById("subtitle").value.trim());
        fd.append("content", content);
        fd.append("author", document.getElementById("author").value);
        fd.append("category_id", document.getElementById("category_id").value);
        fd.append("keywords", document.getElementById("keywords").value);
        fd.append("publish_date", document.getElementById("publish_date").value);
        fd.append("tags", document.getElementById("tags").value);
        fd.append("remove_featured_flag", document.getElementById("remove_featured_flag").value);

        const checkedTags = document.querySelectorAll('input[name="tag_ids[]"]:checked');
        checkedTags.forEach(cb => {
            fd.append("tag_ids[]", cb.value);
        });

        let featuredFile = document.getElementById("featured_image").files[0];
        if (featuredFile) {
            showStatus("ðŸ—œï¸ Ø¯Ø± Ø­Ø§Ù„ Ø¨Ù‡ÛŒÙ†Ù‡â€ŒØ³Ø§Ø²ÛŒ ØªØµÙˆÛŒØ±...", true);
            featuredFile = await compressFeaturedImage(featuredFile);
            fd.append("featured_image", featuredFile);
        }

        const data = await new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "news-save.php", true);
            xhr.setRequestHeader("Accept", "application/json");

            if (featuredFile) {
                showStatus("â« Ø¯Ø± Ø­Ø§Ù„ Ø¢Ù¾Ù„ÙˆØ¯ Ø¨Ù‡ Ø³Ø±ÙˆØ±...", true);
                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const pct = Math.round((e.loaded / e.total) * 100);
                        setUploadProgress(pct, `Ø¯Ø± Ø­Ø§Ù„ Ø¢Ù¾Ù„ÙˆØ¯... ${pct}%`);
                    }
                };
                xhr.upload.onload = () => setUploadProgress(100, "Ø¢Ù¾Ù„ÙˆØ¯ Ú©Ø§Ù…Ù„ Ø´Ø¯ØŒ Ø¯Ø± Ø­Ø§Ù„ Ù¾Ø±Ø¯Ø§Ø²Ø´...");
            }

            xhr.onload = () => {
                let parsed;
                try { parsed = JSON.parse(xhr.responseText); }
                catch (_) { return reject(new Error("Ù¾Ø§Ø³Ø® Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø² Ø³Ø±ÙˆØ± Ø¯Ø±ÛŒØ§ÙØª Ø´Ø¯.")); }
                if (xhr.status >= 200 && xhr.status < 300 && parsed.status === "success") {
                    resolve(parsed);
                } else {
                    reject(new Error(parsed.message || "Ø®Ø·Ø§ÛŒÛŒ Ø¯Ø± Ù¾Ø±Ø¯Ø§Ø²Ø´ Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø±Ø® Ø¯Ø§Ø¯."));
                }
            };
            xhr.onerror = () => reject(new Error("Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ± Ø¨Ø±Ù‚Ø±Ø§Ø± Ù†Ø´Ø¯."));
            xhr.send(fd);
        });

        setUploadProgress(100, "âœ… Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª Ø°Ø®ÛŒØ±Ù‡ Ø´Ø¯");
        showStatus(`âœ… ${data.message} Ø¯Ø± Ø­Ø§Ù„ Ø§Ù†ØªÙ‚Ø§Ù„...`, true);
        setTimeout(() => {
            window.location.href = "news-list.php";
        }, 1000);

    } catch (err) {
        console.error("Save Error:", err);
        hideUploadProgress();
        showStatus("âŒ Ø®Ø·Ø§ÛŒÛŒ Ø±Ø® Ø¯Ø§Ø¯: " + err.message, false);
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    }
}

function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function openPreview(){
    const title = document.getElementById("title").value.trim();
    const author = document.getElementById("author").value.trim();
    const date = document.getElementById("publish_date_display").value.trim();
    const content = getEditorHTML();
    const catSelect = document.getElementById("category_id");
    const category = catSelect.value ? catSelect.options[catSelect.selectedIndex].text.trim() : "";
    const featured = featuredInput.files[0]
        ? URL.createObjectURL(featuredInput.files[0])
        : (dzImg.getAttribute("src") || "");

    const contentHasText = getEditorText().trim().length > 0;
    const titleSafe = escapeHtml(title || "Ø¨Ø¯ÙˆÙ† Ø¹Ù†ÙˆØ§Ù†");

    const icAuthor = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
    const icDate = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';

    let heroHtml;
    if (featured) {
        heroHtml = `
            <div class="pv-hero">
                ${category ? `<span class="pv-hero-cat">${escapeHtml(category)}</span>` : ""}
                <img src="${featured}" alt="ØªØµÙˆÛŒØ± Ø´Ø§Ø®Øµ">
                <h1 class="pv-hero-title">${titleSafe}</h1>
            </div>`;
    } else {
        heroHtml = `<h1 class="pv-title-fallback">${titleSafe}</h1>`;
    }

    const metaChips = [];
    if (author) metaChips.push(`<span class="chip">${icAuthor}${escapeHtml(author)}</span>`);
    if (date) metaChips.push(`<span class="chip">${icDate}${escapeHtml(date)}</span>`);
    if (category && !featured) metaChips.push(`<span class="chip">${escapeHtml(category)}</span>`);

    const html = `
        <div class="pv-article">
            ${heroHtml}
            ${metaChips.length ? `<div class="pv-meta">${metaChips.join("")}</div>` : ""}
            <div class="pv-body">
                ${contentHasText ? content : '<p class="pv-empty">Ù‡Ù†ÙˆØ² Ù…ØªÙ†ÛŒ Ø¨Ø±Ø§ÛŒ Ø§ÛŒÙ† Ø®Ø¨Ø± Ù†ÙˆØ´ØªÙ‡ Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª.</p>'}
            </div>
            <div id="seoScoreBox" class="pv-seo"></div>
        </div>
    `;

    document.getElementById("previewContent").innerHTML = html;
    calculateSEO();
    const modal = document.getElementById("previewModal");
    modal.style.display = "block";
    requestAnimationFrame(() => modal.classList.add("show"));
    document.querySelector(".preview-scroll").scrollTop = 0;
}

function closePreview(){
    const modal = document.getElementById("previewModal");
    modal.classList.remove("show");
    setTimeout(() => {
        modal.style.display = "none";
    }, 260);
}

/* Ù…Ø­Ø§Ø³Ø¨Ù‡ Ø§Ù…ØªÛŒØ§Ø² Ø³Ø¦Ùˆ (Ù…Ø´ØªØ±Ú© Ø¨ÛŒÙ† ÙˆÛŒØ¬Øª Ùˆ Ù¾ÛŒØ´â€ŒÙ†Ù…Ø§ÛŒØ´) */
function computeSeoScore(){
    let score = 0;
    const title = document.getElementById("title").value.trim();
    const contentText = getEditorText().trim();
    const contentHtml = getEditorHTML();
    const wordCount = contentText ? contentText.split(/\s+/).length : 0;
    const hasImage = featuredInput.files[0] || dzPreview.classList.contains("show") || contentHtml.includes("<img");
    const keywords = document.getElementById("keywords").value.trim();
    const hasH2 = contentHtml.includes("<h2");

    if(title.length > 5) score += 20;
    if(wordCount > 300) score += 25;
    if(hasImage) score += 15;
    if(keywords.length > 3) score += 20;
    if(hasH2) score += 20;
    return score;
}

/* Ø¨Ù‡â€ŒØ±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ ÙˆÛŒØ¬Øª Ø­Ù„Ù‚Ù‡â€ŒØ§ÛŒ Ø§Ù…ØªÛŒØ§Ø² Ø³Ø¦Ùˆ Ø¯Ø± Ø³ØªÙˆÙ† Ú©Ù†Ø§Ø±ÛŒ */
function updateSeoWidget(){
    const score = computeSeoScore();
    const fill = document.getElementById("seoRingFill");
    const num = document.getElementById("seoRingNum");
    const stateEl = document.getElementById("seoState");
    const tipEl = document.getElementById("seoTip");

    let color = "#e74c3c", label = "Ø¶Ø¹ÛŒÙ", tip = "Ø¹Ù†ÙˆØ§Ù† Ùˆ Ù…ØªÙ† Ø±Ø§ Ú©Ø§Ù…Ù„â€ŒØªØ± Ú©Ù†ÛŒØ¯.";
    if (score >= 70) { color = "#00b894"; label = "Ø®ÙˆØ¨"; tip = "Ù…Ø­ØªÙˆØ§ÛŒ Ø´Ù…Ø§ Ø¨Ø±Ø§ÛŒ Ø³Ø¦Ùˆ Ù…Ù†Ø§Ø³Ø¨ Ø§Ø³Øª."; }
    else if (score >= 40) { color = "#F79F1F"; label = "Ù…ØªÙˆØ³Ø·"; tip = "ØªØµÙˆÛŒØ±ØŒ Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ ÛŒØ§ ØªÛŒØªØ± H2 Ø§Ø¶Ø§ÙÙ‡ Ú©Ù†ÛŒØ¯."; }

    if (fill) {
        fill.setAttribute("stroke-dasharray", score + ", 100");
        fill.setAttribute("stroke", color);
    }
    if (num) num.textContent = toFaDigits(score);
    if (stateEl) {
        stateEl.textContent = label;
        stateEl.style.color = color;
    }
    if (tipEl) tipEl.textContent = tip;
}

/* Ø¨Ù‡â€ŒØ±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ Ø²Ù†Ø¯Ù‡ ÙˆÛŒØ¬Øª Ø¨Ø§ ØªØºÛŒÛŒØ± ÙˆØ±ÙˆØ¯ÛŒâ€ŒÙ‡Ø§ */
["title","keywords"].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener("input", updateSeoWidget);
});
if (featuredInput) featuredInput.addEventListener("change", updateSeoWidget);

function calculateSEO(){
    const score = computeSeoScore();
    const contentHtml = getEditorHTML();

    let color = "#e74c3c", label = "Ø¶Ø¹ÛŒÙ";
    if(score >= 70) { color = "#00b894"; label = "Ø®ÙˆØ¨"; }
    else if(score >= 40) { color = "#F79F1F"; label = "Ù…ØªÙˆØ³Ø·"; }

    const title = document.getElementById("title").value.trim();
    const wordCount = getEditorText().trim() ? getEditorText().trim().split(/\s+/).length : 0;
    const hasImage = featuredInput.files[0] || dzPreview.classList.contains("show") || contentHtml.includes("<img");
    const keywords = document.getElementById("keywords").value.trim();
    const hasH2 = contentHtml.includes("<h2");

    const checks = [
        { ok: title.length > 5,   text: "Ø¹Ù†ÙˆØ§Ù† Ù…Ù†Ø§Ø³Ø¨" },
        { ok: wordCount > 300,    text: "Ø·ÙˆÙ„ Ù…ØªÙ† Ú©Ø§ÙÛŒ" },
        { ok: !!hasImage,         text: "ØªØµÙˆÛŒØ± Ø´Ø§Ø®Øµ ÛŒØ§ Ø¯Ø±ÙˆÙ†â€ŒÙ…ØªÙ†ÛŒ" },
        { ok: keywords.length > 3,text: "Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ" },
        { ok: hasH2,              text: "ØªÛŒØªØ± H2 Ø¯Ø± Ù…ØªÙ†" },
    ];

    const icOk = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    const icNo = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="9" y1="12" x2="15" y2="12"/></svg>';

    const checksHtml = checks.map(c =>
        `<div class="pv-check ${c.ok ? "ok" : "no"}">${c.ok ? icOk : icNo}<span>${c.text}</span></div>`
    ).join("");

    const seoBox = document.getElementById("seoScoreBox");
    if (seoBox) {
        seoBox.innerHTML = `
            <div class="pv-seo-head">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Ø§Ù…ØªÛŒØ§Ø² Ø³Ø¦Ùˆ
                </h3>
                <span class="pv-seo-badge" style="background:${color}">${label}</span>
            </div>
            <div class="pv-seo-bar-track">
                <div class="pv-seo-bar-fill" style="width:${score}%;background:${color}"></div>
            </div>
            <div class="pv-seo-foot">
                <span>Ø§Ù…ØªÛŒØ§Ø² Ú©Ù„ÛŒ</span>
                <span><b>${toFaDigits(score)}</b> Ø§Ø² ${toFaDigits(100)}</span>
            </div>
            <div class="pv-checks">${checksHtml}</div>
        `;
    }
}

/* =================== Ø³ÛŒØ³ØªÙ… Ù‡ÙˆØ´Ù…Ù†Ø¯ Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯ Ø¨Ø±Ú†Ø³Ø¨ Ùˆ Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ =================== */
const PERSIAN_STOPWORDS = new Set([
    "Ø§Ø²", "Ø¨Ù‡", "Ø¨Ø§", "Ú©Ù‡", "Ø¯Ø±", "Ø±Ø§", "Ùˆ", "Ø§ÛŒÙ†", "Ø¢Ù†", "Ø¨Ø±Ø§ÛŒ", "Ù…Ø§", "Ø´Ù…Ø§", "Ø¢Ù†Ù‡Ø§", "Ø§Ùˆ", "Ù…Ù†", "ØªÙˆ",
    "Ø§Ø³Øª", "Ù‡Ø³Øª", "Ø¨ÙˆØ¯", "Ø´Ø¯", "ÛŒÚ©", "Ø¯Ùˆ", "Ø³Ù‡", "Ú©Ø§Ø±", "Ø®ÙˆØ¯", "Ù‡Ù…", "Ø±ÙˆÛŒ", "ØªØ§", "Ú©Ù†Ø¯", "Ú©Ù†Ù†Ø¯", "Ú©Ø±Ø¯",
    "Ú©Ø±Ø¯Ù†", "Ø¨ÙˆØ¯Ù†", "Ø´Ø¯Ù†", "Ø¯Ø§Ø´Øª", "Ø¯Ø§Ø´ØªÙ†", "Ø¯Ù‡Ø¯", "Ø¯Ù‡Ù†Ø¯", "Ù¾Ø³", "Ø§Ù…Ø§", "Ø§Ú¯Ø±", "Ø­ØªÛŒ", "Ø¯Ø±Ø¨Ø§Ø±Ù‡", "ØªØ­Øª",
    "Ù…ÙˆØ±Ø¯", "Ø¯ÛŒÚ¯Ø±", "Ø¨Ø³ÛŒØ§Ø±", "Ø®ÛŒÙ„ÛŒ", "Ø¨Ø§ÛŒØ¯", "Ø´Ø§ÛŒØ¯", "ØªÙˆØ§Ù†Ø¯", "ØªÙˆØ§Ù†Ù†Ø¯", "Ù‡Ù…ÛŒÙ†", "Ù‡Ù…Ø§Ù†", "Ù¾ÛŒØ´", "Ø¨Ø¹Ø¯",
    "Ù‚Ø¨Ù„", "Ø·Ø±ÛŒÙ‚", "Ù…Ø®ØªÙ„Ù", "Ø¨Ø®Ø´", "Ø¨Ø®Ø´â€ŒÙ‡Ø§ÛŒ", "Ù…Ø±Ø§Ø­Ù„", "ØªØ¹Ø¯Ø§Ø¯", "ÛŒÚ©ÛŒ", "Ø¨Ø±Ø®ÛŒ", "ØªÙ…Ø§Ù…", "Ù‡Ù…Ù‡", "Ù‡ÛŒÚ†",
    "Ø¨Ø¯ÙˆÙ†", "Ø¨ÛŒÙ†", "Ø²ÛŒØ±", "Ù‡Ø§ÛŒ", "Ù‡Ø§", "Ù…ÛŒ", "Ù…ÛŒÚ©Ù†Ø¯", "Ù…ÛŒÚ©Ù†Ù†Ø¯", "Ø¯Ø§Ø±Ø¯", "Ø¯Ø§Ø±Ù†Ø¯", "Ø¨Ø§Ø´Ù†Ø¯", "Ø¨Ø§Ø´Ø¯",
    "Ø´ÙˆØ¯", "Ø´ÙˆÙ†Ø¯", "Ø´Ø¯Ù‡", "Ø´Ø¯Ù‡ Ø§Ø³Øª", "Ø´Ø¯Ù‡ Ø§Ù†Ø¯", "Ù‡Ø§ÛŒÛŒ", "Ø·ÙˆØ±", "Ø¨Ø§Ø±Ù‡", "Ø«Ø§Ù†ÛŒÙ‡", "Ø¯Ù‚ÛŒÙ‚Ù‡", "Ø³Ø§Ø¹Øª", "Ù…Ú©Ø³Ø§"
]);

const ENGLISH_STOPWORDS = new Set([
    "the", "and", "a", "of", "to", "in", "is", "that", "it", "on", "for", "as", "with", "was", "by",
    "an", "be", "this", "are", "from", "at"
]);

function generateSuggestions() {
    const title = document.getElementById("title").value.trim();
    const subtitleEl = document.getElementById("subtitle");
    const subtitle = subtitleEl ? subtitleEl.value.trim() : "";
    const contentText = getEditorText();
    
    // ØªØ±Ú©ÛŒØ¨ Ù…ØªÙˆÙ† Ø¨Ø±Ø§ÛŒ Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ú©Ù„Ù…Ø§Øª
    const combinedText = (title + " " + subtitle + " " + contentText)
        .replace(/[\u200c-\u200f]/g, " ") // Ø¬Ø§ÛŒÚ¯Ø²ÛŒÙ†ÛŒ Ù†ÛŒÙ…â€ŒÙØ§ØµÙ„Ù‡â€ŒÙ‡Ø§
        .replace(/[0-9Û°-Û¹]/g, "") // Ø­Ø°Ù Ø§Ø¹Ø¯Ø§Ø¯
        .replace(/[^\p{Arabic}\p{L}\s-]/gu, " ") // Ù†Ú¯Ù‡ Ø¯Ø§Ø´ØªÙ† Ø­Ø±ÙˆÙ Ø§Ù„ÙØ¨Ø§ Ùˆ Ø­Ø°Ù Ø¹Ù„Ø§Ø¦Ù… Ù†Ú¯Ø§Ø±Ø´ÛŒ
        .toLowerCase();
    
    // ØªØ¨Ø¯ÛŒÙ„ Ø¨Ù‡ Ú©Ù„Ù…Ø§Øª ØªÚ©ÛŒ Ùˆ ÙÛŒÙ„ØªØ± Ú©Ø±Ø¯Ù†
    const words = combinedText.split(/\s+/).map(w => {
        w = w.trim();
        w = w.replace(/^-+|-+$/g, ""); // Ø­Ø°Ù Ø®Ø· ØªÛŒØ±Ù‡ Ø§ÙˆÙ„ Ùˆ Ø¢Ø®Ø± Ú©Ù„Ù…Ù‡
        w = w.replace(/ÛŒ/g, "ÛŒ").replace(/Ú©/g, "Ú©"); // Ù†Ø±Ù…Ø§Ù„â€ŒØ³Ø§Ø²ÛŒ Ø¹Ù…ÙˆÙ…ÛŒ
        return w;
    }).filter(w => {
        return w.length >= 3 && !PERSIAN_STOPWORDS.has(w) && !ENGLISH_STOPWORDS.has(w);
    });
    
    // Ù…Ø­Ø§Ø³Ø¨Ù‡ ÙØ±Ø§ÙˆØ§Ù†ÛŒ ØªÚ©Ø±Ø§Ø± Ú©Ù„Ù…Ø§Øª
    const freq = {};
    words.forEach(w => {
        freq[w] = (freq[w] || 0) + 1;
    });
    
    // Ù…Ø±ØªØ¨â€ŒØ³Ø§Ø²ÛŒ Ø¨Ø± Ø§Ø³Ø§Ø³ ØªØ¹Ø¯Ø§Ø¯ ØªÚ©Ø±Ø§Ø±
    const sortedWords = Object.keys(freq).sort((a, b) => freq[b] - freq[a]);
    
    // Ø§Ø³ØªØ®Ø±Ø§Ø¬ Û¸ Ú©Ù„Ù…Ù‡ Ø§ÙˆÙ„ Ø¨Ø§ Ø¨ÛŒØ´ØªØ±ÛŒÙ† ØªÚ©Ø±Ø§Ø±
    const topSuggestions = sortedWords.slice(0, 8);
    
    const container = document.getElementById("suggested-chips-list");
    container.innerHTML = "";
    
    if (topSuggestions.length === 0) {
        container.innerHTML = `<span style="font-size: 11.5px; color: var(--muted-color); font-style: italic; padding: 0 4px;">Ú©Ù„Ù…Ù‡ Ú©Ù„ÛŒØ¯ÛŒ ÛŒØ§ÙØª Ù†Ø´Ø¯. Ù…ØªÙ† Ø®Ø¨Ø± Ø±Ø§ Ø¨Ù†ÙˆÛŒØ³ÛŒØ¯.</span>`;
        return;
    }
    
    topSuggestions.forEach(word => {
        const btn = document.createElement("span");
        btn.className = "suggestion-chip";
        btn.innerHTML = `+ ${word}`;
        btn.onclick = () => addSuggestedWord(btn, word);
        container.appendChild(btn);
    });
}

function addSuggestedWord(btnEl, word) {
    // Û±. Ø§ÙØ²ÙˆØ¯Ù† Ø¨Ù‡ Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ (Keywords)
    const keywordsInput = document.getElementById("keywords");
    let keywords = keywordsInput.value.split("ØŒ").join(",").split(",").map(k => k.trim()).filter(Boolean);
    if (!keywords.includes(word)) {
        keywords.push(word);
        keywordsInput.value = keywords.join("ØŒ ");
    }
    
    // Û². Ø§ÙØ²ÙˆØ¯Ù† Ø¨Ù‡ ØªÚ¯â€ŒÙ‡Ø§ÛŒ Ø³Ø¦Ùˆ (Tags)
    const tagsInput = document.getElementById("tags");
    let tags = tagsInput.value.split("ØŒ").join(",").split(",").map(t => t.trim()).filter(Boolean);
    if (!tags.includes(word)) {
        tags.push(word);
        tagsInput.value = tags.join("ØŒ ");
        if (typeof renderTagChips === "function") {
            renderTagChips();
        }
    }
    
    // Û³. Ø§Ù†Ø·Ø¨Ø§Ù‚ Ùˆ Ø§Ù†ØªØ®Ø§Ø¨ Ø®ÙˆØ¯Ú©Ø§Ø± Ø¨Ø±Ú†Ø³Ø¨â€ŒÙ‡Ø§ÛŒ Ù…ÙˆØ¶ÙˆØ¹ÛŒ Ø¯Ø± ØµÙˆØ±Øª Ù‡Ù…Ø®ÙˆØ§Ù†ÛŒ Ø¨Ø§ Ú©Ù„Ù…Ù‡ Ú©Ù„ÛŒØ¯ÛŒ
    const checkboxes = document.querySelectorAll('input[name="tag_ids[]"]');
    checkboxes.forEach(cb => {
        const labelSpan = cb.nextElementSibling;
        if (labelSpan) {
            const tagName = labelSpan.textContent.trim().toLowerCase();
            // Ø§Ù†Ø·Ø¨Ø§Ù‚ Ú©Ø§Ù…Ù„ ÛŒØ§ Ø§Ù†Ø·Ø¨Ø§Ù‚ Ø¬Ø²Ø¦ÛŒ
            if (tagName === word.toLowerCase() || word.toLowerCase().includes(tagName) || tagName.includes(word.toLowerCase())) {
                cb.checked = true;
                cb.dispatchEvent(new Event("change", { bubbles: true }));
            }
        }
    });
    
    // Ø§Ù†ÛŒÙ…ÛŒØ´Ù† Ú©ÙˆÚ†Ú© Ø­Ø°Ù Ú†ÛŒÙ¾ Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯ÛŒ Ù¾Ø³ Ø§Ø² Ú©Ù„ÛŒÚ© Ùˆ Ø§Ø¶Ø§ÙÙ‡ Ø´Ø¯Ù†
    btnEl.style.transform = "scale(0.8)";
    btnEl.style.opacity = "0";
    setTimeout(() => btnEl.remove(), 200);
}

