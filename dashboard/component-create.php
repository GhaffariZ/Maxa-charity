<?php

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
<html lang="fa">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>مدیریت کامپوننت</title>

<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- تم دارک/لایت از «داشبورد مدیریت» تبعیت می‌کند (کلید مشترک: maxa-theme) -->
<script>
(function(){
  function applyMaxaTheme(){
    var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
    if(d) document.documentElement.setAttribute('data-theme','dark'); else document.documentElement.removeAttribute('data-theme');
  }
  applyMaxaTheme();
  window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });
})();
</script>

<style>

:root{
--bg:#f4f7fb;
--bg-soft:#eef3f8;
--card:#ffffff;
--card-2:#f8fbff;
--border:#e2e8f0;
--border-strong:#cbd5e1;
--text:#0f172a;
--text-soft:#475569;
--muted:#64748b;
--shadow-sm:0 4px 14px rgba(15,23,42,.04);
--shadow-md:0 12px 30px rgba(15,23,42,.07);
--shadow-lg:0 18px 45px rgba(15,23,42,.10);

--primary:#4f46e5;
--primary-hover:#4338ca;
--primary-soft:#eef2ff;

--success:#16a34a;
--success-soft:#f0fdf4;

--danger:#dc2626;
--danger-soft:#fef2f2;

--warning:#d97706;
--warning-soft:#fff7ed;

--info:#0284c7;
--info-soft:#f0f9ff;

--gray:#64748b;
--radius:22px;
--radius-lg:20px;
--radius-md:16px;
--radius-sm:12px;
}


*{
box-sizing:border-box;
}

body{
font-family:"Vazirmatn","IRANSans","Tahoma",sans-serif;
background:
radial-gradient(circle at top right, #ffffff 0%, #f7fbff 28%, #f4f7fb 65%);
direction:rtl;
padding:36px;
margin:0;
color:var(--text);
font-size:15px;
line-height:1.9;
-webkit-font-smoothing:antialiased;
}

.container{
max-width:1500px;
margin:auto;
}

.card{
background:var(--card);
padding:32px;
border-radius:28px;
margin-bottom:36px;
box-shadow:var(--shadow-md);
border:1px solid var(--border);
}


h2{
margin:0 0 26px;
font-size:30px;
font-weight:900;
letter-spacing:-.6px;
color:var(--text);
}

.group-title{
font-size:24px;
font-weight:900;
margin-top:50px;
margin-bottom:18px;
color:var(--text);
padding-right:14px;
border-right:5px solid var(--primary);
letter-spacing:-.4px;
}


input,
textarea,
select{
width:100%;
padding:14px 16px;
border-radius:14px;
border:1px solid var(--border);
margin-top:10px;
margin-bottom:20px;
font-size:14px;
background:#fff;
transition:.22s ease;
font-family:inherit;
color:var(--text);
box-shadow:none;
}

input::placeholder,
textarea::placeholder{
color:#94a3b8;
}

input:hover,
textarea:hover,
select:hover{
border-color:var(--border-strong);
}

input:focus,
textarea:focus,
select:focus{
outline:none;
border-color:var(--primary);
box-shadow:0 0 0 4px rgba(79,70,229,.10);
}

textarea{
height:520px;
direction:ltr;
resize:vertical;
line-height:1.95;
font-size:13px;
font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;
background:#fcfdff;
}


button{
border:none;
padding:12px 18px;
border-radius:12px;
cursor:pointer;
font-family:inherit;
font-size:13px;
font-weight:800;
transition:.2s ease;
display:inline-flex;
align-items:center;
justify-content:center;
gap:8px;
box-shadow:var(--shadow-sm);
}

button:hover{
transform:translateY(-1px);
}

button:active{
transform:translateY(0);
}

.btn_main{
background:linear-gradient(135deg,var(--primary),#6366f1);
color:#fff;
}

.edit-btn{
background:linear-gradient(135deg,#0ea5e9,#0284c7);
color:#fff;
}

.save-btn{
background:linear-gradient(135deg,#16a34a,#15803d);
color:#fff;
}

.copy-btn{
background:linear-gradient(135deg,#64748b,#475569);
color:#fff;
}

.delete-btn{
background:linear-gradient(135deg,#ef4444,#dc2626);
color:#fff;
}


.edit-btn{
background:#0ea5e9;
color:white;
}

.save-btn{
background:var(--success);
color:white;
}

.delete-btn{
background:var(--danger);
color:white;
}

.copy-btn{
background:var(--gray);
color:white;
}

.component-box{
background:var(--card);
border-radius:24px;
margin-bottom:22px;
box-shadow:var(--shadow-sm);
border:1px solid var(--border);
overflow:hidden;
transition:.25s ease;
}

.component-box:hover{
transform:translateY(-2px);
box-shadow:var(--shadow-lg);
}

.component-header{
display:flex;
justify-content:space-between;
align-items:center;
padding:22px 26px;
cursor:pointer;
background:linear-gradient(to left,#ffffff,#f8fbff);
border-bottom:1px solid rgba(226,232,240,.6);
}

.component-name{
font-size:18px;
font-weight:900;
letter-spacing:-.3px;
color:var(--text);
}

.tag{
color:white;
padding:7px 14px;
border-radius:999px;
font-size:12px;
margin-right:12px;
font-weight:800;
box-shadow:0 4px 12px rgba(0,0,0,.08);
}

.component-content{
padding:24px 26px 28px;
display:none;
animation:fade .25s ease;
}

.component-box.active .component-content{
display:block;
}

.toggle-icon{
font-size:22px;
transition:.25s;
color:var(--muted);
font-weight:bold;
}

.component-box.active .toggle-icon{
transform:rotate(180deg);
}


#imageList{
margin-top:10px;
display:flex;
flex-direction:column;
gap:8px;
}

.image-item{
background:#f1f5f9;
padding:8px 12px;
border-radius:10px;
font-size:13px;
display:flex;
justify-content:space-between;
align-items:center;
}

.image-item span{
color:#334155;
}

.image-remove{
cursor:pointer;
color:#dc2626;
font-weight:bold;
}
 
/* ظرف اصلی ویرایشگر */
.editor-layout{
display:grid;
grid-template-columns:1fr 1fr;
gap:24px;
margin-top:20px;
align-items:start;
direction:ltr;
}

.editor-panel,
.preview-panel{
background:linear-gradient(to bottom,#fbfdff,#f8fbff);
padding:18px;
border-radius:20px;
border:1px solid var(--border);
box-shadow:inset 0 1px 0 rgba(255,255,255,.6);
min-width:0;
}

.panel-title{
font-size:14px;
font-weight:900;
margin-bottom:14px;
color:var(--text-soft);
direction:rtl;
display:flex;
align-items:center;
gap:8px;
}

.editor-panel textarea{
width:100%;
height:520px;
margin:0;
border-radius:16px;
border:1px solid var(--border);
background:#fcfdff;
}


.actions{
display:flex;
gap:10px;
flex-wrap:wrap;
margin-top:24px;
justify-content:flex-end;
padding-top:8px;
}

.group-title{
font-size:27px;
font-weight:900;
margin-top:50px;
margin-bottom:22px;
color:#0f172a;
padding-right:14px;
border-right:6px solid var(--primary);
letter-spacing:-.5px;
}

#searchBox{
margin-bottom:34px;
background:#fff;
font-size:15px;
box-shadow:var(--shadow-sm);
}

.toggle-icon{
font-size:22px;
transition:.25s;
color:var(--muted);
font-weight:bold;
}

.component-box.active .toggle-icon{
transform:rotate(180deg);
}

#uploadBox{
border:2px dashed #cfd8e3;
border-radius:18px;
padding:34px;
text-align:center;
cursor:pointer;
background:linear-gradient(to bottom,#fbfdff,#f8fbff);
transition:.22s ease;
position:relative;
}

#uploadBox:hover{
border-color:var(--primary);
background:var(--primary-soft);
}

#uploadBox input{
position:absolute;
inset:0;
opacity:0;
cursor:pointer;
}

.upload-text{
font-size:14px;
font-weight:700;
color:var(--text-soft);
}

.upload-help{
font-size:13px;
color:var(--muted);
margin-top:-8px;
margin-bottom:20px;
line-height:2;
}

#imagePreview{
margin-top:20px;
display:grid;
grid-template-columns:repeat(auto-fill,minmax(120px,1fr));
gap:15px;
}

.preview-item{
position:relative;
background:white;
border-radius:14px;
overflow:hidden;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.preview-item img{
width:100%;
height:100px;
object-fit:cover;
display:block;
}

.preview-label{
font-size:12px;
padding:6px;
text-align:center;
background:#f1f5f9;
}

.preview-remove{
position:absolute;
top:6px;
left:6px;
background:#dc2626;
color:white;
width:20px;
height:20px;
border-radius:50%;
font-size:12px;
display:flex;
align-items:center;
justify-content:center;
cursor:pointer;
}
@keyframes fade{
from{
opacity:0;
transform:translateY(-6px);
}
to{
opacity:1;
transform:translateY(0);
}
}

@media(max-width:1100px){
.editor-layout{
grid-template-columns:1fr;
}
}

@media(max-width:768px){
body{
padding:18px;
}

.card{
padding:22px;
border-radius:20px;
}

.component-header{
flex-direction:column;
align-items:flex-start;
gap:12px;
}

.actions{
justify-content:center;
}

h2{
font-size:24px;
}

.group-title{
font-size:21px;
}

.editor-panel textarea,
.preview-frame{
height:400px;
}

.existing-images{
grid-template-columns:repeat(auto-fill,minmax(140px,1fr));
}
}
    .upload-help{
        font-size:13px;
        color:#64748b;
        margin-top:-10px;
        margin-bottom:20px;
        line-height:2;
    }
}

.component-header{
flex-direction:column;
align-items:flex-start;
gap:15px;
}


h2{
font-size:24px;
}

.group-title{
font-size:22px;
}


.editor-layout{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:24px;
    margin-top:20px;
    align-items:start;
    direction:ltr;
}

.editor-panel,
.preview-panel{
    background:#f9fbff;
    padding:18px;
    border-radius:18px;
    border:1px solid #e5eaf3;
    min-width:0;
}

.panel-title{
    font-size:15px;
    font-weight:800;
    margin-bottom:15px;
    color:#334155;
    direction:rtl;
}

/* باکس کد */
.editor-panel textarea{
    width:100%;
    height:520px;
    margin:0;
    direction:ltr;
    resize:vertical;
    line-height:2;
    font-size:13px;
    font-family:Consolas,monospace;
    background:#fbfcfe;
    border:1px solid #dbe3ef;
    border-radius:14px;
}

/* باکس نمایش - دقیقاً هم‌ارتفاع با textarea */
.preview-frame{
width:100%;
height:520px;
display:block;
border:none;
border-radius:16px;
background:white;
box-shadow:inset 0 0 0 1px var(--border);
}

.upload-help{
font-size:13px;
color:#64748b;
margin-top:-10px;
margin-bottom:20px;
line-height:2;
}

}

.component-images-editor{
margin-top:24px;
background:linear-gradient(to bottom,#fffdf7,#fffaf0);
border:1px solid #f3e8c7;
border-radius:20px;
padding:20px;
box-shadow:inset 0 1px 0 rgba(255,255,255,.7);
}

.existing-images{
display:grid;
grid-template-columns:repeat(auto-fill,minmax(170px,1fr));
gap:16px;
margin-bottom:22px;
}

.existing-image-item{
background:#fff;
border:1px solid #efe2bd;
border-radius:18px;
overflow:hidden;
box-shadow:0 6px 18px rgba(15,23,42,.05);
transition:.2s ease;
}

.existing-image-item:hover{
transform:translateY(-2px);
box-shadow:0 10px 24px rgba(15,23,42,.08);
}

.existing-image-item img{
width:100%;
height:130px;
object-fit:cover;
display:block;
background:#f8fafc;
}

.existing-image-info{
padding:12px;
display:flex;
flex-direction:column;
gap:5px;
direction:ltr;
text-align:left;
}

.existing-image-info span{
font-size:13px;
font-weight:900;
color:#b45309;
}

.existing-image-info small{
font-size:11px;
color:#78716c;
word-break:break-all;
line-height:1.8;
}

.delete-image-label{
display:flex;
align-items:center;
gap:8px;
padding:10px 12px;
background:#fff1f2;
color:#dc2626;
font-size:13px;
font-weight:800;
cursor:pointer;
border-top:1px solid #ffe4e6;
}

.delete-image-label input{
width:auto;
margin:0;
accent-color:#dc2626;
}

.add-images-box{
border-top:1px dashed #e7c88b;
padding-top:18px;
margin-top:6px;
}

.add-images-box label{
display:block;
font-size:14px;
font-weight:900;
margin-bottom:8px;
color:#92400e;
}

.no-image{
background:#fff;
color:#78716c;
padding:18px;
border-radius:14px;
font-size:14px;
grid-column:1/-1;
border:1px dashed #f5d48d;
text-align:center;
}


@media(max-width:900px){

.editor-layout{
    flex-direction:column;
    align-items:center;
}

.editor-panel,
.preview-panel{
    width:100%;
}

/* هماهنگ کردن ارتفاع Textarea و Iframe */
.editor-panel textarea,
.preview-frame {
    width: 100%;
    height: 650px; /* ارتفاع هر دو باکس دقیقاً یکسان */
    border: 1px solid #ddd;
    border-radius: 8px;
    display: block;
}

}
/* حذف استایل‌های اضافی iframe */
.preview-frame {
    background: #fff;
    box-shadow: none; /* حذف سایه‌های قبلی گوشی */
}

/* ===== دارک‌مود: تبعیت از «داشبورد مدیریت» (کلید مشترک maxa-theme) — افزوده‌شده، استایل‌های روشن دست‌نخورده ===== */
:root[data-theme="dark"]{
  --bg:#0f1518;
  --card:#19232a;
  --border:#2a343a;
  --text:#e7ecee;
  --muted:#8e989d;
  --shadow:0 14px 38px rgba(0,0,0,.5);
  color-scheme:dark;
}
[data-theme="dark"] body{background:#0f1518}
[data-theme="dark"] h2,
[data-theme="dark"] .component-name,
[data-theme="dark"] .group-title{color:var(--text)}
[data-theme="dark"] input,
[data-theme="dark"] textarea,
[data-theme="dark"] select,
[data-theme="dark"] #searchBox{background:#0f1518;border-color:var(--border);color:var(--text)}
[data-theme="dark"] textarea{background:#11181d}
[data-theme="dark"] input::placeholder,
[data-theme="dark"] textarea::placeholder{color:#7c8589}
[data-theme="dark"] .component-box{background:var(--card)}
[data-theme="dark"] .component-header{background:linear-gradient(to left,#1d2932,#19232a)}
[data-theme="dark"] .component-box:hover{box-shadow:0 18px 40px rgba(0,0,0,.55)}
[data-theme="dark"] .image-item{background:#0f1518}
[data-theme="dark"] .image-item span{color:#cfd6da}
[data-theme="dark"] .editor-panel,
[data-theme="dark"] .preview-panel{background:#0f1518;border-color:var(--border)}
[data-theme="dark"] .panel-title{color:#cfd6da}
[data-theme="dark"] #uploadBox{border-color:#39454c;background:#0f1518}
[data-theme="dark"] #uploadBox:hover{border-color:var(--primary);background:#141d22}
[data-theme="dark"] .upload-text,
[data-theme="dark"] .upload-help{color:var(--muted)}
[data-theme="dark"] .preview-item{background:var(--card)}
[data-theme="dark"] .preview-label{background:#0f1518;color:#cfd6da}
[data-theme="dark"] .toggle-icon{color:var(--muted)}
[data-theme="dark"] .component-images-editor{background:#0f1518;border-color:var(--border);}
[data-theme="dark"] .existing-image-item{background:var(--card);border-color:var(--border);}
[data-theme="dark"] .existing-image-info small{color:var(--muted);}
[data-theme="dark"] .delete-image-label{background:#2a1114;color:#f87171;}
[data-theme="dark"] .add-images-box{border-top-color:#39454c;}
[data-theme="dark"] .add-images-box label{color:#cfd6da;}
[data-theme="dark"] .no-image{background:#11181d;color:var(--muted);}
[data-theme="dark"] .editor-panel,
[data-theme="dark"] .preview-panel {background: #1e293b;border-color: #334155;}
[data-theme="dark"] .editor-panel textarea,
[data-theme="dark"] .preview-frame {border-color: #475569;}
[data-theme="dark"] .component-images-editor{background:#2a2116;border-color:#5b4630;}
[data-theme="dark"] .existing-image-item{background:var(--card);border-color:#4b5563;}
[data-theme="dark"] .existing-image-info span{color:#fbbf24;}
[data-theme="dark"] .existing-image-info small{color:var(--muted);}
[data-theme="dark"] .add-images-box{border-top-color:#5b4630;}
[data-theme="dark"] .add-images-box label{color:#fcd34d;}
[data-theme="dark"] .no-image{background:#11181d;color:var(--muted);border-color:#5b4630;}
</style>

</head>

<body>

<div class="container">

<div class="card">

<h2>ایجاد کامپوننت</h2>

<form action="components-save.php" method="POST" enctype="multipart/form-data">

<input type="text" name="component_name" placeholder="نام کامپوننت" required>

<select name="component_tag" required>

<option value="">انتخاب تگ</option>

<?php foreach($tags as $tag){ ?>

<option value="<?= $tag ?>"><?= $tag ?></option>

<?php } ?>

</select>

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


<button class="btn_main">ثبت</button>

</form>

</div>

<input type="text" id="searchBox" placeholder="جستجوی کامپوننت">

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

<button type="button" class="edit-btn" onclick="enableEdit(this)">
ویرایش
</button>

<button class="save-btn">
ذخیره
</button>

<button type="button" class="copy-btn" onclick="copyCode(this)">
کپی
</button>

<button
type="submit"
formaction="component-delete.php"
class="delete-btn"
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
