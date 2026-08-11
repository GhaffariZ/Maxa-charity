
(function(){
  function applyMaxaTheme(){
    var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
    if(d){ document.documentElement.setAttribute('data-theme','dark'); if(document.body) document.body.setAttribute('data-theme','dark'); }
    else { document.documentElement.removeAttribute('data-theme'); if(document.body) document.body.removeAttribute('data-theme'); }
  }
  applyMaxaTheme();
  window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });
})();



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
        .replace(/[^\u0600-\u06FFa-zA-Z\s-]/g, " ") // Ù†Ú¯Ù‡ Ø¯Ø§Ø´ØªÙ† Ø­Ø±ÙˆÙ Ø§Ù„ÙØ¨Ø§ Ùˆ Ø­Ø°Ù Ø¹Ù„Ø§Ø¦Ù… Ù†Ú¯Ø§Ø±Ø´ÛŒ
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
    renderKeywordsChips();
}

/* =================== Ù…Ø¯ÛŒØ±ÛŒØª Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ (Chips) =================== */
function renderKeywordsChips() {
    const hiddenInput = document.getElementById("keywords");
    const container = document.getElementById("keywordsChips");
    if (!hiddenInput || !container) return;
    
    let kws = hiddenInput.value.split("ØŒ").join(",").split(",").map(k => k.trim()).filter(Boolean);
    container.innerHTML = "";
    kws.forEach((kw, index) => {
        const chip = document.createElement("span");
        chip.className = "chip";
        chip.style.cssText = "background:var(--surface-2); border:1px solid var(--border-color); color:var(--text-color); font-size:12.5px; padding:4px 10px; border-radius:12px; display:inline-flex; align-items:center; gap:6px;";
        chip.innerHTML = `${escapeHtml(kw)} <svg onclick="removeKeyword(${index})" style="cursor:pointer; color:var(--muted-color);" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;
        container.appendChild(chip);
    });
    hiddenInput.value = kws.join("ØŒ");
    updateSeoWidget();
}

function removeKeyword(index) {
    const hiddenInput = document.getElementById("keywords");
    let kws = hiddenInput.value.split("ØŒ").join(",").split(",").map(k => k.trim()).filter(Boolean);
    kws.splice(index, 1);
    hiddenInput.value = kws.join("ØŒ");
    renderKeywordsChips();
}

document.addEventListener("DOMContentLoaded", () => {
    const kwVisual = document.getElementById("keywords_visual");
    if (kwVisual) {
        kwVisual.addEventListener("keydown", function(e) {
            if (e.key === "Enter" || e.key === "," || e.key === "ØŒ") {
                e.preventDefault();
                const val = this.value.trim();
                if (val) {
                    const hiddenInput = document.getElementById("keywords");
                    let kws = hiddenInput.value.split("ØŒ").join(",").split(",").map(k => k.trim()).filter(Boolean);
                    if (!kws.includes(val)) kws.push(val);
                    hiddenInput.value = kws.join("ØŒ");
                    this.value = "";
                    renderKeywordsChips();
                }
            }
        });
        kwVisual.addEventListener("blur", function() {
            const val = this.value.trim();
            if (val) {
                const hiddenInput = document.getElementById("keywords");
                let kws = hiddenInput.value.split("ØŒ").join(",").split(",").map(k => k.trim()).filter(Boolean);
                if (!kws.includes(val)) kws.push(val);
                hiddenInput.value = kws.join("ØŒ");
                this.value = "";
                renderKeywordsChips();
            }
        });
    }
    renderKeywordsChips();
});

