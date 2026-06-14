<?php
require_once __DIR__ . '/_guard.php';
dash_require('courses');
/* ============================================================================
 *  ساخت دوره‌ی جدید — آکادمی مکسا
 *  صفحه‌ی کامل ساخت دوره با سرفصل‌بندِ تعاملی (ویدئو / متن / تصویر)
 *  الهام‌گرفته از بهترین نمونه‌های جهانی (Udemy / Coursera / Teachable)
 * ========================================================================== */
require __DIR__ . '/course-db.php';
require __DIR__ . '/course-ui.php';

/* ---------- حالت ویرایش ---------- */
$editId  = (int)($_GET['edit'] ?? $_POST['edit_id'] ?? 0);
$editing = $editId ? load_course_full($pdo, $coursesSchemaReady, $editId) : null;
if (!$editing) $editId = 0;

/* ---------- ذخیره‌سازی فرم ---------- */
$saved = false; $savedMsg = ''; $savedErr = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim((string)($_POST['title'] ?? ''));
  if ($title === '') {
    $savedErr = 'عنوان دوره الزامی است.';
  } else {
    $curriculum = json_decode((string)($_POST['curriculum'] ?? '[]'), true) ?: [];
    $status = ($_POST['action'] ?? 'draft') === 'publish' ? 'published' : 'draft';
    if ($pdo && $coursesSchemaReady) {
      try {
        $pdo->beginTransaction();
        if ($editId) {
          q($pdo, "UPDATE courses SET
              title=?,subtitle=?,description=?,category=?,level=?,language=?,price=?,discount_price=?,is_free=?,
              thumbnail=?,promo_video=?,instructor=?,instructor_bio=?,what_you_learn=?,requirements=?,status=?
              WHERE id=?", [
            $title,
            trim((string)($_POST['subtitle'] ?? '')),
            trim((string)($_POST['description'] ?? '')),
            trim((string)($_POST['category'] ?? '')),
            (string)($_POST['level'] ?? 'all'),
            (string)($_POST['language'] ?? 'fa'),
            (float)($_POST['price'] ?? 0),
            ($_POST['discount_price'] ?? '') !== '' ? (float)$_POST['discount_price'] : null,
            !empty($_POST['is_free']) ? 1 : 0,
            trim((string)($_POST['thumbnail'] ?? '')),
            trim((string)($_POST['promo_video'] ?? '')),
            trim((string)($_POST['instructor'] ?? '')),
            trim((string)($_POST['instructor_bio'] ?? '')),
            trim((string)($_POST['what_you_learn'] ?? '')),
            trim((string)($_POST['requirements'] ?? '')),
            $status, $editId,
          ]);
          $cid = $editId;
          q($pdo, "DELETE FROM course_lessons WHERE course_id=?", [$cid]);
          q($pdo, "DELETE FROM course_sections WHERE course_id=?", [$cid]);
        } else {
        q($pdo, "INSERT INTO courses
            (title,subtitle,description,category,level,language,price,discount_price,is_free,
             thumbnail,promo_video,instructor,instructor_bio,what_you_learn,requirements,status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [
          $title,
          trim((string)($_POST['subtitle'] ?? '')),
          trim((string)($_POST['description'] ?? '')),
          trim((string)($_POST['category'] ?? '')),
          (string)($_POST['level'] ?? 'all'),
          (string)($_POST['language'] ?? 'fa'),
          (float)($_POST['price'] ?? 0),
          ($_POST['discount_price'] ?? '') !== '' ? (float)$_POST['discount_price'] : null,
          !empty($_POST['is_free']) ? 1 : 0,
          trim((string)($_POST['thumbnail'] ?? '')),
          trim((string)($_POST['promo_video'] ?? '')),
          trim((string)($_POST['instructor'] ?? '')),
          trim((string)($_POST['instructor_bio'] ?? '')),
          trim((string)($_POST['what_you_learn'] ?? '')),
          trim((string)($_POST['requirements'] ?? '')),
          $status,
        ]);
        $cid = (int)$pdo->lastInsertId();
        }
        $so = 0;
        foreach ($curriculum as $sec) {
          q($pdo, "INSERT INTO course_sections (course_id,title,sort_order) VALUES (?,?,?)",
             [$cid, (string)($sec['title'] ?? 'فصل بدون عنوان'), $so++]);
          $sid = (int)$pdo->lastInsertId(); $lo = 0;
          foreach (($sec['lessons'] ?? []) as $ls) {
            q($pdo, "INSERT INTO course_lessons (section_id,course_id,title,type,content,duration,is_preview,sort_order)
                     VALUES (?,?,?,?,?,?,?,?)", [
              $sid, $cid,
              (string)($ls['title'] ?? 'درس'),
              (string)($ls['type'] ?? 'video'),
              (string)($ls['content'] ?? ''),
              (int)($ls['duration'] ?? 0),
              !empty($ls['is_preview']) ? 1 : 0,
              $lo++,
            ]);
          }
        }
        $pdo->commit();
        $saved = true;
        if ($editId) { $savedMsg = 'تغییرات دوره ذخیره شد.'; $editing = load_course_full($pdo, $coursesSchemaReady, $editId); }
        else $savedMsg = $status === 'published' ? 'دوره با موفقیت منتشر شد!' : 'پیش‌نویس دوره ذخیره شد.';
      } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $savedErr = 'خطا در ذخیره‌سازی: ' . $e->getMessage();
      }
    } else {
      // بدون دیتابیس: حالت نمایشی
      $saved = true;
      $savedMsg = 'دوره در حالت نمایشی ثبت شد (اتصال دیتابیس برقرار نیست).';
    }
  }
}

$pageCss = <<<CSS
.builder{display:grid;grid-template-columns:1fr 340px;gap:22px;align-items:start}
@media(max-width:1080px){.builder{grid-template-columns:1fr}}
.fsection{margin-bottom:18px;scroll-margin-top:20px}
.fsection-head{display:flex;align-items:center;gap:12px;margin-bottom:16px}
.fsection-num{width:34px;height:34px;border-radius:11px;display:grid;place-items:center;font-weight:800;font-size:14px;flex-shrink:0;
  background:var(--primary-08);color:var(--color-primary-dark)}
.fsection-head h2{font-size:17px;font-weight:800}
.fsection-head .s{font-size:12px;color:var(--color-muted);font-weight:500}

/* ---- آپلودر تصویر ---- */
.uploader{border:2px dashed var(--color-border);border-radius:16px;padding:26px;text-align:center;transition:border-color .2s,background .2s;cursor:pointer;position:relative;overflow:hidden}
.uploader:hover,.uploader.drag{border-color:var(--color-primary-light);background:var(--primary-08)}
.uploader .up-ic{width:52px;height:52px;border-radius:15px;display:grid;place-items:center;margin:0 auto 12px;background:var(--primary-08);color:var(--color-primary)}
.uploader .up-ic .ic{width:26px;height:26px}
.uploader b{font-size:13.5px}
.uploader span{display:block;font-size:11.5px;color:var(--color-muted);margin-top:4px}
.uploader.has-img{padding:0;border-style:solid;border-color:var(--color-border)}
.uploader.has-img .up-inner{display:none}
.uploader img.preview{display:none;width:100%;height:190px;object-fit:cover}
.uploader.has-img img.preview{display:block}

/* ---- سرفصل‌بند ---- */
.curriculum{display:flex;flex-direction:column;gap:14px}
.cur-section{border:1px solid var(--color-border);border-radius:16px;background:var(--color-surface);overflow:hidden;transition:box-shadow .2s,border-color .2s}
.cur-section.dragging{opacity:.5}
.cur-section.drag-over{border-color:var(--color-primary-light);box-shadow:0 0 0 3px var(--primary-08)}
.cur-sec-head{display:flex;align-items:center;gap:10px;padding:13px 14px;background:var(--color-bg)}
.cur-sec-head .grip{color:var(--color-muted);cursor:grab;display:grid;place-items:center;width:24px;flex-shrink:0}
.cur-sec-head .grip:active{cursor:grabbing}
.cur-sec-title{flex:1;background:transparent;border:1.5px solid transparent;border-radius:10px;padding:7px 10px;font-weight:800;font-size:14px;color:var(--color-text);transition:border-color .2s,background .2s}
.cur-sec-title:focus{outline:none;border-color:var(--color-primary-light);background:var(--color-surface)}
.cur-sec-meta{font-size:11.5px;color:var(--color-muted);font-weight:600;white-space:nowrap}
.icon-btn-sm{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;color:var(--color-muted);transition:background .2s,color .2s;flex-shrink:0}
.icon-btn-sm:hover{background:var(--primary-08);color:var(--color-primary-dark)}
.icon-btn-sm.del:hover{background:rgba(224,85,107,.12);color:var(--danger)}
.icon-btn-sm .ic{width:17px;height:17px}
.cur-sec-head .chev{transition:transform .3s var(--ease)}
.cur-section.collapsed .chev{transform:rotate(-90deg)}
.cur-section.collapsed .cur-sec-body{display:none}
.cur-sec-body{padding:12px 14px 14px}

/* ---- درس‌ها ---- */
.lesson{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--color-border);border-radius:12px;margin-bottom:8px;background:var(--color-bg);transition:border-color .2s,box-shadow .2s}
.lesson.dragging{opacity:.5}
.lesson.drag-over{border-color:var(--color-primary-light);box-shadow:0 0 0 3px var(--primary-08)}
.lesson .grip{color:var(--color-muted);cursor:grab;flex-shrink:0;display:grid;place-items:center}
.lesson-type-ic{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;flex-shrink:0;color:#fff}
.lesson-type-ic .ic{width:18px;height:18px}
.lt-video{background:linear-gradient(135deg,#007b7a,#006665)}
.lt-text{background:linear-gradient(135deg,#7c4ddb,#6d3fd1)}
.lt-image{background:linear-gradient(135deg,#f4a61e,#db8d0c)}
.lt-pdf{background:linear-gradient(135deg,#e0556b,#c33f54)}
.lt-quiz{background:linear-gradient(135deg,#16a37a,#0e7d5c)}
.lesson-main{flex:1;min-width:0}
.lesson-title-inp{width:100%;background:transparent;border:1.5px solid transparent;border-radius:9px;padding:5px 8px;font-weight:600;font-size:13px;color:var(--color-text);transition:border-color .2s,background .2s}
.lesson-title-inp:focus{outline:none;border-color:var(--color-primary-light);background:var(--color-surface)}
.lesson-sub{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:3px;padding:0 8px}
.lesson-sub .typ{font-size:11px;color:var(--color-muted);font-weight:600}
.lesson-prev-tag{font-size:10px;font-weight:700;color:var(--color-primary-dark);background:var(--primary-08);padding:2px 7px;border-radius:99px}
.lesson-actions{display:flex;align-items:center;gap:2px;flex-shrink:0}

/* پنل تنظیمات درس */
.lesson-cfg{display:none;margin:-2px 0 10px;padding:14px;border:1px solid var(--color-border);border-radius:12px;background:var(--color-surface)}
.lesson.cfg-open + .lesson-cfg{display:block}
.seg{display:inline-flex;background:var(--color-bg);border:1px solid var(--color-border);border-radius:11px;padding:4px;gap:3px;flex-wrap:wrap}
.seg button{padding:7px 12px;border-radius:8px;font-weight:700;font-size:12px;color:var(--color-muted);display:inline-flex;align-items:center;gap:6px;transition:background .18s,color .18s}
.seg button .ic{width:14px;height:14px}
.seg button.on{background:var(--color-surface);color:var(--color-primary-dark);box-shadow:var(--shadow-sm)}
.add-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px}

/* ---- ریل کناری ---- */
.rail{position:sticky;top:20px;display:flex;flex-direction:column;gap:16px}
.pv-card{border:1px solid var(--color-border);border-radius:16px;overflow:hidden;background:var(--color-surface);box-shadow:var(--shadow-sm)}
.pv-thumb{height:150px;background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary-dark));position:relative;display:grid;place-items:center;color:#fff}
.pv-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.pv-thumb .ph{position:relative;z-index:1;opacity:.5}.pv-thumb .ph .ic{width:40px;height:40px}
.pv-body{padding:15px}
.pv-cat{font-size:11px;font-weight:700;color:var(--color-primary-dark);background:var(--primary-08);padding:4px 10px;border-radius:99px;display:inline-block}
.pv-title{font-weight:800;font-size:15px;margin:9px 0 4px;line-height:1.5}
.pv-sub{font-size:12px;color:var(--color-muted);line-height:1.6;min-height:20px}
.pv-meta{display:flex;gap:14px;margin-top:11px;padding-top:11px;border-top:1px solid var(--color-border);font-size:11.5px;color:var(--color-muted);font-weight:600}
.pv-meta span{display:inline-flex;align-items:center;gap:5px}.pv-meta .ic{width:14px;height:14px}
.pv-price{display:flex;align-items:baseline;gap:8px;margin-top:12px}
.pv-price b{font-size:20px;font-weight:800;color:var(--color-text)}
.pv-price del{font-size:13px;color:var(--color-muted)}

.checklist{display:flex;flex-direction:column;gap:9px}
.chk{display:flex;align-items:center;gap:10px;font-size:12.5px;font-weight:600;color:var(--color-muted);transition:color .2s}
.chk .box{width:21px;height:21px;border-radius:7px;border:2px solid var(--color-border);display:grid;place-items:center;flex-shrink:0;transition:background .25s,border-color .25s}
.chk .box .ic{width:13px;height:13px;color:#fff;opacity:0;transform:scale(.4);transition:opacity .2s,transform .25s var(--ease);stroke-width:3.5}
.chk.done{color:var(--color-text)}
.chk.done .box{background:var(--success);border-color:var(--success)}
.chk.done .box .ic{opacity:1;transform:scale(1)}
.progress-mini{height:7px;border-radius:99px;background:var(--color-bg);overflow:hidden;margin-bottom:14px}
.progress-mini i{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,var(--color-primary-light),var(--color-primary));width:0;transition:width .5s var(--ease)}
.rail-actions{display:flex;flex-direction:column;gap:10px}
.tag-list{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
.tag-pill{display:inline-flex;align-items:center;gap:7px;background:var(--primary-08);color:var(--color-primary-dark);padding:6px 8px 6px 12px;border-radius:99px;font-size:12px;font-weight:600}
.tag-pill button{width:18px;height:18px;border-radius:50%;display:grid;place-items:center;color:var(--color-primary-dark);background:rgba(0,123,122,.12)}
.tag-pill button .ic{width:11px;height:11px}
.empty-cur{text-align:center;padding:30px;color:var(--color-muted);border:2px dashed var(--color-border);border-radius:16px;font-size:13px}
CSS;

course_html_head('ساخت دوره جدید', $pageCss);
?>
<body>
<form class="page" id="courseForm" method="post" autocomplete="off">
  <?php if ($editId): ?><input type="hidden" name="edit_id" value="<?= $editId ?>"><?php endif; ?>
  <!-- سرصفحه -->
  <div class="page-head">
    <div class="ph-icon"><?= $editId ? cic('edit') : cic('plus') ?></div>
    <div>
      <h1><?= $editId ? 'ویرایش دوره' : 'ساخت دوره‌ی جدید' ?></h1>
      <div class="ph-sub"><?= $editId ? 'محتوای دوره را به‌روزرسانی کنید؛ تغییرات پس از ذخیره اعمال می‌شود.' : 'یک دوره‌ی کامل بسازید؛ سرفصل‌ها، ویدئو، متن و تصویر را آزادانه بچینید.' ?></div>
    </div>
    <div class="ph-spacer"></div>
    <button type="submit" name="action" value="draft" class="btn btn-ghost"><?= cic('save') ?> ذخیره پیش‌نویس</button>
    <button type="submit" name="action" value="publish" class="btn btn-primary" id="publishBtn"><?= cic('rocket') ?> <?= $editId ? 'ذخیره و انتشار' : 'انتشار دوره' ?></button>
  </div>

  <div class="builder">
    <!-- ستون اصلی -->
    <div class="main-col">

      <!-- ۱) اطلاعات پایه -->
      <section class="fsection card card-pad" id="sec-basic">
        <div class="fsection-head">
          <div class="fsection-num">۱</div>
          <div><h2>اطلاعات پایه</h2><div class="s">عنوان و معرفیِ دوره را مشخص کنید</div></div>
        </div>
        <div class="field">
          <label class="lbl">عنوان دوره <span class="req">*</span></label>
          <input class="inp" id="f_title" name="title" maxlength="120" placeholder="مثال: دوره جامع طراحی وب از صفر تا صد" required>
          <div class="hint">عنوانی گویا و جذاب بنویسید؛ این مهم‌ترین عاملِ کلیک کاربر است.</div>
        </div>
        <div class="field">
          <label class="lbl">زیرعنوان (توضیح کوتاه)</label>
          <input class="inp" id="f_subtitle" name="subtitle" maxlength="160" placeholder="در یک جمله بگویید فراگیر پس از این دوره چه می‌تواند بکند">
        </div>
        <div class="inp-group">
          <div class="field">
            <label class="lbl">دسته‌بندی</label>
            <select class="sel" id="f_category" name="category">
              <option value="">انتخاب دسته‌بندی…</option>
              <option>برنامه‌نویسی</option><option>طراحی</option><option>هوش مصنوعی</option>
              <option>کسب‌وکار</option><option>زبان</option><option>توسعه فردی</option>
              <option>بازاریابی</option><option>سلامت</option><option>هنر</option>
            </select>
          </div>
          <div class="field">
            <label class="lbl">سطح دوره</label>
            <select class="sel" name="level" id="f_level">
              <option value="all">همه سطوح</option>
              <option value="beginner">مقدماتی</option>
              <option value="intermediate">متوسط</option>
              <option value="advanced">پیشرفته</option>
            </select>
          </div>
        </div>
        <div class="field">
          <label class="lbl">زبان دوره</label>
          <select class="sel" name="language" style="max-width:220px">
            <option value="fa">فارسی</option><option value="en">انگلیسی</option><option value="ar">عربی</option>
          </select>
        </div>
        <div class="field" style="margin-bottom:0">
          <label class="lbl">توضیحات کامل دوره</label>
          <textarea class="txa" id="f_description" name="description" placeholder="شرح کاملی از محتوای دوره، مخاطب هدف و دستاوردها بنویسید…"></textarea>
        </div>
      </section>

      <!-- ۲) رسانه -->
      <section class="fsection card card-pad" id="sec-media">
        <div class="fsection-head">
          <div class="fsection-num">۲</div>
          <div><h2>تصویر و ویدئوی معرفی</h2><div class="s">ظاهر دوره در فروشگاه را زیبا کنید</div></div>
        </div>
        <div class="field">
          <label class="lbl">تصویر شاخص دوره</label>
          <div class="uploader" id="thumbUploader">
            <img class="preview" id="thumbPreview" alt="">
            <div class="up-inner">
              <div class="up-ic"><?= cic('image') ?></div>
              <b>تصویر را اینجا رها کنید یا کلیک کنید</b>
              <span>نسبت ۱۶:۹ — حداقل ۱۲۸۰×۷۲۰ پیکسل (JPG/PNG)</span>
            </div>
            <input type="file" id="thumbFile" accept="image/*" hidden>
          </div>
          <input type="hidden" name="thumbnail" id="f_thumbnail">
        </div>
        <div class="field" style="margin-bottom:0">
          <label class="lbl">لینک ویدئوی معرفی (اختیاری)</label>
          <input class="inp" name="promo_video" id="f_promo" placeholder="https://… یا کد جاسازی آپارات/یوتیوب">
          <div class="hint">یک ویدئوی کوتاه ۱ تا ۳ دقیقه‌ای نرخ خرید را به‌شدت بالا می‌برد.</div>
        </div>
      </section>

      <!-- ۳) قیمت‌گذاری -->
      <section class="fsection card card-pad" id="sec-price">
        <div class="fsection-head">
          <div class="fsection-num">۳</div>
          <div><h2>قیمت‌گذاری</h2><div class="s">قیمت و تخفیف دوره را تعیین کنید</div></div>
        </div>
        <label class="switch" style="margin-bottom:16px">
          <input type="checkbox" id="f_free" name="is_free" value="1">
          <span class="track"></span>
          <span>این دوره رایگان است</span>
        </label>
        <div class="inp-group" id="priceFields">
          <div class="field" style="margin-bottom:0">
            <label class="lbl">قیمت دوره (تومان)</label>
            <input class="inp" type="number" min="0" step="1000" name="price" id="f_price" placeholder="۰" inputmode="numeric">
          </div>
          <div class="field" style="margin-bottom:0">
            <label class="lbl">قیمت با تخفیف (اختیاری)</label>
            <input class="inp" type="number" min="0" step="1000" name="discount_price" id="f_discount" placeholder="مثلاً ۷۹۰۰۰۰" inputmode="numeric">
          </div>
        </div>
      </section>

      <!-- ۴) سرفصل‌ها / محتوا -->
      <section class="fsection card card-pad" id="sec-curriculum">
        <div class="fsection-head">
          <div class="fsection-num">۴</div>
          <div><h2>سرفصل‌ها و محتوای دوره</h2><div class="s">فصل‌ها را بسازید و درس‌های ویدئویی، متنی یا تصویری اضافه کنید</div></div>
          <div class="ph-spacer"></div>
          <span class="badge b-primary" id="curStat"><span class="bd"></span>۰ درس</span>
        </div>
        <div class="curriculum" id="curriculum"></div>
        <div class="add-row">
          <button type="button" class="btn btn-soft" id="addSectionBtn"><?= cic('plus') ?> افزودن فصل جدید</button>
        </div>
      </section>

      <!-- ۵) جزئیات تکمیلی -->
      <section class="fsection card card-pad" id="sec-extra" style="margin-bottom:0">
        <div class="fsection-head">
          <div class="fsection-num">۵</div>
          <div><h2>جزئیات تکمیلی</h2><div class="s">دستاوردها، پیش‌نیازها و معرفی مدرس</div></div>
        </div>
        <div class="field">
          <label class="lbl"><?= cic('check') ?> در این دوره چه می‌آموزید؟</label>
          <div style="display:flex;gap:8px">
            <input class="inp" id="learnInput" placeholder="یک دستاورد بنویسید و Enter بزنید…">
            <button type="button" class="btn btn-soft" id="addLearn" style="flex-shrink:0"><?= cic('plus') ?></button>
          </div>
          <div class="tag-list" id="learnList"></div>
          <input type="hidden" name="what_you_learn" id="f_learn">
        </div>
        <div class="field">
          <label class="lbl"><?= cic('list') ?> پیش‌نیازها</label>
          <div style="display:flex;gap:8px">
            <input class="inp" id="reqInput" placeholder="یک پیش‌نیاز بنویسید و Enter بزنید…">
            <button type="button" class="btn btn-soft" id="addReq" style="flex-shrink:0"><?= cic('plus') ?></button>
          </div>
          <div class="tag-list" id="reqList"></div>
          <input type="hidden" name="requirements" id="f_req">
        </div>
        <div class="inp-group">
          <div class="field" style="margin-bottom:0">
            <label class="lbl">نام مدرس</label>
            <input class="inp" name="instructor" id="f_instructor" placeholder="نام و نام خانوادگی مدرس">
          </div>
          <div class="field" style="margin-bottom:0">
            <label class="lbl">عنوان/تخصص مدرس</label>
            <input class="inp" name="instructor_bio" id="f_instructor_bio" placeholder="مثال: مدرس و توسعه‌دهنده‌ی ارشد">
          </div>
        </div>
      </section>
    </div>

    <!-- ریل کناری -->
    <aside class="rail">
      <div class="pv-card">
        <div class="pv-thumb"><img id="railThumb" alt="" style="display:none"><span class="ph" id="railThumbPh"><?= cic('image') ?></span></div>
        <div class="pv-body">
          <span class="pv-cat" id="railCat">دسته‌بندی</span>
          <div class="pv-title" id="railTitle">عنوان دوره شما</div>
          <div class="pv-sub" id="railSub">زیرعنوان دوره اینجا نمایش داده می‌شود.</div>
          <div class="pv-meta">
            <span><?= cic('video') ?><b id="railLessons">۰</b> درس</span>
            <span><?= cic('clock') ?><span id="railDur">۰ دقیقه</span></span>
          </div>
          <div class="pv-price"><b id="railPrice">رایگان</b><del id="railOld" style="display:none"></del></div>
        </div>
      </div>

      <div class="card card-pad">
        <div class="card-head"><div><h3 style="font-size:15px">تکمیل دوره</h3></div><b id="chkPct" style="color:var(--color-primary-dark)">۰٪</b></div>
        <div class="progress-mini" style="margin-top:12px"><i id="chkBar"></i></div>
        <div class="checklist">
          <div class="chk" data-chk="title"><span class="box"><?= cic('check') ?></span> عنوان دوره</div>
          <div class="chk" data-chk="desc"><span class="box"><?= cic('check') ?></span> توضیحات دوره</div>
          <div class="chk" data-chk="thumb"><span class="box"><?= cic('check') ?></span> تصویر شاخص</div>
          <div class="chk" data-chk="price"><span class="box"><?= cic('check') ?></span> قیمت‌گذاری</div>
          <div class="chk" data-chk="lessons"><span class="box"><?= cic('check') ?></span> حداقل یک درس</div>
        </div>
      </div>

      <div class="rail-actions">
        <button type="submit" name="action" value="publish" class="btn btn-primary btn-block btn-lg"><?= cic('rocket') ?> انتشار دوره</button>
        <button type="submit" name="action" value="draft" class="btn btn-ghost btn-block"><?= cic('save') ?> ذخیره به‌عنوان پیش‌نویس</button>
      </div>
    </aside>
  </div>

  <input type="hidden" name="curriculum" id="f_curriculum">
</form>

<div class="toast" id="toast"><div class="ti"><?= cic('check') ?></div><span id="toastMsg">ذخیره شد</span></div>
<?php course_theme_fab(); ?>

<script>
(function(){
  "use strict";
  // داده‌ی دوره هنگام ویرایش (در حالت ساخت جدید: null)
  var EDIT = <?= $editing ? json_encode($editing, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : 'null' ?>;
  var toFa = function(s){ return String(s).replace(/\d/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[d];}); };
  var sep  = function(n){ return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g,'٬'); };
  var faN  = function(n){ return toFa(sep(n)); };
  var $ = function(id){ return document.getElementById(id); };

  /* ---------- آیکن‌های نوع درس ---------- */
  var TYPE_ICONS = {
    video:'<rect x="2" y="5" width="14" height="14" rx="3"/><path d="m16 9 5-3v12l-5-3"/>',
    text:'<line x1="5" y1="6" x2="19" y2="6"/><line x1="5" y1="11" x2="19" y2="11"/><line x1="5" y1="16" x2="13" y2="16"/>',
    image:'<rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="9" r="1.8"/><path d="m21 16-5-5L5 21"/>',
    pdf:'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
    quiz:'<circle cx="12" cy="12" r="9"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12" y2="17.01"/>'
  };
  var TYPE_LABEL = {video:'ویدئو',text:'متن / مقاله',image:'تصویر',pdf:'فایل PDF',quiz:'آزمون'};
  function svg(name){ return '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'+(TYPE_ICONS[name]||'')+'</svg>'; }
  var ICX = '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
  function ic(p){ return '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'+p+'</svg>'; }
  var I_TRASH='<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>';
  var I_GRIP='<circle cx="9" cy="6" r="1.4" fill="currentColor" stroke="none"/><circle cx="15" cy="6" r="1.4" fill="currentColor" stroke="none"/><circle cx="9" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="15" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="9" cy="18" r="1.4" fill="currentColor" stroke="none"/><circle cx="15" cy="18" r="1.4" fill="currentColor" stroke="none"/>';
  var I_CHEV='<polyline points="6 9 12 15 18 9"/>';
  var I_GEAR='<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-2.18-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 8 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H2a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 3.6 8a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 8 3.6 1.65 1.65 0 0 0 9 2.09V2a2 2 0 0 1 4 0v.09A1.65 1.65 0 0 0 14 3.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 20.4 8a1.65 1.65 0 0 0 1.51 1H22a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>';

  /* ============ سرفصل‌بند ============ */
  var curEl = $('curriculum');
  var secCount = 0;

  function emptyState(){
    if(!curEl.querySelector('.cur-section')){
      curEl.innerHTML = '<div class="empty-cur" id="emptyCur">هنوز فصلی نساخته‌اید. برای شروع، «افزودن فصل جدید» را بزنید.</div>';
    }
  }
  function clearEmpty(){ var e=$('emptyCur'); if(e) e.remove(); }

  function addSection(title){
    clearEmpty();
    secCount++;
    var sec = document.createElement('div');
    sec.className = 'cur-section';
    sec.setAttribute('draggable','false');
    sec.innerHTML =
      '<div class="cur-sec-head">'+
        '<span class="grip" title="جابه‌جایی فصل">'+ic(I_GRIP)+'</span>'+
        '<input class="cur-sec-title" value="'+(title||('فصل '+toFa(secCount)))+'" placeholder="عنوان فصل">'+
        '<span class="cur-sec-meta"><span class="l-count">۰</span> درس</span>'+
        '<button type="button" class="icon-btn-sm collapse" title="بازوبسته">'+ic(I_CHEV)+'<span class="chev" style="display:none"></span></button>'+
        '<button type="button" class="icon-btn-sm del delSec" title="حذف فصل">'+ic(I_TRASH)+'</button>'+
      '</div>'+
      '<div class="cur-sec-body">'+
        '<div class="lessons"></div>'+
        '<div class="add-row">'+
          '<button type="button" class="btn btn-soft addLesson" data-type="video">'+svg('video')+' ویدئو</button>'+
          '<button type="button" class="btn btn-soft addLesson" data-type="text">'+svg('text')+' متن</button>'+
          '<button type="button" class="btn btn-soft addLesson" data-type="image">'+svg('image')+' تصویر</button>'+
          '<button type="button" class="btn btn-soft addLesson" data-type="pdf">'+svg('pdf')+' PDF</button>'+
          '<button type="button" class="btn btn-soft addLesson" data-type="quiz">'+svg('quiz')+' آزمون</button>'+
        '</div>'+
      '</div>';
    curEl.appendChild(sec);
    wireSection(sec);
    sync();
    return sec;
  }

  function chevFix(sec){
    var b = sec.querySelector('.collapse');
    b.classList.toggle('rot');
  }

  function wireSection(sec){
    var head = sec.querySelector('.cur-sec-head');
    sec.querySelector('.collapse').addEventListener('click', function(){ sec.classList.toggle('collapsed'); });
    sec.querySelector('.delSec').addEventListener('click', function(){
      if(sec.querySelectorAll('.lesson').length && !confirm('این فصل و درس‌هایش حذف شود؟')) return;
      sec.remove(); emptyState(); sync();
    });
    sec.querySelector('.cur-sec-title').addEventListener('input', sync);
    sec.querySelectorAll('.addLesson').forEach(function(b){
      b.addEventListener('click', function(){ addLesson(sec, b.getAttribute('data-type')); });
    });
    // درگ فصل از روی دستگیره
    var grip = head.querySelector('.grip');
    grip.addEventListener('mousedown', function(){ sec.setAttribute('draggable','true'); });
    grip.addEventListener('mouseup', function(){ sec.setAttribute('draggable','false'); });
    sec.addEventListener('dragstart', function(e){ if(sec.getAttribute('draggable')!=='true'){e.preventDefault();return;} sec.classList.add('dragging'); e.dataTransfer.effectAllowed='move'; });
    sec.addEventListener('dragend', function(){ sec.classList.remove('dragging'); sec.setAttribute('draggable','false'); curEl.querySelectorAll('.drag-over').forEach(function(x){x.classList.remove('drag-over');}); });
  }

  // درگ فصل‌ها
  curEl.addEventListener('dragover', function(e){
    var dragging = curEl.querySelector('.cur-section.dragging'); if(!dragging) return;
    e.preventDefault();
    var after = getDragAfter(curEl, '.cur-section:not(.dragging)', e.clientY);
    if(after==null) curEl.appendChild(dragging); else curEl.insertBefore(dragging, after);
  });
  function getDragAfter(container, sel, y){
    var els = [].slice.call(container.querySelectorAll(sel));
    var closest = {offset:-Infinity, el:null};
    els.forEach(function(child){
      var box = child.getBoundingClientRect();
      var offset = y - box.top - box.height/2;
      if(offset<0 && offset>closest.offset) closest = {offset:offset, el:child};
    });
    return closest.el;
  }

  /* ---------- درس ---------- */
  function addLesson(sec, type, data){
    var lessons = sec.querySelector('.lessons');
    var wrap = document.createElement('div');
    wrap.className = 'lesson-wrap';
    var ls = document.createElement('div');
    ls.className = 'lesson';
    ls.dataset.type = type;
    ls.innerHTML =
      '<span class="grip">'+ic(I_GRIP)+'</span>'+
      '<span class="lesson-type-ic lt-'+type+'">'+svg(type)+'</span>'+
      '<div class="lesson-main">'+
        '<input class="lesson-title-inp" placeholder="عنوان درس ('+TYPE_LABEL[type]+')">'+
        '<div class="lesson-sub"><span class="typ">'+TYPE_LABEL[type]+'</span><span class="dur-tag" style="font-size:11px;color:var(--color-muted)"></span></div>'+
      '</div>'+
      '<div class="lesson-actions">'+
        '<button type="button" class="icon-btn-sm cfg" title="تنظیمات درس">'+ic(I_GEAR)+'</button>'+
        '<button type="button" class="icon-btn-sm del delLesson" title="حذف درس">'+ic(I_TRASH)+'</button>'+
      '</div>';
    var cfg = document.createElement('div');
    cfg.className = 'lesson-cfg';
    cfg.innerHTML = buildCfg(type);
    wrap.appendChild(ls); wrap.appendChild(cfg);
    lessons.appendChild(wrap);
    wireLesson(sec, wrap, ls, cfg);
    // مقداردهی (درس جدید یا داده‌ی موجود هنگام ویرایش)
    ls.querySelector('.lesson-title-inp').value = (data && data.title!=null) ? data.title : ('درس جدید — '+TYPE_LABEL[type]);
    if (data) {
      if (cfg.querySelector('.content-inp') && data.content!=null) cfg.querySelector('.content-inp').value = data.content;
      if (data.duration) {
        cfg.querySelector('.dur-inp').value = data.duration;
        ls.querySelector('.dur-tag').textContent = '· '+toFa(data.duration)+' دقیقه';
      }
      if (data.is_preview==1 || data.is_preview===true) {
        cfg.querySelector('.prev-chk').checked = true;
        var sub = ls.querySelector('.lesson-sub'), t=document.createElement('span');
        t.className='lesson-prev-tag'; t.textContent='پیش‌نمایش'; sub.appendChild(t);
      }
    }
    sync();
  }

  function buildCfg(type){
    var typeSeg =
      '<div class="field"><label class="lbl">نوع محتوا</label>'+
      '<div class="seg type-seg">'+
        ['video','text','image','pdf','quiz'].map(function(t){
          return '<button type="button" data-t="'+t+'" class="'+(t===type?'on':'')+'">'+svg(t)+' '+TYPE_LABEL[t]+'</button>';
        }).join('')+
      '</div></div>';
    return typeSeg + '<div class="type-body">'+contentField(type)+'</div>'+
      '<div class="inp-group" style="margin-top:12px">'+
        '<div class="field" style="margin-bottom:0"><label class="lbl">مدت (دقیقه)</label><input class="inp dur-inp" type="number" min="0" placeholder="۰" inputmode="numeric"></div>'+
        '<div class="field" style="margin-bottom:0;align-self:end"><label class="switch"><input type="checkbox" class="prev-chk"><span class="track"></span><span>نمایش رایگان (پیش‌نمایش)</span></label></div>'+
      '</div>';
  }
  function contentField(type){
    if(type==='video') return '<div class="field" style="margin-bottom:0"><label class="lbl">لینک ویدئو یا آپلود</label><input class="inp content-inp" placeholder="https://… یا کد جاسازی"><div class="hint">می‌توانید لینک مستقیم، آپارات یا یوتیوب قرار دهید.</div></div>';
    if(type==='text') return '<div class="field" style="margin-bottom:0"><label class="lbl">متن درس</label><textarea class="txa content-inp" placeholder="محتوای متنی این درس را بنویسید…"></textarea></div>';
    if(type==='image') return '<div class="field" style="margin-bottom:0"><label class="lbl">لینک تصویر</label><input class="inp content-inp" placeholder="https://…/image.jpg"><div class="hint">آدرس تصویر آموزشی یا اسلاید را وارد کنید.</div></div>';
    if(type==='pdf') return '<div class="field" style="margin-bottom:0"><label class="lbl">لینک فایل PDF</label><input class="inp content-inp" placeholder="https://…/file.pdf"></div>';
    if(type==='quiz') return '<div class="field" style="margin-bottom:0"><label class="lbl">توضیح آزمون</label><textarea class="txa content-inp" placeholder="سؤالات یا توضیح آزمون را وارد کنید…"></textarea></div>';
    return '';
  }

  function wireLesson(sec, wrap, ls, cfg){
    ls.querySelector('.cfg').addEventListener('click', function(){ ls.classList.toggle('cfg-open'); });
    ls.querySelector('.delLesson').addEventListener('click', function(){ wrap.remove(); sync(); });
    ls.querySelector('.lesson-title-inp').addEventListener('input', sync);
    bindCfg(ls, cfg);
    // درگ درس
    var grip = ls.querySelector('.grip');
    grip.addEventListener('mousedown', function(){ wrap.setAttribute('draggable','true'); });
    grip.addEventListener('mouseup', function(){ wrap.setAttribute('draggable','false'); });
    wrap.addEventListener('dragstart', function(e){ if(wrap.getAttribute('draggable')!=='true'){e.preventDefault();return;} wrap.classList.add('dragging'); e.stopPropagation(); });
    wrap.addEventListener('dragend', function(){ wrap.classList.remove('dragging'); wrap.setAttribute('draggable','false'); });
    var lessonsBox = sec.querySelector('.lessons');
    lessonsBox.addEventListener('dragover', function(e){
      var dragging = lessonsBox.querySelector('.lesson-wrap.dragging'); if(!dragging) return;
      e.preventDefault(); e.stopPropagation();
      var after = getDragAfter(lessonsBox, '.lesson-wrap:not(.dragging)', e.clientY);
      if(after==null) lessonsBox.appendChild(dragging); else lessonsBox.insertBefore(dragging, after);
    });
  }

  function bindCfg(ls, cfg){
    cfg.querySelectorAll('.type-seg button').forEach(function(b){
      b.addEventListener('click', function(){
        var t = b.getAttribute('data-t');
        cfg.querySelectorAll('.type-seg button').forEach(function(x){x.classList.remove('on');});
        b.classList.add('on');
        ls.dataset.type = t;
        ls.querySelector('.lesson-type-ic').className = 'lesson-type-ic lt-'+t;
        ls.querySelector('.lesson-type-ic').innerHTML = svg(t);
        ls.querySelector('.lesson-sub .typ').textContent = TYPE_LABEL[t];
        cfg.querySelector('.type-body').innerHTML = contentField(t);
        cfg.querySelector('.content-inp').addEventListener('input', sync);
        sync();
      });
    });
    cfg.querySelector('.content-inp').addEventListener('input', sync);
    cfg.querySelector('.dur-inp').addEventListener('input', function(){
      var v = parseInt(cfg.querySelector('.dur-inp').value)||0;
      ls.querySelector('.dur-tag').textContent = v? ('· '+toFa(v)+' دقیقه') : '';
      sync();
    });
    cfg.querySelector('.prev-chk').addEventListener('change', function(){
      var sub = ls.querySelector('.lesson-sub');
      var t = sub.querySelector('.lesson-prev-tag');
      if(cfg.querySelector('.prev-chk').checked){ if(!t){ t=document.createElement('span'); t.className='lesson-prev-tag'; t.textContent='پیش‌نمایش'; sub.appendChild(t);} }
      else if(t){ t.remove(); }
      sync();
    });
  }

  /* ============ همگام‌سازی: شمارش، JSON، پیش‌نمایش، چک‌لیست ============ */
  function collect(){
    var data = [];
    curEl.querySelectorAll('.cur-section').forEach(function(sec){
      var lessons = [];
      sec.querySelectorAll('.lesson-wrap').forEach(function(wrap){
        var ls = wrap.querySelector('.lesson'), cfg = wrap.querySelector('.lesson-cfg');
        lessons.push({
          title: ls.querySelector('.lesson-title-inp').value,
          type: ls.dataset.type,
          content: cfg.querySelector('.content-inp') ? cfg.querySelector('.content-inp').value : '',
          duration: parseInt(cfg.querySelector('.dur-inp').value)||0,
          is_preview: cfg.querySelector('.prev-chk').checked ? 1 : 0
        });
      });
      data.push({ title: sec.querySelector('.cur-sec-title').value, lessons: lessons });
    });
    return data;
  }

  function sync(){
    var data = collect();
    $('f_curriculum').value = JSON.stringify(data);
    var totalLessons = 0, totalDur = 0;
    data.forEach(function(s){ totalLessons += s.lessons.length; s.lessons.forEach(function(l){ totalDur += l.duration; }); });
    // شمارش هر فصل
    curEl.querySelectorAll('.cur-section').forEach(function(sec){
      sec.querySelector('.l-count').textContent = toFa(sec.querySelectorAll('.lesson-wrap').length);
    });
    $('curStat').innerHTML = '<span class="bd"></span>'+toFa(totalLessons)+' درس · '+toFa(data.length)+' فصل';
    // پیش‌نمایش
    $('railLessons').textContent = toFa(totalLessons);
    $('railDur').textContent = totalDur? (totalDur>=60? (toFa(Math.floor(totalDur/60))+' ساعت '+toFa(totalDur%60)+' دقیقه') : (toFa(totalDur)+' دقیقه')) : '۰ دقیقه';
    updateChecklist(totalLessons);
  }

  /* ---------- پیش‌نمایش زنده ---------- */
  function bindPreview(){
    $('f_title').addEventListener('input', function(){ $('railTitle').textContent = $('f_title').value || 'عنوان دوره شما'; updateChecklist(); });
    $('f_subtitle').addEventListener('input', function(){ $('railSub').textContent = $('f_subtitle').value || 'زیرعنوان دوره اینجا نمایش داده می‌شود.'; });
    $('f_category').addEventListener('change', function(){ $('railCat').textContent = $('f_category').value || 'دسته‌بندی'; });
    $('f_description').addEventListener('input', updateChecklist);
    function priceView(){
      var free = $('f_free').checked;
      var p = parseInt($('f_price').value)||0, d = parseInt($('f_discount').value)||0;
      if(free){ $('railPrice').textContent='رایگان'; $('railOld').style.display='none'; }
      else if(d>0 && d<p){ $('railPrice').textContent=faN(d)+' تومان'; $('railOld').style.display='inline'; $('railOld').textContent=faN(p)+' تومان'; }
      else { $('railPrice').textContent = p? (faN(p)+' تومان') : 'رایگان'; $('railOld').style.display='none'; }
      updateChecklist();
    }
    [$('f_price'),$('f_discount')].forEach(function(el){ el.addEventListener('input', priceView); });
    $('f_free').addEventListener('change', function(){
      $('priceFields').style.opacity = $('f_free').checked? '.45':'1';
      $('priceFields').style.pointerEvents = $('f_free').checked? 'none':'auto';
      priceView();
    });
  }

  /* ---------- چک‌لیست ---------- */
  function updateChecklist(lessons){
    if(lessons===undefined){ var d=collect(); lessons=0; d.forEach(function(s){lessons+=s.lessons.length;}); }
    var st = {
      title: !!$('f_title').value.trim(),
      desc: $('f_description').value.trim().length>20,
      thumb: !!$('f_thumbnail').value,
      price: $('f_free').checked || (parseInt($('f_price').value)||0)>0,
      lessons: lessons>0
    };
    var done=0, total=0;
    document.querySelectorAll('.chk').forEach(function(c){
      total++; var k=c.getAttribute('data-chk');
      if(st[k]){ c.classList.add('done'); done++; } else c.classList.remove('done');
    });
    var pct = Math.round(done/total*100);
    $('chkPct').textContent = toFa(pct)+'٪';
    $('chkBar').style.width = pct+'%';
  }

  /* ---------- آپلودر تصویر ---------- */
  (function(){
    var up=$('thumbUploader'), file=$('thumbFile'), prev=$('thumbPreview');
    function setImg(src){
      prev.src=src; up.classList.add('has-img'); $('f_thumbnail').value=src;
      $('railThumb').src=src; $('railThumb').style.display='block'; $('railThumbPh').style.display='none';
      updateChecklist();
    }
    up.addEventListener('click', function(){ file.click(); });
    file.addEventListener('change', function(){
      var f=file.files[0]; if(!f) return;
      var r=new FileReader(); r.onload=function(){ setImg(r.result); }; r.readAsDataURL(f);
    });
    ['dragenter','dragover'].forEach(function(ev){ up.addEventListener(ev,function(e){e.preventDefault();up.classList.add('drag');}); });
    ['dragleave','drop'].forEach(function(ev){ up.addEventListener(ev,function(e){e.preventDefault();up.classList.remove('drag');}); });
    up.addEventListener('drop', function(e){
      var f=e.dataTransfer.files[0]; if(!f||!/^image\//.test(f.type)) return;
      var r=new FileReader(); r.onload=function(){ setImg(r.result); }; r.readAsDataURL(f);
    });
  })();

  /* ---------- تگ‌ها (آموخته‌ها / پیش‌نیازها) ---------- */
  function tagField(inputId, btnId, listId, hiddenId, initial){
    var input=$(inputId), list=$(listId), hidden=$(hiddenId), items=(initial||[]).slice();
    function render(){
      list.innerHTML = items.map(function(t,i){ return '<span class="tag-pill"><span>'+t.replace(/</g,'&lt;')+'</span><button type="button" data-i="'+i+'">'+ICX+'</button></span>'; }).join('');
      hidden.value = items.join('\n');
      list.querySelectorAll('button').forEach(function(b){ b.addEventListener('click', function(){ items.splice(+b.getAttribute('data-i'),1); render(); }); });
    }
    function add(){ var v=input.value.trim(); if(v){ items.push(v); input.value=''; render(); } }
    $(btnId).addEventListener('click', add);
    input.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); add(); } });
    if(items.length) render();
  }
  var splitLines=function(s){ return (s||'').split('\n').map(function(x){return x.trim();}).filter(Boolean); };
  tagField('learnInput','addLearn','learnList','f_learn', EDIT? splitLines(EDIT.what_you_learn):[]);
  tagField('reqInput','addReq','reqList','f_req', EDIT? splitLines(EDIT.requirements):[]);

  /* ---------- افزودن فصل ---------- */
  $('addSectionBtn').addEventListener('click', function(){ var s=addSection(); s.scrollIntoView({behavior:'smooth',block:'center'}); });

  /* ---------- اعتبارسنجی ارسال ---------- */
  $('courseForm').addEventListener('submit', function(e){
    sync();
    var submitter = e.submitter;
    var publishing = submitter && submitter.value==='publish';
    if(!$('f_title').value.trim()){
      e.preventDefault(); showToast('عنوان دوره را وارد کنید.', true); $('f_title').focus(); return;
    }
    if(publishing){
      var d=collect(), n=0; d.forEach(function(s){n+=s.lessons.length;});
      if(n===0){ e.preventDefault(); showToast('برای انتشار حداقل یک درس اضافه کنید.', true); return; }
    }
  });

  /* ---------- توست ---------- */
  function showToast(msg, err){
    var t=$('toast'); $('toastMsg').textContent=msg; t.classList.toggle('err', !!err);
    t.querySelector('.ti').innerHTML = err? ICX : '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    t.classList.add('show'); clearTimeout(t._t); t._t=setTimeout(function(){ t.classList.remove('show'); }, 3200);
  }
  window.__toast = showToast;

  /* ---------- راه‌اندازی ---------- */
  bindPreview();
  emptyState();

  if (EDIT) {
    // پیش‌پرکردنِ فیلدها هنگام ویرایش
    var setV=function(id,v){ var el=$(id); if(el!=null && v!=null) el.value=v; };
    setV('f_title', EDIT.title); setV('f_subtitle', EDIT.subtitle); setV('f_description', EDIT.description);
    setV('f_category', EDIT.category); setV('f_level', EDIT.level); setV('f_promo', EDIT.promo_video);
    setV('f_instructor', EDIT.instructor); setV('f_instructor_bio', EDIT.instructor_bio);
    setV('f_price', EDIT.price>0?EDIT.price:''); setV('f_discount', EDIT.discount_price>0?EDIT.discount_price:'');
    var langEl=document.querySelector('[name="language"]'); if(langEl && EDIT.language) langEl.value=EDIT.language;
    if (EDIT.is_free==1 || EDIT.is_free===true) $('f_free').checked = true;
    if (EDIT.thumbnail) {
      $('f_thumbnail').value = EDIT.thumbnail;
      $('thumbPreview').src = EDIT.thumbnail; $('thumbUploader').classList.add('has-img');
      $('railThumb').src = EDIT.thumbnail; $('railThumb').style.display='block'; $('railThumbPh').style.display='none';
    }
    // بازسازی سرفصل‌ها
    var curr = EDIT.curriculum || [];
    if (curr.length) {
      curr.forEach(function(sec){
        var s = addSection(sec.title || 'فصل');
        s.querySelector('.lessons').innerHTML=''; // حذفِ خالی اولیه (در صورت وجود)
        (sec.lessons||[]).forEach(function(l){ addLesson(s, l.type||'video', l); });
      });
    } else { addSection('فصل اول: مقدمه'); }
    // به‌روزرسانیِ نمایش‌ها پس از پرکردن
    $('railTitle').textContent = EDIT.title || 'عنوان دوره شما';
    if (EDIT.subtitle) $('railSub').textContent = EDIT.subtitle;
    if (EDIT.category) $('railCat').textContent = EDIT.category;
    $('f_free').dispatchEvent(new Event('change'));
    $('f_price').dispatchEvent(new Event('input'));
  } else {
    addSection('فصل اول: مقدمه');   // یک فصل آماده برای شروع سریع
  }
  updateChecklist();

  <?php if ($saved): ?> showToast(<?= json_encode($savedMsg, JSON_UNESCAPED_UNICODE) ?>, false); <?php endif; ?>
  <?php if ($savedErr): ?> showToast(<?= json_encode($savedErr, JSON_UNESCAPED_UNICODE) ?>, true); <?php endif; ?>
})();
</script>
</body>
</html>
