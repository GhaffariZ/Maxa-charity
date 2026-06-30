<div class="medical-intake">

<div class="mi-container">

<div class="mi-card">

<h2 class="mi-title">تشکیل پرونده پزشکی اولیه</h2>

<p class="mi-subtitle">
برای بررسی پرونده و دریافت مشاوره تخصصی فرم زیر را تکمیل کنید.
</p>

<form>

<div class="mi-section">

<h3>اطلاعات فردی</h3>

<div class="mi-grid">

<div class="mi-group">
<label>نام و نام خانوادگی <span class="mi-required">*</span></label>
<input type="text" name="full_name" required>
</div>

<div class="mi-group">
<label>شماره موبایل <span class="mi-required">*</span></label>
<input type="tel" name="mobile" required>
</div>

<div class="mi-group">
<label>سن</label>
<input type="number" name="age">
</div>

<div class="mi-group">
<label>جنسیت</label>
<select name="gender">
<option value="">انتخاب کنید</option>
<option>مرد</option>
<option>زن</option>
</select>
</div>

<div class="mi-group">
<label>استان <span class="mi-required">*</span></label>
<input type="text" name="province" required>
</div>

<div class="mi-group">
<label>شهر محل سکونت <span class="mi-required">*</span></label>
<input type="text" name="city" required>
</div>

</div>
</div>


<div class="mi-section">

<h3>اطلاعات پزشکی</h3>

<div class="mi-grid">

<div class="mi-group">
<label>نوع سرطان</label>
<select name="cancer_type">
<option value="">انتخاب کنید</option>
<option>سرطان پستان</option>
<option>سرطان ریه</option>
<option>سرطان معده</option>
<option>سرطان روده</option>
<option>سرطان خون</option>
<option>سایر</option>
</select>
</div>

<div class="mi-group">
<label>وضعیت تشخیص</label>
<select name="diagnosis_status">
<option value="">انتخاب کنید</option>
<option>تشخیص قطعی</option>
<option>در حال بررسی</option>
<option>مشکوک</option>
</select>
</div>

</div>

<div class="mi-group">
<label>توضیحات</label>
<textarea name="description" rows="3"></textarea>
</div>

</div>


<div class="mi-section">

<h3>آپلود مدارک پزشکی</h3>

<label class="mi-upload">
انتخاب فایل
<input type="file" multiple accept="image/*" id="miFileInput">
</label>

<div class="mi-preview" id="miPreview"></div>

</div>


<div class="mi-group mi-checkbox">
<input type="checkbox" id="consent" required>
<label for="consent">
با ذخیره اطلاعات پزشکی خود موافق هستم
<span class="mi-required">*</span>
</label>
</div>

<button class="mi-submit">ثبت پرونده</button>

</form>

</div>
</div>
</div>

<style>
/* Self-hosted Vazirmatn variable font (reliable on the Iran network, no external CDN) */
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
       url('/webfont/Vazirmatn[wght].woff2') format('woff2');
  font-weight: 100 900;
  font-style: normal;
  font-display: swap;
}
  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }
.medical-intake{
font-family:'Vazirmatn', Tahoma, sans-serif;
}

.medical-intake .mi-container{
max-width:900px;
margin:auto;
padding:40px 20px;
}

.medical-intake .mi-card{
background:white;
padding:30px;
border-radius:12px;
box-shadow:0 6px 20px rgba(0,0,0,0.06);
}

.medical-intake .mi-title{
margin-bottom:5px;
}

.medical-intake .mi-subtitle{
color:#777;
margin-bottom:25px;
}

.medical-intake .mi-section{
margin-bottom:30px;
}

.medical-intake h3{
font-size:18px;
margin-bottom:15px;
border-right:4px solid #b57edc;
padding-right:8px;
}

.medical-intake .mi-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:15px;
}

@media(max-width:700px){
.medical-intake .mi-grid{
grid-template-columns:1fr;
}
}

.medical-intake .mi-group{
margin-bottom:15px;
}

.medical-intake label{
display:block;
margin-bottom:6px;
font-size:14px;
}

.medical-intake input,
.medical-intake select,
.medical-intake textarea{
width:100%;
padding:10px;
border-radius:8px;
border:1px solid #ddd;
font-family:inherit;
}

.medical-intake .mi-upload{
display:block;
border:2px dashed #ccc;
padding:20px;
text-align:center;
border-radius:10px;
cursor:pointer;
}

.medical-intake .mi-upload input{
display:none;
}

.medical-intake .mi-preview{
display:flex;
flex-wrap:wrap;
gap:10px;
margin-top:15px;
}

.medical-intake .mi-image-box{
position:relative;
}

.medical-intake .mi-image-box img{
width:90px;
height:90px;
object-fit:cover;
border-radius:8px;
border:1px solid #ddd;
}

.medical-intake .mi-remove{
position:absolute;
top:-6px;
left:-6px;
background:#ff4d4d;
color:white;
border-radius:50%;
width:20px;
height:20px;
font-size:12px;
display:flex;
align-items:center;
justify-content:center;
cursor:pointer;
}

.medical-intake .mi-submit{
width:100%;
padding:14px;
border:none;
border-radius:10px;
background:linear-gradient(45deg,#b57edc,#2bb3b1);
color:white;
font-size:15px;
cursor:pointer;
}

.medical-intake .mi-checkbox{
display:flex;
justify-content:center;
align-items:center;
gap:6px;
margin-top:10px;
}

.medical-intake .mi-checkbox input{
width:auto;
margin:0;
}

.medical-intake .mi-checkbox label{
margin:0;
cursor:pointer;
}
.medical-intake .mi-title{
text-align:center;
}

.medical-intake .mi-subtitle{
text-align:center;
max-width:600px;
margin:0 auto 25px auto;
}
/* ستاره قرمز فیلدهای ضروری */
.medical-intake .mi-required{
color:#e74c3c;
font-weight:bold;
margin-right:3px;
}

/* حالت خطا برای input و select و textarea */
.medical-intake input.mi-error,
.medical-intake select.mi-error,
.medical-intake textarea.mi-error{
border:2px solid #e74c3c !important;
background:#fff7cc;
}

/* اگر خطا مربوط به چک باکس باشد */
.medical-intake .mi-checkbox.mi-error{
border:2px solid #e74c3c;
background:#fff7cc;
padding:8px 12px;
border-radius:8px;
}

/* پیام خطا */
.medical-intake .mi-error-message{
color:#e74c3c;
font-size:13px;
margin-top:6px;
}

/* کمی فاصله بهتر بین label و input */
.medical-intake .mi-group label{
display:block;
margin-bottom:6px;
}

.medical-intake .mi-success-message{
font-family: inherit;
background:#e8f8f7;
border:2px solid #3fb6b2;
color:#333;
padding:30px;
border-radius:12px;
text-align:center;
font-size:18px;
line-height:1.9;
margin-top:20px;
}
.medical-intake .mi-success-message p {
    margin: 10px 0;
}
.medical-intake .mi-success-message strong {
    font-size: 20px;
    color: #3fb6b2;
}
</style>



<script>
document.addEventListener("DOMContentLoaded", function() {

    var token = localStorage.getItem('token');
    if (!token) {
        localStorage.setItem('redirect_to_patientintake', '1');
        window.location.href = '/benefactor-dashboard/login';
        return;
    }

    var form = document.querySelector(".medical-intake form");
    var fileInput = document.getElementById("miFileInput");
    var preview = document.getElementById("miPreview");
    var filesArray = [];

    if (fileInput) {
        fileInput.addEventListener("change", function() {

            for (var i = 0; i < this.files.length; i++) {
                filesArray.push(this.files[i]);
            }

            renderImages();
        });
    }

    function renderImages() {

        preview.innerHTML = "";

        filesArray.forEach(function(file, index){

            var reader = new FileReader();

            reader.onload = function(e){

                var box = document.createElement("div");
                box.className = "mi-image-box";

                var img = document.createElement("img");
                img.src = e.target.result;

                var remove = document.createElement("div");
                remove.className = "mi-remove";
                remove.setAttribute("data-index", index);
                remove.innerHTML = "×";

                box.appendChild(img);
                box.appendChild(remove);

                preview.appendChild(box);

            };

            reader.readAsDataURL(file);

        });

    }


    preview.addEventListener("click", function(e){

        if (e.target.classList.contains("mi-remove")){

            var idx = e.target.getAttribute("data-index");
            filesArray.splice(idx,1);
            renderImages();

        }

    });



    form.addEventListener("submit", function(e){

        e.preventDefault();

        var firstError = null;

        document.querySelectorAll(".mi-error-message").forEach(function(el){
            el.remove();
        });

        document.querySelectorAll(".mi-error").forEach(function(el){
            el.classList.remove("mi-error");
        });

        var requiredFields = form.querySelectorAll("[required]");

        requiredFields.forEach(function(field){

            var isInvalid = false;

            if (field.type === "checkbox"){

                if (!field.checked){

                    isInvalid = true;
                    field.closest(".mi-checkbox").classList.add("mi-error");

                }

            } else {

                if (field.value.trim() === ""){

                    isInvalid = true;
                    field.classList.add("mi-error");

                }

            }

            if (isInvalid){

                var msg = document.createElement("div");
                msg.className = "mi-error-message";
                msg.innerText = "این فیلد را حتما پر کنید";

                field.parentNode.appendChild(msg);

                if (!firstError){
                    firstError = field;
                }

            }

        });



        if (firstError){

            firstError.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });

        } else {

            var submitBtn = form.querySelector('.mi-submit');
            var originalText = submitBtn.innerText;
            submitBtn.innerText = 'در حال ارسال...';
            submitBtn.disabled = true;

            var formData = new FormData(form);
            filesArray.forEach(function(file) {
                formData.append('documents[]', file);
            });

            fetch('/api/medical-records', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                body: formData
            })
            .then(function(response) {
                if (!response.ok) {
                    if (response.status === 401) {
                        localStorage.setItem('redirect_to_patientintake', '1');
                        window.location.href = '/benefactor-dashboard/login';
                        throw new Error('Unauthorized');
                    }
                    throw new Error('خطا در ارسال اطلاعات');
                }
                return response.json();
            })
            .then(function(data) {
                var container = document.querySelector(".mi-card");
                form.style.display = "none";
    
                var success = document.createElement("div");
                success.className = "mi-success-message";
    
                success.innerHTML =
                "<p><strong>اطلاعات شما با موفقیت ثبت شد.</strong></p>" +
                "<p>همکاران ما تا 48 ساعت آینده با شما تماس خواهند گرفت.</p>" +
                "<p>با سپاس از شما</p>";
    
                container.appendChild(success);
    
                setTimeout(function(){
                    window.scrollTo({
                        top:0,
                        behavior:"smooth"
                    });
                },100);
            })
            .catch(function(error) {
                if (error.message !== 'Unauthorized') {
                    alert('مشکلی در ثبت اطلاعات پیش آمد. لطفا دوباره تلاش کنید.');
                    submitBtn.innerText = originalText;
                    submitBtn.disabled = false;
                }
            });

        }

    });

});
</script>

