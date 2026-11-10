<dialog class="dash-dialog command-dialog" id="commandDialog" aria-labelledby="commandTitle">
  <div class="tool-head"><div><h2 id="commandTitle">جست‌وجو و فرمان</h2><p>صفحه‌ها، ابزارها و عملیات همین صفحه</p></div><button type="button" class="icon-btn" data-close-dialog aria-label="بستن جست‌وجو">×</button></div>
  <label class="command-input-wrap" for="commandInput"><span aria-hidden="true"><?= iconoir('search') ?></span><input id="commandInput" type="search" placeholder="مثلاً: ویرایش خبر، پرونده، تغییر تم…" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="true" aria-controls="commandResults"></label>
  <div class="tool-tabs" id="commandTabs" role="group" aria-label="نوع نتیجه"><button type="button" data-command-filter="all" aria-pressed="true">همه</button><button type="button" data-command-filter="pages" aria-pressed="false">صفحه‌ها</button><button type="button" data-command-filter="commands" aria-pressed="false">فرمان‌ها</button><button type="button" data-command-filter="context" aria-pressed="false">همین صفحه</button></div>
  <p class="tool-status" id="commandStatus" role="status"></p>
  <div id="commandResults" role="listbox" aria-label="نتایج جست‌وجو"></div>
  <div class="command-foot"><span>↑ ↓ انتخاب · Enter اجرا · Esc بستن</span><span>Ctrl / ⌘ K</span></div>
</dialog>

<dialog class="dash-dialog side-dialog" id="notifPanel" aria-labelledby="notifTitle">
  <div class="tool-head"><div><h2 id="notifTitle">اعلان‌ها</h2><p>رویدادهای ۳۰ روز اخیر و موارد بازِ قدیمی‌تر</p></div><button type="button" class="icon-btn" data-close-dialog aria-label="بستن اعلان‌ها">×</button></div>
  <div class="tool-tabs" id="notifFilters" role="group" aria-label="فیلتر اعلان‌ها"><button type="button" data-notif-filter="all" aria-pressed="true">همه</button><button type="button" data-notif-filter="unread" aria-pressed="false">نخوانده</button><button type="button" class="tool-refresh" id="notifRefresh">به‌روزرسانی</button></div>
  <p class="tool-status" id="notifStatus" role="status"></p>
  <div class="notification-list" id="notifList" aria-busy="true"></div>
  <div class="notification-pager"><button type="button" id="notifPrevious">قبلی</button><span id="notifPage"></span><button type="button" id="notifNext">بعدی</button></div>
  <div class="tool-foot"><button class="btn btn-primary" type="button" id="notifMark" disabled>خواندن همهٔ اعلان‌ها</button><p>خواندن اعلان، وضعیت رسیدگی به کار را تغییر نمی‌دهد.</p></div>
</dialog>

<dialog class="dash-dialog side-dialog" id="helpPanel" aria-labelledby="helpTitle">
  <div class="tool-head"><div><h2 id="helpTitle">راهنمای این بخش</h2><p id="helpPageName"></p></div><button type="button" class="icon-btn" data-close-dialog aria-label="بستن راهنما">×</button></div>
  <div class="help-content" id="helpContent"></div>
  <div class="tool-foot"><a class="btn btn-primary" href="docs.php" target="_blank" rel="noopener">بازکردن راهنمای کامل در پنجرهٔ جدید</a></div>
</dialog>
<div class="dash-feedback" id="dashFeedback" role="status" hidden></div>
