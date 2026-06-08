<?php
$components_path = __DIR__ . "/components/";
$component_dirs = array_filter(glob($components_path . "*"), function($v){ return is_dir($v); });
$components = [];

foreach ($component_dirs as $dir) {
    $name = basename($dir);
    $tag = "عمومی";
    $metaFile = $dir . "/meta.json";

    if (file_exists($metaFile)) {
        $meta = json_decode(file_get_contents($metaFile), true);
        if (is_array($meta) && isset($meta["tag"])) {
            $tag = $meta["tag"];
        }
    }

    $components[] = [
        "name" => $name,
        "tag"  => $tag
    ];
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>ساخت صفحه جدید | پنل مکسا</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
body {
  margin: 0;
  font-family: tahoma;
  background: #f1f3f9;
  min-height: 100vh;
}
.page-builder-wrapper{
    margin-right:280px;
    padding:30px 40px;
    box-sizing:border-box;
    min-height:100vh;
}
@media (max-width:1200px){
  .page-builder-wrapper{ margin-right:240px }
}
@media (max-width:768px){
  .page-builder-wrapper{ margin-right:0; padding:10px }
}
.container{
  max-width:1000px;
  margin:0 auto;
}
.card{
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 5px 25px rgba(0,0,0,.05);
  margin-bottom:40px;
}
.input-group { margin-bottom: 25px; }
.input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  box-sizing: border-box;
}
.builder-area { margin-top: 10px; }
.builder-toolbar {
  display: flex;
  gap: 12px;
  margin-top: 25px;
  padding-top: 20px;
  border-top: 1px dashed #eee;
}
.btn {
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  transition: 0.3s;
}
.btn-add { background: #6c5ce7; color: white; }
.btn-preview { background: #0984e3; color: white; }
.btn-save { background: #00b894; color: white; }
.btn:hover { opacity: .9; transform:translateY(-2px);}
.component-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f8f9fe;
  border: 1px solid #e2e6f0;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 12px;
  cursor: grab;
}
.comp-left { display: flex; align-items: center; gap: 15px; }
.order {
  width: 30px; height: 30px;
  background: #6c5ce7; color: white;
  display: flex; align-items: center; justify-content: center;
  border-radius: 50%; font-size: 13px;
}
.remove {
  background: #ff7675; color: white; border: none;
  padding: 6px 12px; border-radius: 6px; cursor: pointer;
}
.modal {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,.6);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-content{
    background: white;
    width: 90%;
    max-width: 600px;
    max-height: 70vh;
    overflow-y: auto;
    padding: 25px;
    border-radius: 14px;
}
.tag-group { margin-bottom: 25px; }
.tag-title {
  font-weight: bold;
  color: #636e72;
  border-bottom: 2px solid #f1f3f9;
  padding-bottom: 8px;
  margin-bottom: 15px;
}
.comp-item {
  display: inline-block;
  padding: 10px 20px;
  background: #f1f3f9;
  border-radius: 8px;
  margin: 5px;
  cursor: pointer;
  transition: 0.2s;
}
.comp-item:hover { background: #6c5ce7; color: white; }
.preview-frame { width: 100%; height: 500px; border: 1px solid #eee; border-radius: 8px; }
#blockCount {
  background:#6c5ce7;
  color:white;
  padding:5px 15px;
  border-radius:20px;
  font-size:13px;
  display:inline-block;
}

/* ============================================================
   بازطراحیِ UI مطابق تمِ داشبورد مکسا (همان نام کلاس‌ها — منطق دست‌نخورده)
   ============================================================ */
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b;
  --primary-08:rgba(0,123,122,.08); --primary-12:rgba(0,123,122,.12);
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --shadow-lg:0 20px 44px -14px rgba(0,102,101,.20),0 8px 20px -10px rgba(16,40,40,.12);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --primary-08:rgba(79,178,176,.10); --primary-12:rgba(79,178,176,.16);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  --shadow-lg:0 24px 48px -16px rgba(0,0,0,.62),0 10px 24px -12px rgba(0,0,0,.5);
  color-scheme:dark; background-color:var(--color-bg);
}
/* تکهٔ زائدِ قالب Conca که بعد از builder رها شده بود کاملاً مخفی می‌شود */
.app-main{display:none !important}

*{box-sizing:border-box}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;transition:background .3s,color .3s}
*::-webkit-scrollbar{width:9px;height:9px}
*::-webkit-scrollbar-thumb{background:rgba(0,123,122,.20);border-radius:99px;border:2px solid transparent;background-clip:padding-box}
[data-theme="dark"] *::-webkit-scrollbar-thumb{background:rgba(255,255,255,.16);background-clip:padding-box}

.page-builder-wrapper{margin-right:0;padding:28px 22px;min-height:100vh}
.container{max-width:1000px;margin:0 auto}
.card{background:var(--color-surface);border:1px solid var(--color-border);padding:26px;border-radius:var(--radius);box-shadow:var(--shadow-sm);margin-bottom:30px}
.card h2{font-size:20px;font-weight:800;letter-spacing:-.01em}

.input-group{margin-bottom:22px}
.input{width:100%;padding:12px 14px;border:1.5px solid var(--color-border);border-radius:var(--radius-sm);font-family:inherit;font-size:14px;color:var(--color-text);background:var(--color-bg);transition:border-color .2s,box-shadow .2s,background .2s}
.input:focus{outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:var(--color-surface)}
#builderPlaceholder{border:2px dashed var(--color-border) !important;border-radius:var(--radius-sm);color:var(--color-muted) !important;background:var(--color-bg)}

.builder-toolbar{display:flex;gap:12px;margin-top:25px;padding-top:20px;border-top:1px dashed var(--color-border)}
.btn{border:none;padding:12px 22px;border-radius:13px;cursor:pointer;font-family:inherit;font-size:14px;font-weight:800;transition:transform .15s var(--ease),box-shadow .25s,filter .2s}
.btn:hover{transform:translateY(-2px)}
.btn:active{transform:scale(.97)}
.btn-add{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff;box-shadow:0 10px 22px -10px rgba(0,123,122,.7)}
.btn-preview{background:var(--primary-08);color:var(--color-primary-dark)}
.btn-preview:hover{filter:brightness(.97)}
.btn-save{background:linear-gradient(135deg,#1bb98a,var(--success));color:#fff;box-shadow:0 10px 22px -10px rgba(22,163,122,.7)}

.component-box{display:flex;align-items:center;justify-content:space-between;background:var(--color-bg);border:1px solid var(--color-border);padding:14px 16px;border-radius:var(--radius-sm);margin-bottom:12px;cursor:grab;transition:border-color .2s,box-shadow .2s}
.component-box:hover{border-color:var(--color-primary-light);box-shadow:var(--shadow-sm)}
.comp-left{display:flex;align-items:center;gap:15px}
.order{width:30px;height:30px;background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));color:#fff;display:flex;align-items:center;justify-content:center;border-radius:50%;font-size:13px;font-weight:700;flex-shrink:0}
.remove{background:rgba(224,85,107,.12);color:var(--danger);border:none;padding:7px 14px;border-radius:9px;cursor:pointer;font-family:inherit;font-weight:700;font-size:12.5px;transition:filter .2s}
.remove:hover{filter:brightness(.96)}
/* خوانایی نامِ کامپوننتِ انتخاب‌شده در دارک‌مود (override رنگِ inline داخل JS) */
.component-box .comp-left > div:last-child{color:var(--color-text) !important}
.component-box .comp-left > div:first-child{color:var(--color-muted) !important}
.comp-item{color:var(--color-text)}

.modal{position:fixed;inset:0;background:rgba(16,40,40,.5);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:1000}
.modal-content{background:var(--color-surface);color:var(--color-text);width:90%;max-width:600px;max-height:72vh;overflow-y:auto;padding:26px;border-radius:var(--radius);border:1px solid var(--color-border);box-shadow:var(--shadow-lg)}
.modal-content h3{font-size:17px;font-weight:800}
.tag-group{margin-bottom:24px}
.tag-title{font-weight:800;color:var(--color-muted);border-bottom:2px solid var(--color-border);padding-bottom:9px;margin-bottom:14px;font-size:13px}
.comp-item{display:inline-block;padding:10px 18px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:11px;margin:5px;cursor:pointer;font-size:13px;font-weight:600;transition:.2s}
.comp-item:hover{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff;border-color:transparent}
.preview-frame{width:100%;height:500px;border:1px solid var(--color-border);border-radius:var(--radius-sm);background:#fff}
#blockCount{background:var(--primary-08);color:var(--color-primary-dark);padding:6px 15px;border-radius:99px;font-size:12.5px;font-weight:800;display:inline-block}

@media (max-width:768px){
  .page-builder-wrapper{margin-right:0;padding:16px 12px}
  .card{padding:18px}
  .builder-toolbar{flex-direction:column}
  .btn{width:100%}
}
</style>
</head>
<body>

<div class="page-builder-wrapper">
  <div class="container">
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center; margin-bottom:30px;">
        <h2>ساخت صفحه جدید</h2>
        <div id="blockCount">0 بلوک</div>
      </div>

      <div class="input-group">
        <label style="display:block; margin-bottom:8px; font-weight:bold;">نام نمایشی صفحه</label>
        <input type="text" class="input" id="template_name" placeholder="مثلاً: فرود کمپین تابستان">
      </div>

      <div id="builderPlaceholder" style="text-align:center; padding:40px; border:2px dashed #ddd; border-radius:10px; color:#aaa;">
        هنوز کامپوننتی اضافه نشده است. برای شروع روی دکمه زیر کلیک کنید.
      </div>

      <div class="builder-area" id="componentList"></div>

      <div style="margin-top:20px;">
        <button type="button" class="btn btn-add" onclick="openModal()">+ افزودن کامپوننت جدید</button>
      </div>

      <div id="actionButtons" class="builder-toolbar" style="display:none;">
        <button type="button" class="btn btn-preview" onclick="previewPage()">👁️ پیش‌نمایش زنده</button>
        <button type="button" class="btn btn-save" onclick="saveTemplate()">🚀 ثبت و انتشار نهایی</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal انتخاب کامپوننت -->
<div class="modal" id="componentModal">
  <div class="modal-content">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h3 style="margin:0;">انتخاب بلوک محتوا</h3>
      <button type="button" onclick="closeModal()" style="border:none; background:none; cursor:pointer; font-size:20px;">&times;</button>
    </div>
    <div id="componentsArea"></div>
  </div>
</div>

<!-- Modal پیش‌نمایش -->
<div class="modal" id="previewModal">
  <div class="modal-content" style="width:90%; max-width:1200px;">
    <div style="display:flex; justify-content:space-between; margin-bottom:15px;">
      <h3 style="margin:0;">پیش‌نمایش زنده</h3>
      <button type="button" onclick="closePreview()" class="remove">بستن</button>
    </div>
    <iframe id="previewFrame" class="preview-frame"></iframe>
  </div>
</div>

<script>
let components = <?php echo json_encode($components, JSON_UNESCAPED_UNICODE); ?>;
let selectedComponents = [];
let dragIndex = null;

/* تم (دارک/لایت) از «داشبورد مدیریت» کنترل می‌شود — کلید مشترک: maxa-theme */
(function(){
  function applyMaxaTheme(){
    var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
    if(d) document.documentElement.setAttribute('data-theme','dark'); else document.documentElement.removeAttribute('data-theme');
  }
  applyMaxaTheme();
  window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });
})();

function openModal() {
  document.getElementById("componentModal").style.display = "flex";
  renderComponents();
}

function closeModal() {
  document.getElementById("componentModal").style.display = "none";
}

function renderComponents() {
  let tags = {};

  components.forEach(c => {
    if (!tags[c.tag]) tags[c.tag] = [];
    tags[c.tag].push(c);
  });

  let html = "";

  if (components.length === 0) {
    html = '<div style="text-align:center;color:#999;padding:20px;">هیچ کامپوننتی پیدا نشد.</div>';
  } else {
    for (let tag in tags) {
      html += `<div class="tag-group"><div class="tag-title">${tag}</div>`;
      tags[tag].forEach(c => {
        html += `<div class="comp-item" onclick="selectComponent('${c.name.replace(/'/g, "\\'")}')">${c.name}</div>`;
      });
      html += `</div>`;
    }
  }

  document.getElementById("componentsArea").innerHTML = html;
}

function selectComponent(name) {
  selectedComponents.push(name);
  closeModal();
  renderSelected();
}

function renderSelected() {
  let list = document.getElementById("componentList");
  let placeholder = document.getElementById("builderPlaceholder");
  let actionButtons = document.getElementById("actionButtons");

  list.innerHTML = "";

  if (selectedComponents.length > 0) {
    placeholder.style.display = "none";
    actionButtons.style.display = "flex";
  } else {
    placeholder.style.display = "block";
    actionButtons.style.display = "none";
  }

  selectedComponents.forEach((c, i) => {
    list.innerHTML += `
      <div class="component-box" draggable="true" ondragstart="drag(event)" data-index="${i}">
        <div class="comp-left">
          <div style="color:#ccc; cursor:move;">⠿</div>
          <div class="order">${i + 1}</div>
          <div style="font-weight:bold; color:#2d3436;">${c}</div>
        </div>
        <button type="button" class="remove" onclick="removeComponent(${i})">حذف</button>
      </div>
    `;
  });

  document.getElementById("blockCount").innerText = selectedComponents.length + " بلوک";
}

function removeComponent(i) {
  selectedComponents.splice(i, 1);
  renderSelected();
}

function drag(e) {
  dragIndex = e.target.closest(".component-box").dataset.index;
}

document.addEventListener("dragover", function(e) {
  e.preventDefault();
});

document.addEventListener("drop", function(e) {
  e.preventDefault();
  let target = e.target.closest(".component-box");
  if (!target || dragIndex === null) return;

  let dropIndex = target.dataset.index;
  let temp = selectedComponents[dragIndex];
  selectedComponents.splice(dragIndex, 1);
  selectedComponents.splice(dropIndex, 0, temp);
  dragIndex = null;
  renderSelected();
});

function previewPage() {
  fetch("template-preview.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ components: selectedComponents })
  })
  .then(res => res.text())
  .then(html => {
    let frame = document.getElementById("previewFrame");
    frame.contentWindow.document.open();
    frame.contentWindow.document.write(html);
    frame.contentWindow.document.close();
    document.getElementById("previewModal").style.display = "flex";
  })
  .catch(err => {
    console.error(err);
    alert("خطا در پیش‌نمایش صفحه");
  });
}

function closePreview() {
  document.getElementById("previewModal").style.display = "none";
}

function saveTemplate() {
  let name = document.getElementById("template_name").value.trim();

  if (!name) {
    alert("نام صفحه را وارد کنید");
    return;
  }

  if (!selectedComponents.length) {
    alert("حداقل یک کامپوننت اضافه کنید.");
    return;
  }

  fetch("template-save.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      name: name,
      components: selectedComponents
    })
  })
.then(res => res.json())
.then(data => {
  if (data.status === "success") {
    window.location.href = "page-list.php";
  }
})
  .catch(err => {
    console.error("Fetch error:", err);
    alert("مشکل در پاسخ سرور یا JSON.");
  });
}

renderSelected();
</script>

</body>


<!-- ========== SIDEBAR ========== -->
  <!-- استایل‌های قالب Conca حذف شدند تا با تم داشبورد تداخل نکنند (بخش زیر نیز مخفی است) -->
 </head>
 <body>
  <div class="app-main">
   <!-- app wrapper start -->
   <div class="app-wrapper d-flex flex-column align-items-stretch min-vh-100" id="app-wrapper">
	<!-- app sidebar start -->
	<div class="app-sidebar overflow-hidden" id="app-sidebar">
	 <div class="app-sidebar-wrapper">
	  <!-- app sidebar header -->
	  <div class="app-sidebar-header d-flex align-items-center justify-content-between">
	   <a class="app-sidebar-logo" href="index.html">
		<img alt="Conca" class="app-main-logo logo-black" src="assets/img/logo/logo.png" width="105"/>
		<img alt="Conca" class="app-main-logo logo-white d-none" src="assets/img/logo/logo.png" width="105"/>
	   </a>
	   <button class="app-sidebar-close-btn app-sidebar-mobile-close d-xl-none" type="button">
		<svg fill="none" height="12" viewbox="0 0 20 12" width="20" xmlns="http://www.w3.org/2000/svg">
		 <path d="M10.6923 10.2857L6.53846 6M6.53846 6L10.6923 1.71429M6.53846 6L19 6M1 11L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
		 </path>
		</svg>
	   </button>
	  </div>
	  <!-- app sidebar menu -->
	  <div class="app-sidebar-menu" id="app-sidebar-menu">
	   <ul>
		<li class="app-sidebar-menu-heading">
		  <a class="menu-link d-flex align-items-center" href="index.html">
		 <span>
		  <span class="app-sidebar-menu-heading-line">
		  </span>
		  صفحه اصلی
			<path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
		 </span>
		 </a>
		</li>
		<li class="app-sidebar-menu-item">
		 <a class="menu-link d-flex align-items-center" href="app-chat.html">
		  <span class="menu-icon flex-shrink-0">
		   <svg fill="none" height="17" viewbox="0 0 17 17" width="17" xmlns="http://www.w3.org/2000/svg">
			<path clip-rule="evenodd" d="M13.5533 13.5524C11.2612 15.8448 7.86709 16.34 5.08957 15.0555C4.67954 14.8904 4.34337 14.757 4.02378 14.757C3.13362 14.7623 2.02563 15.6254 1.44978 15.0502C0.873921 14.4743 1.7377 13.3655 1.7377 12.4699C1.7377 12.1503 1.60957 11.8201 1.4445 11.4093C0.159379 8.63222 0.655337 5.23702 2.94745 2.9454C5.87345 0.018324 10.6273 0.0183241 13.5533 2.94465C16.4846 5.87625 16.4793 10.6261 13.5533 13.5524Z" fill-rule="evenodd" opacity="0.4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M11.2029 8.55969H11.2092" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
			</path>
			<path d="M8.19507 8.55969H8.20137" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
			</path>
			<path d="M5.18726 8.55969H5.19356" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
			</path>
		   </svg>
		  </span>
		  <span class="menu-title flex-grow-1">
		   چت
		  </span>
		 </a>
		</li>
		<li class="app-sidebar-menu-item">
		 <a class="menu-link d-flex align-items-center" href="app-pos.html">
		  <span class="menu-icon flex-shrink-0">
		   <svg fill="none" height="17" viewbox="0 0 17 17" width="17" xmlns="http://www.w3.org/2000/svg">
			<path d="M2.89062 10.3899L10.3895 2.89099" opacity="0.4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.5">
			</path>
			<path d="M7.57812 12.9582L8.47812 12.0582" opacity="0.4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.5">
			</path>
			<path d="M9.59375 10.9404L11.3863 9.14795" opacity="0.4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.5">
			</path>
			<path d="M1.95095 6.92815L6.93095 1.94815C8.52095 0.358152 9.31595 0.350652 10.891 1.92565L14.5735 5.60815C16.1485 7.18315 16.141 7.97815 14.551 9.56815L9.57095 14.5482C7.98095 16.1382 7.18595 16.1457 5.61095 14.5707L1.92845 10.8882C0.353453 9.31315 0.353452 8.52565 1.95095 6.92815Z" opacity="0.4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M0.75 15.7478H15.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
		   </svg>
		  </span>
		  <span class="menu-title flex-grow-1">
		   بازارچه خیریه
		  </span>
		 </a>
		</li>
<li class="app-sidebar-menu-heading">
  <span>
    <span class="app-sidebar-menu-heading-line"></span>
    کامپوننت ها
  </span>
</li>

<li class="app-sidebar-menu-item has-dropdown">
  <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
    <span class="menu-icon flex-shrink-0">
      <!-- آیکن کامپوننت‌ها -->
      <svg fill="none" height="17" viewBox="0 0 17 17" width="17">
        <path d="M14.5962 0.75H1.90385C1.26659 0.75 0.75 1.26659 0.75 1.90385V14.5962C0.75 15.2334 1.26659 15.75 1.90385 15.75H14.5962C15.2334 15.75 15.75 15.2334 15.75 14.5962V1.90385C15.75 1.26659 15.2334 0.75 14.5962 0.75Z"
              stroke="currentColor" stroke-width="1.5"/>
      </svg>
    </span>

    <span class="menu-title flex-grow-1">
      کامپوننت ها
    </span>

    <span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
      <svg fill="none" height="10" viewBox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
        <path d="M1 9L5 5L1 1"
              stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
      </svg>
    </span>
  </a>

  <!-- زیرمنوی کامپوننت‌ها -->
  <ul class="app-sidebar-submenu">
    <!-- ساخت کامپوننت جدید -->
    <li class="app-sidebar-menu-item">
      <a class="menu-link d-flex align-items-center" href="component-create.php">
        <span class="menu-title flex-grow-1">
          ساخت کامپوننت جدید
        </span>
        <span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
          <svg fill="none" height="10" viewBox="0 0 6 10" width="6">
            <path d="M1 9L5 5L1 1"
                  stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
          </svg>
        </span>
      </a>
    </li>
  </ul>
</li>

<li class="app-sidebar-menu-heading">
  <span>
    <span class="app-sidebar-menu-heading-line"></span>
    ساخت و مدیریت صفحات سایت
  </span>
</li>

<li class="app-sidebar-menu-item has-dropdown">
  <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
    <span class="menu-icon flex-shrink-0">
      <svg fill="none" height="17" viewBox="0 0 17 17" width="17">
        <path d="M14.5962 0.75H1.90385C1.26659 0.75 0.75 1.26659 0.75 1.90385V14.5962C0.75 15.2334 1.26659 15.75 1.90385 15.75H14.5962C15.2334 15.75 15.75 15.2334 15.75 14.5962V1.90385C15.75 1.26659 15.2334 0.75 14.5962 0.75Z"
              stroke="currentColor" stroke-width="1.5"/>
      </svg>
    </span>

    <span class="menu-title flex-grow-1">
      صفحات سایت 
    </span>

    <span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
      <svg fill="none" height="10" viewBox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
        <path d="M1 9L5 5L1 1"
              stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
      </svg>
    </span>
  </a>


  <ul class="app-sidebar-submenu">
    <li class="app-sidebar-menu-item">
      <a class="menu-link d-flex align-items-center" href="template-create.php">
        <span class="menu-title flex-grow-1">
          ساخت صفحه جدید
        </span>
        <span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
          <svg fill="none" height="10" viewBox="0 0 6 10" width="6">
            <path d="M1 9L5 5L1 1"
                  stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
          </svg>
        </span>
      </a>
    </li>
    
    <li class="app-sidebar-menu-item">
      <a class="menu-link d-flex align-items-center" href="page-list.php">
        <span class="menu-title flex-grow-1">
          مدیریت تمام صفحات
        </span>
        <span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
          <svg fill="none" height="10" viewBox="0 0 6 10" width="6">
            <path d="M1 9L5 5L1 1"
                  stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
          </svg>
        </span>
      </a>
    </li>
  </ul>
</li>


		<li class="app-sidebar-menu-heading">
		 <span>
		  <span class="app-sidebar-menu-heading-line">
		  </span>
		  مدیریت حساب های کاربری
		 </span>
		</li>
		<li class="app-sidebar-menu-item has-dropdown">
		 <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
		  <span class="menu-icon flex-shrink-0">
		   <svg fill="none" height="16" viewbox="0 0 17 16" width="17" xmlns="http://www.w3.org/2000/svg">
			<path d="M14.5962 4.21155H1.90385C1.26659 4.21155 0.75 4.72814 0.75 5.36539V13.4423C0.75 14.0796 1.26659 14.5962 1.90385 14.5962H14.5962C15.2334 14.5962 15.75 14.0796 15.75 13.4423V5.36539C15.75 4.72814 15.2334 4.21155 14.5962 4.21155Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M0.75 8.82692H15.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M8.25 7.67307V9.98076" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M11.7117 4.21153C11.7117 3.29348 11.347 2.41302 10.6978 1.76386C10.0486 1.1147 9.16817 0.75 8.25011 0.75V0.75C7.33206 0.75 6.4516 1.1147 5.80244 1.76386C5.15327 2.41302 4.78857 3.29348 4.78857 4.21153" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
		   </svg>
		  </span>
		  <span class="menu-title flex-grow-1">
		   مدیریت جذب آنلاین 
		  </span>
		  <span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
		   <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
			<path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
		   </svg>
		  </span>
		 </a>
		 <ul class="app-sidebar-submenu">
		  <li class="app-sidebar-menu-item">
		   <a class="menu-link d-flex align-items-center" href="user-settings.html">
			<span class="menu-title flex-grow-1">
			 تنظیمات
			</span>
		   </a>
		  </li>
		  <li class="app-sidebar-menu-item">
		   <a class="menu-link d-flex align-items-center" href="user-settings-billing.html">
			<span class="menu-title flex-grow-1">
			 طرح های صورتحساب
			</span>
		   </a>
		  </li>
		  <li class="app-sidebar-menu-item">
		   <a class="menu-link d-flex align-items-center" href="user-settings-notification.html">
			<span class="menu-title flex-grow-1">
			 اطلاعیه ها
			</span>
		   </a>
		  </li>
		  <li class="app-sidebar-menu-item">
		   <a class="menu-link d-flex align-items-center" href="user-settings-connection.html">
			<span class="menu-title flex-grow-1">
			 اتصالات
			</span>
		   </a>
		  </li>
		 </ul>
		</li>
		<li class="app-sidebar-menu-item has-dropdown">
		 <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
		  <span class="menu-icon flex-shrink-0">
		   <svg fill="none" height="16" viewbox="0 0 17 16" width="17" xmlns="http://www.w3.org/2000/svg">
			<path d="M5.94234 5.9423C7.37615 5.9423 8.53849 4.77997 8.53849 3.34615C8.53849 1.91234 7.37615 0.75 5.94234 0.75C4.50853 0.75 3.34619 1.91234 3.34619 3.34615C3.34619 4.77997 4.50853 5.9423 5.94234 5.9423Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M11.1346 14.5961H0.75V13.4423C0.75 12.0652 1.29704 10.7445 2.27079 9.77079C3.24453 8.79705 4.56521 8.25 5.9423 8.25C7.31938 8.25 8.64006 8.79705 9.6138 9.77079C10.5875 10.7445 11.1346 12.0652 11.1346 13.4423V14.5961Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M10.5576 0.75C11.2462 0.75 11.9065 1.02352 12.3934 1.5104C12.8802 1.99727 13.1538 2.65761 13.1538 3.34615C13.1538 4.03469 12.8802 4.69504 12.3934 5.18191C11.9065 5.66878 11.2462 5.9423 10.5576 5.9423" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M12.4038 8.46924C13.3867 8.84314 14.2329 9.50667 14.8304 10.372C15.4279 11.2374 15.7486 12.2638 15.75 13.3154V14.5962H14.0192" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
		   </svg>
		  </span>
		  <span class="menu-title flex-grow-1">
		   کاربران
		  </span>
		  <span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
		   <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
			<path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
		   </svg>
		  </span>
		 </a>
		 <ul class="app-sidebar-submenu">
		  <li class="app-sidebar-menu-item">
		   <a class="menu-link d-flex align-items-center" href="users-list.html">
			<span class="menu-title flex-grow-1">
			 لیست کاربران
			</span>
		   </a>
		  </li>
		  <li class="app-sidebar-menu-item">
		   <a class="menu-link d-flex align-items-center" href="users-add.html">
			<span class="menu-title flex-grow-1">
			 کاربر ایجاد کنید
			</span>
		   </a>
		  </li>
		  <li class="app-sidebar-menu-item">
		   <a class="menu-link d-flex align-items-center" href="users-view.html">
			<span class="menu-title flex-grow-1">
			 مشاهده کاربر
			</span>
		   </a>
		  </li>
		 </ul>
		</li>
		<li class="app-sidebar-menu-item has-dropdown">
		 <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
		  <span class="menu-icon flex-shrink-0">
		   <svg fill="none" height="17" viewbox="0 0 17 17" width="17" xmlns="http://www.w3.org/2000/svg">
			<path d="M14.346 7.12576H1.91907C1.75328 7.12435 1.58913 7.15852 1.43767 7.22596C1.28622 7.2934 1.15099 7.39255 1.04112 7.5167C0.931244 7.64086 0.849281 7.78714 0.800759 7.94567C0.752236 8.10421 0.738284 8.2713 0.759843 8.43569L1.60608 14.8114C1.64286 15.0921 1.78106 15.3496 1.9946 15.5354C2.20814 15.7211 2.48227 15.8224 2.76531 15.82H13.4766C13.7596 15.8224 14.0337 15.7211 14.2473 15.5354C14.4608 15.3496 14.599 15.0921 14.6358 14.8114L15.482 8.43569C15.5033 8.27324 15.49 8.10812 15.4428 7.95121C15.3956 7.7943 15.3158 7.64918 15.2084 7.5254C15.1011 7.40162 14.9687 7.30201 14.82 7.23312C14.6714 7.16424 14.5098 7.12764 14.346 7.12576Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M5.23438 10.0238V12.9219" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M8.13232 10.0238V12.9219" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M11.0303 10.0238V12.9219" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M11.0073 1.95561C11.7017 2.07558 12.338 2.41906 12.8192 2.93381C13.3005 3.44856 13.6005 4.10642 13.6735 4.80731L13.9286 7.12577" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M2.33643 7.12576L2.59146 4.80731C2.66927 4.11057 2.97136 3.45797 3.45224 2.94782C3.93312 2.43767 4.56675 2.09759 5.25768 1.97879" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M11.0305 2.19903C11.0305 2.38932 10.993 2.57775 10.9202 2.75356C10.8474 2.92936 10.7407 3.0891 10.6061 3.22366C10.4715 3.35821 10.3118 3.46495 10.136 3.53777C9.96019 3.61059 9.77177 3.64807 9.58148 3.64807H6.68341C6.2991 3.64807 5.93053 3.4954 5.65879 3.22366C5.38704 2.95191 5.23438 2.58334 5.23438 2.19903C5.23438 1.81473 5.38704 1.44616 5.65879 1.17441C5.93053 0.902666 6.2991 0.75 6.68341 0.75H9.58148C9.96579 0.75 10.3344 0.902666 10.6061 1.17441C10.8778 1.44616 11.0305 1.81473 11.0305 2.19903V2.19903Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
		   </svg>
		  </span>
		  <span class="menu-title flex-grow-1">
		   تجارت الکترونیک
		   <span class="badge bg-success rounded-pill pulse pulse-success border-0">
			2
		   </span>
		  </span>
		  <span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
		   <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
			<path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
		   </svg>
		  </span>
		 </a>
		 <ul class="app-sidebar-submenu">
		  <li class="app-sidebar-menu-item has-dropdown">
		   <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
			<span class="menu-title flex-grow-1">
			 محصولات
			</span>
			<span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
			 <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
			  <path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			  </path>
			 </svg>
			</span>
		   </a>
		   <ul class="app-sidebar-submenu">
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-product-list.html">
			  <span class="menu-title flex-grow-1">
			   لیست محصولات
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-product-add.html">
			  <span class="menu-title flex-grow-1">
			   افزودن محصول
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-product-edit.html">
			  <span class="menu-title flex-grow-1">
			   ویرایش محصول
			  </span>
			 </a>
			</li>
		   </ul>
		  </li>
		  <li class="app-sidebar-menu-item has-dropdown">
		   <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
			<span class="menu-title flex-grow-1">
			 دسته بندی
			</span>
			<span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
			 <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
			  <path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			  </path>
			 </svg>
			</span>
		   </a>
		   <ul class="app-sidebar-submenu">
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-product-cat-list.html">
			  <span class="menu-title flex-grow-1">
			   فهرست دسته
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-product-cat-add.html">
			  <span class="menu-title flex-grow-1">
			   اضافه کردن دسته
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-product-cat-edit.html">
			  <span class="menu-title flex-grow-1">
			   ویرایش دسته
			  </span>
			 </a>
			</li>
		   </ul>
		  </li>
		  <li class="app-sidebar-menu-item has-dropdown">
		   <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
			<span class="menu-title flex-grow-1">
			 کد تخفیف
			</span>
			<span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
			 <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
			  <path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			  </path>
			 </svg>
			</span>
		   </a>
		   <ul class="app-sidebar-submenu">
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-coupon-list.html">
			  <span class="menu-title flex-grow-1">
			   لیست کد تخفیف
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-coupon-history.html">
			  <span class="menu-title flex-grow-1">
			   تاریخچه کد تخفیف
			  </span>
			 </a>
			</li>
		   </ul>
		  </li>
		  <li class="app-sidebar-menu-item has-dropdown">
		   <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
			<span class="menu-title flex-grow-1">
			 سفارشات
			</span>
			<span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
			 <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
			  <path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			  </path>
			 </svg>
			</span>
		   </a>
		   <ul class="app-sidebar-submenu">
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-order-list.html">
			  <span class="menu-title flex-grow-1">
			   لیست سفارش
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-order-details.html">
			  <span class="menu-title flex-grow-1">
			   جزئیات سفارش
			  </span>
			 </a>
			</li>
		   </ul>
		  </li>
		  <li class="app-sidebar-menu-item has-dropdown">
		   <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
			<span class="menu-title flex-grow-1">
			 مشتریان
			</span>
			<span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
			 <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
			  <path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			  </path>
			 </svg>
			</span>
		   </a>
		   <ul class="app-sidebar-submenu">
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-customer-list.html">
			  <span class="menu-title flex-grow-1">
			   همه مشتریان
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item has-dropdown">
			 <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
			  <span class="menu-title flex-grow-1">
			   جزئیات مشتریان
			  </span>
			  <span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
			   <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
				<path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
				</path>
			   </svg>
			  </span>
			 </a>
			 <ul class="app-sidebar-submenu">
			  <li class="app-sidebar-menu-item">
			   <a class="menu-link d-flex align-items-center" href="ecommerce-customer-details-general.html">
				<span class="menu-title flex-grow-1">
				 عمومی
				</span>
			   </a>
			  </li>
			  <li class="app-sidebar-menu-item">
			   <a class="menu-link d-flex align-items-center" href="ecommerce-customer-details-security.html">
				<span class="menu-title flex-grow-1">
				 امنیت
				</span>
			   </a>
			  </li>
			  <li class="app-sidebar-menu-item">
			   <a class="menu-link d-flex align-items-center" href="ecommerce-customer-details-payments.html">
				<span class="menu-title flex-grow-1">
				 روش های پرداخت
				</span>
			   </a>
			  </li>
			  <li class="app-sidebar-menu-item">
			   <a class="menu-link d-flex align-items-center" href="ecommerce-customer-details-address.html">
				<span class="menu-title flex-grow-1">
				 آدرس
				</span>
			   </a>
			  </li>
			  <li class="app-sidebar-menu-item">
			   <a class="menu-link d-flex align-items-center" href="ecommerce-customer-details-notifications.html">
				<span class="menu-title flex-grow-1">
				 اطلاعیه ها
				</span>
			   </a>
			  </li>
			 </ul>
			</li>
		   </ul>
		  </li>
		  <li class="app-sidebar-menu-item">
		   <a class="menu-link d-flex align-items-center" href="ecommerce-review-list.html">
			<span class="menu-title flex-grow-1">
			 مدیریت نظرات
			</span>
		   </a>
		  </li>
		  <li class="app-sidebar-menu-item has-dropdown">
		   <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
			<span class="menu-title flex-grow-1">
			 تنظیمات فروشگاه
			</span>
			<span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
			 <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
			  <path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			  </path>
			 </svg>
			</span>
		   </a>
		   <ul class="app-sidebar-submenu">
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-settings-general.html">
			  <span class="menu-title flex-grow-1">
			   عمومی
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-settings-payments.html">
			  <span class="menu-title flex-grow-1">
			   روش های پرداخت
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-settings-shippings.html">
			  <span class="menu-title flex-grow-1">
			   روش های حمل و نقل
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="ecommerce-settings-notifications.html">
			  <span class="menu-title flex-grow-1">
			   اطلاعیه ها
			  </span>
			 </a>
			</li>
		   </ul>
		  </li>
		 </ul>
		</li>
		<li class="app-sidebar-menu-item has-dropdown">
		 <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
		  <span class="menu-icon flex-shrink-0">
		   <svg fill="none" height="17" viewbox="0 0 17 17" width="17" xmlns="http://www.w3.org/2000/svg">
			<path d="M8.25018 15.75C6.56004 13.8892 4.26794 12.684 1.77697 12.3461C1.49344 12.3148 1.23158 12.1795 1.04193 11.9664C0.852285 11.7533 0.748291 11.4775 0.750021 11.1923V1.90372C0.75001 1.73705 0.786105 1.57237 0.855821 1.42098C0.925537 1.2696 1.02722 1.13512 1.15388 1.02679C1.2783 0.920425 1.42393 0.841754 1.5811 0.795997C1.73826 0.750241 1.90336 0.738447 2.06543 0.761399C4.44736 1.15672 6.62636 2.34377 8.25018 4.13066V15.75Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
			<path d="M8.25 15.75C9.94015 13.8892 12.2322 12.684 14.7232 12.3461C15.0067 12.3148 15.2686 12.1795 15.4582 11.9664C15.6479 11.7533 15.7519 11.4775 15.7502 11.1923V1.90372C15.7502 1.73705 15.7141 1.57237 15.6444 1.42098C15.5746 1.2696 15.473 1.13512 15.3463 1.02679C15.2219 0.920425 15.0763 0.841754 14.9191 0.795997C14.7619 0.750241 14.5968 0.738447 14.4347 0.761399C12.0528 1.15672 9.87382 2.34377 8.25 4.13066V15.75Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
		   </svg>
		  </span>
		  <span class="menu-title flex-grow-1">
		   LMS
		  </span>
		  <span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
		   <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
			<path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			</path>
		   </svg>
		  </span>
		 </a>
		 <ul class="app-sidebar-submenu">
		  <li class="app-sidebar-menu-item has-dropdown">
		   <a class="menu-link d-flex align-items-center" href="javascript:void(0);">
			<span class="menu-title flex-grow-1">
			 دوره ها
			</span>
			<span class="menu-arrow flex-shrink-0 d-flex align-items-center justify-content-center">
			 <svg fill="none" height="10" viewbox="0 0 6 10" width="6" xmlns="http://www.w3.org/2000/svg">
			  <path d="M1 9L5 5L1 1" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
			  </path>
			 </svg>
			</span>
		   </a>
		   <ul class="app-sidebar-submenu">
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="lms-course-list.html">
			  <span class="menu-title flex-grow-1">
			   همه دوره ها
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="lms-course-add.html">
			  <span class="menu-title flex-grow-1">
			   اضافه کردن دوره
			  </span>
			 </a>
			</li>
			<li class="app-sidebar-menu-item">
			 <a class="menu-link d-flex align-items-center" href="lms-course-edit.html">
			  <span class="menu-title flex-grow-1">
			   ویرایش دوره
			  </span>
			 </a>
			</li>
		   </ul>
		  </li>
		  <li class="app-sidebar-menu-item">
		   <a class="menu-link d-flex align-items-center" href="lms-course-grid.html">
			<span class="menu-title flex-grow-1">
			 گرید دوره
			</span>
		   </a>
		  </li>
		  <li class="app-sidebar-menu-item">
		   <a class="menu-link d-flex align-items-center" href="lms-course-details.html">
			<span class="menu-title flex-grow-1">
			 جزئیات دوره
			</span>
		   </a>
		  </li>
		 </ul>
		</li>
	   </ul>
	  </div>
	  <!-- app sidebar menu end -->
	  <!-- app sidebar footer start -->
	  <div class="app-sidebar-footer">
	   <div class="d-flex align-items-center gap-3">
		<div class="avatar rounded-pill">
		 <img alt="conca" src="assets/img/avatar/10.jpg"/>
		</div>
		<div class="">
		 <h6 class="mb-0">
		  عرفان تیموری
		 </h6>
		 <span class="text-muted">
		  مدیر
		 </span>
		</div>
	   </div>
	   <div class="">
		<button aria-expanded="false" class="dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
		 <svg fill="none" height="15" viewbox="0 0 11 15" width="11" xmlns="http://www.w3.org/2000/svg">
		  <path d="M0.75 5.351L5.24 0.861002C5.2736 0.825912 5.31396 0.797988 5.35865 0.778911C5.40333 0.759835 5.45141 0.75 5.5 0.75C5.54859 0.75 5.59667 0.759835 5.64135 0.778911C5.68604 0.797988 5.7264 0.825912 5.76 0.861002L10.25 5.351" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
		  </path>
		  <path d="M0.75 9.151L5.24 13.641C5.2736 13.6761 5.31396 13.704 5.35865 13.7231C5.40333 13.7422 5.45141 13.752 5.5 13.752C5.54859 13.752 5.59667 13.7422 5.64135 13.7231C5.68604 13.704 5.7264 13.6761 5.76 13.641L10.25 9.151" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
		  </path>
		 </svg>
		</button>
		<ul class="dropdown-menu">
		 <li>
		  <a class="dropdown-item" href="user-profile.html">
		   نمایه
		  </a>
		 </li>
		 <li>
		  <a class="dropdown-item" href="user-settings-notification.html">
		   اطلاعیه ها
		  </a>
		 </li>
		 <li>
		  <a class="dropdown-item" href="user-settings.html">
		   تنظیمات
		  </a>
		 </li>
		 <li>
		  <hr class="dropdown-divider"/>
		 </li>
		 <li>
		  <a class="dropdown-item" href="auth-login-basic.html">
		   خروج از سیستم
		  </a>
		 </li>
		</ul>
	   </div>
	  </div>
	  <!-- app sidebar footer end -->
	 </div>
	</div>
	<!-- app sidebar end -->
  <script src="assets/vendor/libs/jquery/jquery.js">
  </script>
  <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js">
  </script>
  <script src="assets/js/bootstrap.js">
  </script>
  <!-- app js -->
  <script src="assets/js/conca-sidebar.js">
  </script>
  <script src="assets/js/conca.js">
  </script>
  <!-- page specific script -->
  <script src="assets/vendor/libs/apexcharts/apexcharts.js">
  </script>
  <script src="assets/js/dashboard/dashboard-ecommerce.js">
  </script>
</body>
</html>