<div class="medical-intake">

<div class="mi-container">

<div class="mi-card">

<h2 class="mi-title">تشکیل پرونده پزشکی اولیه</h2>

<p class="mi-subtitle">
برای بررسی پرونده و دریافت مشاوره تخصصی فرم زیر را تکمیل کنید.
</p>

<form id="medicalIntakeForm">

<div class="mi-section">

<h3>اطلاعات فردی</h3>

<div class="mi-grid">

<div class="mi-group">
<label>نام و نام خانوادگی <span class="mi-required">*</span></label>
<input type="text" name="full_name" autocomplete="name" required>
</div>

<div class="mi-group">
<label>شماره موبایل <span class="mi-required">*</span></label>
<input type="tel" name="phone" inputmode="tel" autocomplete="tel" required>
</div>

<div class="mi-identity-date-row">
<div class="mi-group mi-identity-fields">
<label for="miIdentity"><span id="miIdentityLabel">کد ملی</span> <span class="mi-required">*</span></label>
<input type="text" id="miIdentity" name="identity_code" inputmode="numeric" dir="ltr" maxlength="10" required aria-describedby="miIdentityHint">
<div class="mi-checkbox mi-nationality">
<input type="checkbox" name="is_foreign" id="miForeign" value="1">
<label for="miForeign">اتباع هستم</label>
</div>
<small id="miIdentityHint">کد ملی ۱۰ رقمی خود را وارد کنید.</small>
</div>

<fieldset class="mi-birthdate">
<legend>تاریخ تولد</legend>
<div class="mi-date-grid">
<div><select id="miBirthDay" name="birth_day"><option value="">روز</option></select></div>
<div><select id="miBirthMonth" name="birth_month"><option value="">ماه</option></select></div>
<div><select id="miBirthYear" name="birth_year"><option value="">سال</option></select></div>
</div>
</fieldset>
</div>

<div class="mi-group">
<label>جنسیت</label>
<select name="gender">
<option value="unspecified">انتخاب کنید</option>
<option value="male">مرد</option>
<option value="female">زن</option>
</select>
</div>

<div class="mi-group">
<label>استان <span class="mi-required">*</span></label>
<input type="text" name="province" autocomplete="address-level1" required>
</div>

<div class="mi-group">
<label>شهر محل سکونت <span class="mi-required">*</span></label>
<input type="text" name="city" autocomplete="address-level2" required>
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
<option value="سرطان پستان">سرطان پستان</option>
<option value="سرطان ریه">سرطان ریه</option>
<option value="سرطان معده">سرطان معده</option>
<option value="سرطان روده">سرطان روده</option>
<option value="سرطان خون">سرطان خون</option>
<option value="سایر">سایر</option>
</select>
</div>

<div class="mi-group">
<label>وضعیت تشخیص</label>
<select name="diagnosis_status">
<option value="">انتخاب کنید</option>
<option value="تشخیص قطعی">تشخیص قطعی</option>
<option value="در حال بررسی">در حال بررسی</option>
<option value="مشکوک">مشکوک</option>
</select>
</div>

</div>

<div class="mi-group">
<label>توضیحات</label>
<textarea name="description" rows="3" maxlength="5000"></textarea>
</div>

</div>


<div class="mi-section">

<h3>آپلود مدارک پزشکی</h3>

<label class="mi-upload">
انتخاب فایل
<input type="file" name="documents[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf" id="miFileInput">
</label>

<div class="mi-preview" id="miPreview"></div>

</div>


<div class="mi-group mi-checkbox">
<input type="checkbox" name="consent" id="consent" value="1" required>
<label for="consent">
با ذخیره اطلاعات پزشکی خود موافق هستم
<span class="mi-required">*</span>
</label>
</div>

<button type="submit" class="mi-submit">ثبت پرونده</button>

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
background-color:#fff;
color:#222;
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
.medical-intake .mi-form-message{
margin:12px 0;
padding:10px 12px;
border-radius:8px;
background:#fff1f1;
color:#b42318;
font-size:13px;
}
.medical-intake .mi-nationality{justify-content:flex-start;}
.medical-intake .mi-identity-date-row{grid-column:1/-1;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:15px;align-items:start;}
.medical-intake .mi-identity-fields{min-width:0;}
.medical-intake .mi-birthdate{border:0;padding:0;margin:0;min-width:0;}
.medical-intake .mi-birthdate legend{font-size:14px;margin-bottom:6px;}
.medical-intake .mi-date-grid{display:grid;grid-template-columns:0.8fr 1.4fr 1fr;gap:8px;}
.medical-intake .mi-date-grid > div{min-width:0;}
.medical-intake .mi-date-grid select{padding:10px 5px;}
.medical-intake small{display:block;color:#555;line-height:1.8;}
.medical-intake input:focus-visible,
.medical-intake select:focus-visible{outline:2px solid #258481;outline-offset:2px;}
</style>



<script>
document.addEventListener("DOMContentLoaded", function() {
    var form = document.getElementById("medicalIntakeForm");
    var fileInput = document.getElementById("miFileInput");
    var preview = document.getElementById("miPreview");
    var submitButton = form.querySelector(".mi-submit");
    var filesArray = [];
    var identity = document.getElementById("miIdentity");
    var foreign = document.getElementById("miForeign");
    var birthDay = document.getElementById("miBirthDay");
    var birthMonth = document.getElementById("miBirthMonth");
    var birthYear = document.getElementById("miBirthYear");
    var today = new Intl.DateTimeFormat("en-US-u-ca-persian", {
        timeZone: "Asia/Tehran", year: "numeric", month: "numeric", day: "numeric"
    }).formatToParts(new Date()).reduce(function(parts, part) {
        parts[part.type] = Number(part.value);
        return parts;
    }, {});
    var months = ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"];
    months.forEach(function(month, index) { birthMonth.add(new Option(month, index + 1)); });
    for (var year = today.year; year >= 1280; year--) {
        birthYear.add(new Option(year.toLocaleString("fa-IR", {useGrouping: false}), year));
    }

    function normalizeDigits(value) {
        return value.replace(/[۰-۹]/g, function(d) { return "۰۱۲۳۴۵۶۷۸۹".indexOf(d); })
            .replace(/[٠-٩]/g, function(d) { return "٠١٢٣٤٥٦٧٨٩".indexOf(d); }).trim();
    }

    function validateIdentity() {
        identity.value = normalizeDigits(identity.value);
        var code = identity.value;
        var valid;
        if (foreign.checked) {
            valid = /^[0-9]{12}$/.test(code) && !/^([0-9])\1+$/.test(code);
        } else {
            var sum = 0;
            for (var i = 0; i < 9; i++) sum += Number(code[i]) * (10 - i);
            var remainder = sum % 11;
            valid = /^[0-9]{10}$/.test(code) && !/^([0-9])\1+$/.test(code) &&
                Number(code[9]) === (remainder < 2 ? remainder : 11 - remainder);
        }
        identity.setCustomValidity(valid || code === "" ? "" : (foreign.checked ?
            "کد فراگیر باید ۱۲ رقم و غیرتکراری باشد." : "کد ملی معتبر نیست؛ کد ۱۰ رقمی را بررسی کنید."));
    }
    identity.addEventListener("input", validateIdentity);
    foreign.addEventListener("change", function() {
        document.getElementById("miIdentityLabel").textContent = foreign.checked ? "کد فراگیر" : "کد ملی";
        document.getElementById("miIdentityHint").textContent = foreign.checked ?
            "کد فراگیر ۱۲ رقمی خود را وارد کنید." : "کد ملی ۱۰ رقمی خود را وارد کنید.";
        identity.maxLength = foreign.checked ? 12 : 10;
        validateIdentity();
    });

    // Borkowski's 1210–1634 interval; birth years are restricted to 1280–1500.
    function isLeapYear(year) { return (((year - 1210 + 1) % 33) - 1) % 4 === 0; }
    function updateBirthDate() {
        var year = Number(birthYear.value);
        var month = Number(birthMonth.value);
        var selectedDay = birthDay.value;
        var days = !month || month <= 6 ? 31 : (month < 12 || !year || isLeapYear(year) ? 30 : 29);
        birthDay.length = 1;
        for (var day = 1; day <= days; day++) {
            birthDay.add(new Option(day.toLocaleString("fa-IR"), day));
        }
        birthDay.value = Number(selectedDay) <= days ? selectedDay : "";
        var any = birthDay.value || birthMonth.value || birthYear.value;
        [birthDay, birthMonth, birthYear].forEach(function(field) { field.required = Boolean(any); });
        var future = year && month && birthDay.value &&
            (year > today.year || (year === today.year && (month > today.month ||
                (month === today.month && Number(birthDay.value) > today.day))));
        birthDay.setCustomValidity(future ? "تاریخ تولد نمی‌تواند در آینده باشد." : "");
    }
    [birthDay, birthMonth, birthYear].forEach(function(field) { field.addEventListener("change", updateBirthDate); });
    updateBirthDate();

    fileInput.addEventListener("change", function() {
        filesArray = Array.prototype.slice.call(this.files, 0, 5);
        renderFiles();
    });

    function renderFiles() {
        preview.innerHTML = "";
        filesArray.forEach(function(file, index){
            var box = document.createElement("div");
            box.className = "mi-image-box";
            if (file.type.indexOf("image/") === 0) {
              var reader = new FileReader();
              reader.onload = function(e){
                var img = document.createElement("img");
                img.src = e.target.result;
                box.insertBefore(img, box.firstChild);
              };
              reader.readAsDataURL(file);
            } else {
              box.textContent = file.name;
            }
            var remove = document.createElement("button");
            remove.type = "button";
            remove.className = "mi-remove";
            remove.setAttribute("data-index", index);
            remove.setAttribute("aria-label", "حذف فایل");
            remove.textContent = "×";
            box.appendChild(remove);
            preview.appendChild(box);
        });
    }

    preview.addEventListener("click", function(e){
        if (e.target.classList.contains("mi-remove")){
            var idx = Number(e.target.getAttribute("data-index"));
            filesArray.splice(idx,1);
            renderFiles();
        }
    });

    form.addEventListener("submit", async function(e){
        e.preventDefault();
        validateIdentity();
        updateBirthDate();
        if (!form.reportValidity()) return;
        var firstError = null;
        form.querySelectorAll(".mi-error-message, .mi-form-message").forEach(function(el){
            el.remove();
        });
        form.querySelectorAll(".mi-error").forEach(function(el){
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
            } else if (field.value.trim() === ""){
                isInvalid = true;
                field.classList.add("mi-error");
            }
            if (isInvalid){
                var msg = document.createElement("div");
                msg.className = "mi-error-message";
                msg.innerText = "این فیلد را حتما پر کنید";
                field.parentNode.appendChild(msg);
                if (!firstError) firstError = field;
            }
        });
        if (firstError){
            firstError.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });
            return;
        }

        var token = localStorage.getItem("maksa_access_token");
        await saveRecord(token);
    });

    async function saveRecord(token) {
        setBusy(true, "در حال ثبت پرونده...");
        try {
            var data = new FormData(form);
            data.delete("documents[]");
            filesArray.forEach(function(file){ data.append("documents[]", file); });

            var headers = token ? {"Authorization": "Bearer " + token} : {};
            var response = await fetch("/api/medical-records/intake", {
                method: "POST",
                headers: headers,
                credentials: "include",
                body: data
            });
            var result = await response.json();

            if (!response.ok) {
                var fields = result.error && result.error.fields;
                throw new Error(fields ? Object.values(fields).join(" — ") :
                    result.error && result.error.message || "ثبت پرونده ناموفق بود.");
            }

            if (result.data.access_token) {
                localStorage.setItem("maksa_access_token", result.data.access_token);
            }
            localStorage.setItem("maksa_benefactor_user", JSON.stringify(result.data.user));

            form.style.display = "none";
            var success = document.createElement("div");
            success.className = "mi-success-message";
            success.innerHTML = "<p><strong>پرونده شما با موفقیت ثبت شد.</strong></p>" +
                "<p>شماره پرونده: " + Number(result.data.record_id).toLocaleString("fa-IR") + "</p>" +
                "<p>همکاران ما تا ۴۸ ساعت آینده با شما تماس خواهند گرفت.</p>";
            document.querySelector(".mi-card").appendChild(success);
            success.scrollIntoView({behavior:"smooth", block:"center"});
        } catch (error) {
            showError(error.message);
        } finally {
            setBusy(false);
        }
    }

    function setBusy(busy, text) {
        submitButton.disabled = busy;
        if (busy) {
            submitButton.dataset.label = submitButton.textContent;
            submitButton.textContent = text;
        } else if (submitButton.dataset.label) {
            submitButton.textContent = submitButton.dataset.label;
            delete submitButton.dataset.label;
        }
    }

    function showError(message) {
        var error = document.createElement("div");
        error.className = "mi-form-message";
        error.textContent = message || "خطایی رخ داد. لطفاً دوباره تلاش کنید.";
        submitButton.before(error);
        error.scrollIntoView({behavior:"smooth", block:"center"});
    }
});
</script>

