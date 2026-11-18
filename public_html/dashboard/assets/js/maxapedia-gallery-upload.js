/* One file per request: independent of PHP's max_file_uploads and combined album size. */
window.maxapediaGalleryUpload = function (form, input, compress) {
  var busy = false;
  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (busy) return;
    var files = Array.from(input.files || []);
    if (!files.length) return;
    if (files.length > 100) {
      alert('در هر نوبت حداکثر ۱۰۰ تصویر انتخاب کنید.');
      return;
    }
    busy = true;
    var button = document.getElementById('galleryUploadSubmitBtn');
    var box = document.getElementById('galleryCompBox');
    var status = document.getElementById('galleryCompStatus');
    var bar = document.getElementById('galleryCompBar');
    var percent = document.getElementById('galleryCompPercent');
    var summary = document.getElementById('gallerySelectedSummary');
    var failures = [];
    var remaining = [];
    var uploaded = 0;
    var uncertain = false;
    button.disabled = true;
    input.disabled = true;
    box.style.display = 'flex';
    status.setAttribute('role', 'status');
    // An input named "action" shadows form.action in real HTML forms.
    var formAction = form.getAttribute('action') || window.location.href;
    var url = new URL(formAction, window.location.href);
    url.searchParams.set('upload_response', 'json');
    // Capture CSRF and album fields once; never include the original multi-file input.
    var fields = Array.from(form.querySelectorAll('input[type="hidden"]')).map(function (field) {
      return [field.name, field.value];
    });
    try {
      for (var i = 0; i < files.length; i++) {
        status.textContent = 'آماده‌سازی و آپلود تصویر ' + (i + 1) + ' از ' + files.length;
        var file = files[i];
        try {
          var optimized = await compress(file);
          var body = new FormData();
          fields.forEach(function (field) { body.append(field[0], field[1]); });
          body.append('gallery_photos[]', optimized, optimized.name);
          var response = await fetch(url.href, {
            method: 'POST', body: body, credentials: 'same-origin', headers: {Accept: 'application/json'}
          });
          var result;
          try { result = await response.json(); }
          catch (error) {
            var detail = 'پاسخ معتبر از سرور دریافت نشد (HTTP ' + response.status + ').';
            if (response.status === 404) detail += ' آدرس یا فایل پردازش آپلود روی هاست موجود نیست.';
            else if (response.status === 413) detail += ' حجم تصویر از سقف مجاز سرور بیشتر است.';
            else if (response.status === 401 || response.status === 419 || (response.redirected && /login\.php/.test(response.url))) {
              detail += ' نشست ورود منقضی شده است؛ دوباره وارد پنل شوید.';
            }
            throw new Error(detail);
          }
          if (response.ok && result.ok && result.uploaded === 1) {
            uploaded++;
          } else if (result.ok === false) {
            remaining.push(file);
            failures.push(file.name + ': ' + (result.error || (result.errors || []).map(function (item) { return item.error; }).join('، ') || 'ذخیره نشد'));
          } else {
            throw new Error('نتیجهٔ ذخیره‌سازی مشخص نیست.');
          }
        } catch (error) {
          // A lost response can follow a successful save. Do not retry automatically.
          uncertain = true;
          remaining.push.apply(remaining, files.slice(i));
          failures.push(file.name + ': ' + error.message + ' ادامهٔ ارسال متوقف شد؛ پیش از تلاش مجدد، آلبوم را بررسی کنید.');
          break;
        }
        var progress = Math.round((i + 1) / files.length * 100);
        bar.style.width = progress + '%';
        percent.textContent = progress + '٪';
      }
      if (uploaded === files.length) {
        var destination = new URL(formAction, window.location.href);
        destination.searchParams.set('album_id', fields.find(function (field) { return field[0] === 'album_id'; })[1]);
        destination.searchParams.set('msg', 'photos_uploaded');
        window.location.assign(destination.href);
        return;
      }
      var pending = new DataTransfer();
      remaining.forEach(function (file) { pending.items.add(file); });
      input.files = pending.files;
      summary.textContent = uploaded + ' تصویر ذخیره شد؛ ' + remaining.length + ' تصویر باقی مانده است.';
      status.textContent = failures.join('\n');
      status.style.whiteSpace = 'pre-wrap';
      if (uncertain) {
        // Force a refresh before a potentially duplicate retry.
        button.textContent = 'برای بررسی نتیجه، صفحه را تازه‌سازی کنید';
      }
    } finally {
      busy = uncertain || uploaded === files.length;
      input.disabled = busy;
      button.disabled = busy;
    }
  });
};
