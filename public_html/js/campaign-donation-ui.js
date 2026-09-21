(function () {
  "use strict";

  if (window.MacsaCampaignDonation) return;

  var state = { campaignId: "", centerSlug: "", title: "" };
  var presetAmounts = [500000, 1000000, 2000000, 5000000];

  function latinDigits(value) {
    return String(value || "").replace(/[۰-۹٠-٩]/g, function (digit) {
      return "۰۱۲۳۴۵۶۷۸۹".indexOf(digit) >= 0
        ? String("۰۱۲۳۴۵۶۷۸۹".indexOf(digit))
        : String("٠١٢٣٤٥٦٧٨٩".indexOf(digit));
    });
  }

  function amountValue(value) {
    var cleaned = latinDigits(value).replace(/[^0-9]/g, "");
    return cleaned ? Number(cleaned) : 0;
  }

  function formatAmount(value) {
    return Number(value || 0).toLocaleString("en-US");
  }

  var ones = ["", "یک", "دو", "سه", "چهار", "پنج", "شش", "هفت", "هشت", "نه"];
  var teens = ["ده", "یازده", "دوازده", "سیزده", "چهارده", "پانزده", "شانزده", "هفده", "هجده", "نوزده"];
  var tens = ["", "", "بیست", "سی", "چهل", "پنجاه", "شصت", "هفتاد", "هشتاد", "نود"];
  var hundreds = ["", "صد", "دویست", "سیصد", "چهارصد", "پانصد", "ششصد", "هفتصد", "هشتصد", "نهصد"];
  var scales = ["", "هزار", "میلیون", "میلیارد", "تریلیون"];

  function underThousand(number) {
    var parts = [];
    var hundred = Math.floor(number / 100);
    var rest = number % 100;
    if (hundred) parts.push(hundreds[hundred]);
    if (rest >= 10 && rest < 20) {
      parts.push(teens[rest - 10]);
    } else {
      var ten = Math.floor(rest / 10);
      var one = rest % 10;
      if (ten) parts.push(tens[ten]);
      if (one) parts.push(ones[one]);
    }
    return parts.join(" و ");
  }

  function numberToPersianWords(number) {
    number = Math.floor(Number(number) || 0);
    if (!number) return "صفر";
    var chunks = [];
    var scale = 0;
    while (number > 0 && scale < scales.length) {
      var chunk = number % 1000;
      if (chunk) {
        var text = underThousand(chunk);
        if (scales[scale]) text += " " + scales[scale];
        chunks.unshift(text);
      }
      number = Math.floor(number / 1000);
      scale += 1;
    }
    return chunks.join(" و ");
  }

  function markup() {
    return '<div class="mcd" id="mcd-modal" aria-hidden="true">' +
      '<div class="mcd-dialog" role="dialog" aria-modal="true" aria-labelledby="mcd-title">' +
        '<button type="button" class="mcd-close" aria-label="بستن">&times;</button>' +
        '<div class="mcd-brand">کمک امن به کمپین مکسا</div>' +
        '<h2 id="mcd-title">کمک به کمپین</h2>' +
        '<p class="mcd-subtitle">اطلاعات زیر مطابق درگاه رسمی کمپین‌ساز برای آغاز پرداخت استفاده می‌شود.</p>' +
        '<form id="mcd-form" novalidate>' +
          '<div class="mcd-field"><label for="mcd-name">نام و نام خانوادگی</label>' +
            '<input id="mcd-name" name="full_name" type="text" autocomplete="name" maxlength="200" placeholder="مثلاً علی رضایی" required></div>' +
          '<div class="mcd-field"><label for="mcd-mobile">شماره همراه</label>' +
            '<input id="mcd-mobile" name="mobile" type="tel" inputmode="numeric" autocomplete="tel" maxlength="14" dir="ltr" placeholder="09123456789" required></div>' +
          '<fieldset class="mcd-presets"><legend>مبلغ پیشنهادی</legend>' +
            presetAmounts.map(function (amount) {
              return '<button type="button" data-amount="' + amount + '">' + Number(amount).toLocaleString("fa-IR") + '<small> ریال</small></button>';
            }).join("") +
          '</fieldset>' +
          '<div class="mcd-field"><label for="mcd-amount">مبلغ کمک</label>' +
            '<div class="mcd-amount-wrap"><input id="mcd-amount" name="amount_rial" type="text" inputmode="numeric" dir="ltr" autocomplete="off" placeholder="0" required><span>ریال</span></div>' +
            '<div class="mcd-amount-words" id="mcd-amount-words">صفر تومان</div></div>' +
          '<div class="mcd-message" id="mcd-message" role="status" aria-live="polite"></div>' +
          '<button type="submit" class="mcd-submit"><span>ثبت کمک و اتصال به درگاه</span><i aria-hidden="true">←</i></button>' +
          '<p class="mcd-note">تمام مبالغ به ریال ثبت می‌شوند. نتیجه نهایی پرداخت فقط پس از تأیید درگاه معتبر است.</p>' +
        '</form>' +
      '</div></div>';
  }

  function styles() {
    return '.mcd{--mcd-teal:#007b7a;--mcd-dark:#063a3c;--mcd-orange:#f5a623;position:fixed;inset:0;z-index:100000;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(4,38,39,.62);backdrop-filter:blur(7px);font-family:Vazirmatn,Tahoma,sans-serif;direction:rtl;opacity:0;transition:opacity .22s ease}' +
      '.mcd.open{display:flex;opacity:1}.mcd-dialog{position:relative;width:min(520px,100%);max-height:calc(100vh - 36px);overflow:auto;background:#fff;border:1px solid rgba(0,123,122,.12);border-radius:24px;padding:28px;box-shadow:0 30px 80px rgba(0,45,45,.28);transform:translateY(12px);transition:transform .22s ease}.mcd.open .mcd-dialog{transform:none}' +
      '.mcd-close{position:absolute;top:16px;left:16px;width:38px;height:38px;border:0;border-radius:12px;background:#eef6f5;color:#35605f;font-size:26px;line-height:1;cursor:pointer}.mcd-brand{display:inline-flex;padding:5px 11px;border-radius:999px;background:rgba(0,123,122,.09);color:var(--mcd-teal);font-size:11px;font-weight:800;margin-bottom:10px}.mcd h2{margin:0 0 5px;padding-left:44px;color:var(--mcd-dark);font-size:21px;line-height:1.6}.mcd-subtitle{margin:0 0 20px;color:#778384;font-size:12.5px;line-height:1.85}' +
      '.mcd-field{margin-bottom:15px}.mcd-field label,.mcd-presets legend{display:block;margin-bottom:7px;color:#334344;font-size:12.5px;font-weight:800}.mcd-field>input,.mcd-amount-wrap{width:100%;height:50px;border:1px solid #dfe7e7;border-radius:13px;background:#fbfdfd;transition:.2s}.mcd-field>input{padding:0 14px;font:inherit;font-size:14px;outline:none}.mcd-field>input:focus,.mcd-amount-wrap:focus-within{border-color:var(--mcd-teal);box-shadow:0 0 0 3px rgba(0,123,122,.1)}' +
      '.mcd-presets{border:0;padding:0;margin:0 0 15px;display:grid;grid-template-columns:repeat(4,1fr);gap:8px}.mcd-presets legend{grid-column:1/-1}.mcd-presets button{min-height:43px;border:1px solid rgba(0,123,122,.18);border-radius:12px;background:#f2f8f7;color:var(--mcd-dark);font-family:inherit;font-size:12px;font-weight:800;cursor:pointer;transition:.18s}.mcd-presets button small{font-size:9px;font-weight:700}.mcd-presets button:hover,.mcd-presets button.active{color:#fff;background:var(--mcd-teal);border-color:var(--mcd-teal);transform:translateY(-1px)}' +
      '.mcd-amount-wrap{display:flex;align-items:center;direction:rtl;overflow:hidden}.mcd-amount-wrap input{width:100%;height:100%;border:0;outline:0;background:transparent;padding:0 14px;font-family:Vazirmatn,Tahoma,sans-serif;font-size:18px;font-weight:800;color:var(--mcd-dark);direction:ltr;text-align:left}.mcd-amount-wrap span{align-self:stretch;display:flex;align-items:center;padding:0 15px;border-right:1px solid #dfe7e7;background:#f0f7f6;color:var(--mcd-teal);font-size:13px;font-weight:900;white-space:nowrap}.mcd-amount-words{min-height:23px;padding-top:7px;color:#687879;font-size:11.5px;font-weight:700;direction:rtl}' +
      '.mcd-message{display:none;margin:5px 0 12px;padding:10px 12px;border-radius:11px;font-size:12px;line-height:1.7}.mcd-message.show{display:block}.mcd-message.error{color:#8b2635;background:#fff0f2}.mcd-message.success{color:#06625d;background:#eaf8f6}.mcd-submit{width:100%;height:52px;border:0;border-radius:14px;background:linear-gradient(135deg,var(--mcd-teal),#006261);color:#fff;font-family:inherit;font-size:14px;font-weight:900;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;box-shadow:0 12px 24px -12px rgba(0,123,122,.8);transition:.2s}.mcd-submit:hover{transform:translateY(-2px)}.mcd-submit:disabled{opacity:.65;cursor:wait;transform:none}.mcd-submit i{font-style:normal;font-size:19px}.mcd-note{margin:12px 0 0;text-align:center;color:#8a9696;font-size:10.5px;line-height:1.7}' +
      '@media(max-width:560px){.mcd{align-items:flex-end;padding:0}.mcd-dialog{width:100%;max-height:94vh;border-radius:24px 24px 0 0;padding:24px 18px}.mcd-presets{grid-template-columns:repeat(2,1fr)}}';
  }

  function ensure() {
    var modal = document.getElementById("mcd-modal");
    if (modal) return modal;
    var style = document.createElement("style");
    style.textContent = styles();
    document.head.appendChild(style);
    document.body.insertAdjacentHTML("beforeend", markup());
    modal = document.getElementById("mcd-modal");
    var form = document.getElementById("mcd-form");
    var amount = document.getElementById("mcd-amount");

    function syncAmount(value) {
      var rial = amountValue(value);
      amount.value = rial ? formatAmount(rial) : "";
      document.getElementById("mcd-amount-words").textContent = numberToPersianWords(Math.floor(rial / 10)) + " تومان";
      modal.querySelectorAll("[data-amount]").forEach(function (button) {
        button.classList.toggle("active", Number(button.getAttribute("data-amount")) === rial);
      });
    }

    amount.addEventListener("input", function () { syncAmount(amount.value); });
    modal.querySelectorAll("[data-amount]").forEach(function (button) {
      button.addEventListener("click", function () { syncAmount(button.getAttribute("data-amount")); amount.focus(); });
    });
    modal.querySelector(".mcd-close").addEventListener("click", close);
    modal.addEventListener("click", function (event) { if (event.target === modal) close(); });
    document.addEventListener("keydown", function (event) { if (event.key === "Escape" && modal.classList.contains("open")) close(); });

    form.addEventListener("submit", async function (event) {
      event.preventDefault();
      var message = document.getElementById("mcd-message");
      var button = form.querySelector(".mcd-submit");
      var fullName = form.elements.full_name.value.trim();
      var mobile = latinDigits(form.elements.mobile.value).replace(/[^0-9+]/g, "");
      var rial = amountValue(form.elements.amount_rial.value);
      message.className = "mcd-message";
      if (fullName.length < 2) return showMessage("نام و نام خانوادگی را وارد کنید.", true);
      if (!/^(?:\+98|0098|98|0)?9\d{9}$/.test(mobile)) return showMessage("شماره همراه معتبر وارد کنید.", true);
      if (rial < 10000 || rial > 5000000000 || rial % 10 !== 0) return showMessage("مبلغ باید حداقل ۱۰٬۰۰۰ ریال و مضربی از ۱۰ باشد.", true);
      if (!window.MaksaCampaignPayment || typeof window.MaksaCampaignPayment.start !== "function") return showMessage("سامانه پرداخت بارگذاری نشده است؛ صفحه را دوباره بارگذاری کنید.", true);

      button.disabled = true;
      button.querySelector("span").textContent = "در حال اتصال به درگاه…";
      try {
        await window.MaksaCampaignPayment.start({
          center_slug: state.centerSlug,
          campaign_id: state.campaignId,
          full_name: fullName,
          mobile: mobile,
          amount_rial: rial,
          onSuccess: function () { showMessage("پرداخت با موفقیت تأیید شد. سپاسگزاریم.", false); },
          onFailure: function () { showMessage("پرداخت ناموفق بود یا لغو شد.", true); }
        });
        showMessage("درگاه پرداخت باز شد؛ فرایند را در پنجره جدید تکمیل کنید.", false);
      } catch (error) {
        showMessage(error && error.message ? error.message : "اتصال به درگاه ناموفق بود.", true);
      } finally {
        button.disabled = false;
        button.querySelector("span").textContent = "ثبت کمک و اتصال به درگاه";
      }
    });
    return modal;
  }

  function showMessage(text, isError) {
    var message = document.getElementById("mcd-message");
    if (!message) return;
    message.textContent = text;
    message.className = "mcd-message show " + (isError ? "error" : "success");
  }

  function open(options) {
    options = options || {};
    state.campaignId = String(options.id || options.campaign_id || "");
    state.centerSlug = String(options.branch_slug || options.center_slug || "hq");
    state.title = String(options.title || "کمپین");
    var modal = ensure();
    document.getElementById("mcd-title").textContent = "کمک برای: " + state.title;
    document.getElementById("mcd-message").className = "mcd-message";
    document.getElementById("mcd-form").reset();
    var defaultAmount = 2000000;
    document.getElementById("mcd-amount").value = formatAmount(defaultAmount);
    document.getElementById("mcd-amount-words").textContent = numberToPersianWords(defaultAmount / 10) + " تومان";
    modal.querySelectorAll("[data-amount]").forEach(function (button) {
      button.classList.toggle("active", Number(button.getAttribute("data-amount")) === defaultAmount);
    });
    modal.style.display = "flex";
    requestAnimationFrame(function () { modal.classList.add("open"); modal.setAttribute("aria-hidden", "false"); });
    setTimeout(function () { document.getElementById("mcd-name").focus(); }, 80);
  }

  function close() {
    var modal = document.getElementById("mcd-modal");
    if (!modal) return;
    modal.classList.remove("open");
    modal.setAttribute("aria-hidden", "true");
    setTimeout(function () { if (!modal.classList.contains("open")) modal.style.display = "none"; }, 220);
  }

  window.MacsaCampaignDonation = { open: open, close: close };
})();
