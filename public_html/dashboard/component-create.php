<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');

$components_path = __DIR__ . "/components/";

$tags = [
"هیرو",
"اطلاعات عمومی ساختار",
"آموزشی",
"پژوهشی",
"درمانی",
"خدمات",
"افراد",
"آمار",
"پروژه‌های حمایتی",
"دوره‌ها",
"روایات",
"اخبار",
"سایر"
];

$tag_colors = [
"هیرو"=>"#6c5ce7",
"اطلاعات عمومی ساختار"=>"#00cec9",
"آموزشی"=>"#00b894",
"پژوهشی"=>"#0984e3",
"درمانی"=>"#d63031",
"خدمات"=>"#e17055",
"افراد"=>"#6c5ce7",
"آمار"=>"#fdcb6e",
"پروژه‌های حمایتی"=>"#e84393",
"دوره‌ها"=>"#00b894",
"روایات"=>"#636e72",
"اخبار"=>"#0984e3",
"سایر"=>"#b2bec3"
];

$groups = [];

if(is_dir($components_path)){

$components = scandir($components_path);

foreach($components as $component){

if($component=='.'||$component=='..'){continue;}

$dir=$components_path.$component;

if(is_dir($dir)){

$code='';
$tag='سایر';
$images = [];

$images_dir = $dir . '/images/';

if(is_dir($images_dir)){
    $image_files = scandir($images_dir);

    foreach($image_files as $img){
        if($img == '.' || $img == '..') continue;

        $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));

        if(in_array($ext, ['jpg','jpeg','png','webp','gif'])){
            $images[] = $img;
        }
    }

    natsort($images);
    $images = array_values($images);
}

if(file_exists($dir.'/component.php')){
$code=file_get_contents($dir.'/component.php');
}

if(file_exists($dir.'/meta.json')){
$meta=json_decode(file_get_contents($dir.'/meta.json'),true);

if(isset($meta['tag'])){
$tag=$meta['tag'];
}
}

$groups[$tag][]=[
    'name'=>$component,
    'code'=>$code,
    'tag'=>$tag,
    'images'=>$images
];


}

}

}

?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>مدیریت کامپوننت | پنل مکسا</title>

<!-- اعمالِ تم پیش از رنگ‌آمیزی تا از پرشِ نور→تاریک جلوگیری شود (کلید مشترک: maxa-theme) -->
<script>(function(){try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/panel.css">

<style>
/* استایل‌های ویژه‌ی این صفحه (توکن‌ها و کامپوننت‌های مشترک از panel.css می‌آیند) */
.container{max-width:1320px}

/* فرمِ ساختِ کامپوننت: ورودی‌های خام را با پوسته‌ی فیلدها هماهنگ می‌کنیم */
.create-form input[type=text],
.create-form select,
.create-form textarea,
#searchBox{
  width:100%;border:1px solid var(--color-border);background:var(--color-bg);border-radius:12px;
  padding:0 14px;font-family:inherit;font-size:14px;color:var(--color-text);margin-bottom:16px;
  transition:border-color .2s,box-shadow .2s,background .2s}
.create-form input[type=text],.create-form select,#searchBox{height:46px}
.create-form input:focus,.create-form select:focus,.create-form textarea:focus,#searchBox:focus{
  outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:var(--color-surface)}
.create-form label{display:block;font-size:12.5px;font-weight:700;margin-bottom:7px;color:var(--color-muted)}
.create-form textarea,.editor-panel textarea{
  min-height:360px;direction:ltr;resize:vertical;line-height:1.95;font-size:13px;color:var(--color-text);
  border:1px solid var(--color-border);border-radius:12px;
  padding:12px 14px;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace}
#searchBox{margin:0 0 22px}

/* عنوانِ گروه (تگ‌ها) */
.group-title{font-size:16px;font-weight:800;margin:36px 0 16px;color:var(--color-text);
  padding-right:12px;border-right:4px solid var(--color-primary)}

/* جعبه‌ی آپلودِ کشیدنی */
#uploadBox{border:2px dashed var(--color-border);border-radius:16px;padding:30px;text-align:center;
  cursor:pointer;background:var(--color-bg);transition:.22s ease;position:relative}
#uploadBox:hover{border-color:var(--color-primary-light);background:var(--primary-08)}
#uploadBox input{position:absolute;inset:0;opacity:0;cursor:pointer}
.upload-text{font-size:14px;font-weight:700;color:var(--color-text)}
.upload-help{font-size:12.5px;color:var(--color-muted);margin:6px 0 18px;line-height:2}

#imagePreview{margin-top:18px;display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:14px}
.preview-item{position:relative;background:var(--color-surface);border:1px solid var(--color-border);border-radius:14px;overflow:hidden;box-shadow:var(--shadow-sm)}
.preview-item img{width:100%;height:100px;object-fit:cover;display:block}
.preview-label{font-size:12px;padding:6px;text-align:center;background:var(--color-bg);color:var(--color-muted)}
.preview-remove{position:absolute;top:6px;left:6px;background:var(--danger);color:#fff;width:22px;height:22px;
  border-radius:50%;font-size:13px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none}

#imageList{margin-top:10px;display:flex;flex-direction:column;gap:8px}
.image-item{background:var(--color-bg);padding:8px 12px;border-radius:10px;font-size:13px;display:flex;justify-content:space-between;align-items:center}
.image-item span{color:var(--color-text)}
.image-remove{cursor:pointer;color:var(--danger);font-weight:bold}

/* آکاردئونِ کامپوننت‌های موجود */
.component-box{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);
  margin-bottom:16px;box-shadow:var(--shadow-sm);overflow:hidden;transition:box-shadow .25s,transform .25s}
.component-box:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.component-header{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:18px 22px;
  cursor:pointer;background:var(--color-surface);border-bottom:1px solid transparent;transition:background .2s}
.component-header:hover{background:var(--color-bg)}
.component-box.active .component-header{border-bottom-color:var(--color-border)}
.component-name{font-size:15px;font-weight:800;color:var(--color-text)}
.component-header .tag{color:#fff;padding:6px 13px;border-radius:999px;font-size:11.5px;margin-right:10px;font-weight:800}
.toggle-icon{font-size:20px;line-height:1;transition:transform .25s;color:var(--color-muted);font-weight:bold}
.component-box.active .toggle-icon{transform:rotate(180deg)}
.component-content{padding:22px;display:none;animation:ccfade .25s ease}
.component-box.active .component-content{display:block}
@keyframes ccfade{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}

/* ویرایشگرِ دو‌ستونه (کد + پیش‌نمایش) */
.editor-layout{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:18px;align-items:start;direction:ltr}
.editor-panel,.preview-panel{background:var(--color-bg);padding:16px;border-radius:16px;border:1px solid var(--color-border);min-width:0}
.panel-title{font-size:13px;font-weight:800;margin-bottom:12px;color:var(--color-muted);direction:rtl;display:flex;align-items:center;gap:8px}
.editor-panel textarea{width:100%;height:360px;min-height:360px;margin:0;border-radius:12px;background:var(--color-surface);display:block}
.editor-panel textarea:focus{outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08)}
.preview-frame{width:100%;height:360px;display:block;border:1px solid var(--color-border);border-radius:12px;background:#fff}

/* ویرایشگرِ تصاویرِ کامپوننت */
.component-images-editor{margin-top:20px;background:var(--secondary-12);border:1px solid rgba(244,166,30,.3);border-radius:16px;padding:18px}
.existing-images{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:18px}
.existing-image-item{background:var(--color-surface);border:1px solid var(--color-border);border-radius:14px;overflow:hidden;box-shadow:var(--shadow-sm);transition:.2s ease}
.existing-image-item:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.existing-image-item img{width:100%;height:120px;object-fit:cover;display:block;background:var(--color-bg)}
.existing-image-info{padding:11px;display:flex;flex-direction:column;gap:4px;direction:ltr;text-align:left}
.existing-image-info span{font-size:12.5px;font-weight:800;color:var(--color-secondary-dark)}
.existing-image-info small{font-size:11px;color:var(--color-muted);word-break:break-all;line-height:1.8}
.delete-image-label{display:flex;align-items:center;gap:8px;padding:9px 11px;background:rgba(224,85,107,.10);color:var(--danger);font-size:12.5px;font-weight:800;cursor:pointer;border-top:1px solid var(--color-border)}
.delete-image-label input{width:auto;margin:0;accent-color:var(--danger)}
.add-images-box{border-top:1px dashed rgba(244,166,30,.4);padding-top:16px;margin-top:4px}
.add-images-box label{display:block;font-size:13px;font-weight:800;margin-bottom:8px;color:var(--color-secondary-dark)}
.add-images-box input[type=file]{width:100%;padding:11px 14px;border:1px dashed var(--color-border);background:var(--color-bg);border-radius:12px;font-family:inherit;font-size:13px;color:var(--color-text);cursor:pointer}
.no-image{background:var(--color-surface);color:var(--color-muted);padding:16px;border-radius:12px;font-size:13.5px;grid-column:1/-1;border:1px dashed var(--color-border);text-align:center}

/* ردیفِ دکمه‌ها */
.component-content .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px;justify-content:flex-end}

@media(max-width:900px){
  .editor-layout{grid-template-columns:1fr}
  .component-header{flex-wrap:wrap}
  .component-content .actions{justify-content:center}
  .component-content .actions .tbtn{flex:1}
}

/* تک‌خط دارک‌مود فقط برای مواردی که توکن‌ها پوشش نمی‌دهند */
[data-theme="dark"] .preview-frame{background:#0f1518}
[data-theme="dark"] .component-images-editor{background:rgba(244,166,30,.06);border-color:rgba(244,166,30,.18)}
</style>

</head>

<body>

<div class="container">

<div class="page-head">
  <div class="ph-ic">
    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
  </div>
  <div class="ph-tx">
    <h1>کامپوننت‌ها</h1>
    <p>قطعه‌های آماده‌ی صفحه را بسازید، ویرایش کنید و کد آن‌ها را مدیریت کنید.</p>
  </div>
</div>

<div class="card">

<h2>ایجاد کامپوننت</h2>
<p class="hint">یک نام و تگ انتخاب کنید، کد کامپوننت را بنویسید و در صورت نیاز تصویر اضافه کنید.</p>

<form class="create-form" action="components-save.php" method="POST" enctype="multipart/form-data">

<label>نام کامپوننت</label>
<input type="text" name="component_name" placeholder="نام کامپوننت" required>

<label>تگ</label>
<select name="component_tag" required>

<option value="">انتخاب تگ</option>

<?php foreach($tags as $tag){ ?>

<option value="<?= $tag ?>"><?= $tag ?></option>

<?php } ?>

</select>

<label>کد کامپوننت</label>
<textarea name="component_code" placeholder="کد کامپوننت"></textarea>

<label>تصاویر کامپوننت</label>

<div id="uploadBox">
    <div class="upload-text">
        تصویر را بکشید اینجا یا کلیک کنید
    </div>
    <input type="file" name="component_images[]" id="imageUploader" multiple accept="image/*">
</div>

<div id="imagePreview"></div>

<div class="upload-help">
برای استفاده در کد بنویسید:<br>
{{image1}}<br>
{{image2}}
</div>


<button class="btn btn-primary">ثبت کامپوننت</button>

</form>

</div>

<input type="text" id="searchBox" placeholder="جستجوی کامپوننت">

<div class="group-title" style="margin-top:8px">کامپوننت‌های موجود</div>

<?php foreach($tags as $tag){ ?>

<?php if(isset($groups[$tag])){ ?>

<div class="group-title"><?= $tag ?></div>

<?php foreach($groups[$tag] as $item){ ?>

<div class="component-box">

<form action="component-update.php" method="POST" enctype="multipart/form-data">

<div class="component-header" onclick="toggleComponent(this)">

<div>

<span class="component-name"><?= $item['name'] ?></span>

<span class="tag" style="background:<?= $tag_colors[$item['tag']] ?>">
<?= $item['tag'] ?>
</span>

</div>

<div class="toggle-icon">⌄</div>

</div>

<div class="component-content">

<input type="hidden" name="component_name" value="<?= $item['name'] ?>">

<select name="component_tag">

<?php foreach($tags as $t){ ?>

<option value="<?= $t ?>" <?= $item['tag']==$t?'selected':'' ?>>
<?= $t ?>
</option>

<?php } ?>

</select>

<div class="editor-layout">

<div class="editor-panel">

<div class="panel-title">
کد کامپوننت
</div>

<textarea name="component_code" disabled><?= htmlspecialchars($item['code']) ?></textarea>

</div>

<div class="preview-panel">

<div class="panel-title">
نمایش واقعی کامپوننت
</div>

<iframe class="preview-frame"></iframe>

</div>

</div>
<div class="component-images-editor">

    <div class="panel-title">
        تصاویر کامپوننت
    </div>

    <div class="existing-images">

        <?php if(!empty($item['images'])){ ?>

            <?php foreach($item['images'] as $imgIndex => $img){ ?>

                <div class="existing-image-item">

                    <img src="/dashboard/components/<?= htmlspecialchars($item['name']) ?>/images/<?= htmlspecialchars($img) ?>" alt="">

                    <div class="existing-image-info">
                        <span>{{image<?= $imgIndex + 1 ?>}}</span>
                        <small><?= htmlspecialchars($img) ?></small>
                    </div>

                    <label class="delete-image-label">
                        <input 
                        type="checkbox" 
                        name="delete_images[]" 
                        value="<?= htmlspecialchars($img) ?>"
                        disabled
                        >
                        حذف
                    </label>

                </div>

            <?php } ?>

        <?php }else{ ?>

            <div class="no-image">
                تصویری برای این کامپوننت ثبت نشده است.
            </div>

        <?php } ?>

    </div>

    <div class="add-images-box">

        <label>
            افزودن تصویر جدید
        </label>

<input 
    type="file" 
    name="new_component_images[]" 
    multiple 
    accept="image/*"
    disabled
>
        <div class="upload-help">
            تصاویر جدید بعد از تصاویر قبلی اضافه می‌شوند و به ترتیب با 
            {{image1}}، {{image2}} و ... قابل استفاده هستند.
        </div>

    </div>

</div>


<div class="actions">

<button type="button" class="tbtn edit-btn" onclick="enableEdit(this)">
ویرایش
</button>

<button class="tbtn primary save-btn">
ذخیره
</button>

<button type="button" class="tbtn copy-btn" onclick="copyCode(this)">
کپی
</button>

<button
type="submit"
formaction="component-delete.php"
class="tbtn danger delete-btn"
onclick="return confirm('حذف شود؟')">

حذف

</button>

</div>

</div>

</form>

</div>

<?php } ?>

<?php } ?>

<?php } ?>

</div>
<?php
$component_images_map = [];

foreach($groups as $group_items){
    foreach($group_items as $component_item){
        $component_images_map[$component_item['name']] = $component_item['images'] ?? [];
    }
}
?>

<script>
const componentImagesMap = <?= json_encode($component_images_map, JSON_UNESCAPED_UNICODE); ?>;
</script>

<script>

function toggleComponent(header){

let box = header.closest(".component-box");

box.classList.toggle("active");

}

function enableEdit(btn){

    let form = btn.closest("form");

    let textarea = form.querySelector("textarea");

    if(textarea){
        textarea.removeAttribute("disabled");
        textarea.focus();
    }

    form.querySelectorAll('input[type="file"], input[type="checkbox"]').forEach(input=>{
        input.removeAttribute("disabled");
    });

    let saveBtn = form.querySelector(".save-btn");

    if(saveBtn){
        saveBtn.removeAttribute("disabled");
    }
}


function copyCode(btn){

let form = btn.closest("form");

let textarea = form.querySelector("textarea");

navigator.clipboard.writeText(textarea.value);

btn.innerHTML = "کپی شد";

setTimeout(()=>{

btn.innerHTML = "کپی";

},2000);

}

document.querySelectorAll("textarea").forEach(textarea=>{

let container = textarea.closest(".editor-layout");

if(!container) return;

let iframe = container.querySelector(".preview-frame");

if(!iframe) return;

function updatePreview(){

let code = textarea.value;

let form = textarea.closest("form");

let componentName =
form.querySelector('input[name="component_name"]').value;

code = code.replace(/{{image(\d+)}}/g,function(match,num){

    let images = componentImagesMap[componentName] || [];

    let index = parseInt(num) - 1;

    if(images[index]){
        return "/dashboard/components/" 
            + componentName + 
            "/images/" + 
            images[index];
    }

    return "";

});



iframe.srcdoc = code;

}

updatePreview();

textarea.addEventListener("input",updatePreview);

});



document.getElementById("searchBox").addEventListener("keyup",function(){

let value = this.value.toLowerCase();

document.querySelectorAll(".component-box").forEach(box=>{

let text = box.innerText.toLowerCase();

box.style.display = text.includes(value) ? "block" : "none";

});

});

let selectedImages = [];

const uploader = document.getElementById("imageUploader");
const preview = document.getElementById("imagePreview");

let files = [];

uploader.addEventListener("change", e => {

    const newFiles = Array.from(e.target.files);

    newFiles.forEach(file=>{
        files.push(file);
    });

    renderPreview();
});

function renderPreview(){

    preview.innerHTML="";

    const dataTransfer = new DataTransfer();

    files.forEach((file,index)=>{

        dataTransfer.items.add(file);

        const reader = new FileReader();

        reader.onload = function(e){

            const div = document.createElement("div");
            div.className="preview-item";

            div.innerHTML = `
            <div class="preview-remove" onclick="removeImage(${index})">×</div>
            <img src="${e.target.result}">
            <div class="preview-label">
            image${index+1}
            </div>
            `;

            preview.appendChild(div);
        }

        reader.readAsDataURL(file);

    });

    uploader.files = dataTransfer.files;
}

function removeImage(index){

    files.splice(index,1);

    renderPreview();
}


</script>

</body>

</html>
