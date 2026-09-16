(function () {
  "use strict";

  const ENDPOINT = "/dashboard/campaign-payment.php";

  function idempotencyKey() {
    const random = window.crypto && typeof window.crypto.randomUUID === "function"
      ? window.crypto.randomUUID()
      : Date.now().toString(36) + Math.random().toString(36).slice(2);
    return "macsa:payment:" + random;
  }

  function popupMessage(popup, message) {
    if (!popup || popup.closed) return;
    popup.document.open();
    popup.document.write("<!doctype html><html lang='fa' dir='rtl'><meta charset='utf-8'><title>پرداخت مکسا</title><body style='font-family:Tahoma,sans-serif;text-align:center;padding:12vh 20px;color:#174b49'><h2>" + message + "</h2></body></html>");
    popup.document.close();
  }

  function redirectPopup(popup, payment) {
    if (!payment.redirect_url) throw new Error("آدرس درگاه از سرویس دریافت نشد.");
    if ((payment.redirect_method || "GET").toUpperCase() === "POST") {
      popup.document.open();
      popup.document.write("<!doctype html><html><meta charset='utf-8'><body><form id='gateway' method='post'></form></body></html>");
      popup.document.close();
      const form = popup.document.getElementById("gateway");
      form.action = payment.redirect_url;
      Object.entries(payment.redirect_fields || {}).forEach(function (entry) {
        const input = popup.document.createElement("input");
        input.type = "hidden";
        input.name = entry[0];
        input.value = String(entry[1]);
        form.appendChild(input);
      });
      form.submit();
      return;
    }
    popup.location.replace(payment.redirect_url);
  }

  async function status(centerSlug, paymentId) {
    const query = new URLSearchParams({ center_slug: centerSlug, payment_id: paymentId });
    const response = await fetch(ENDPOINT + "?" + query.toString(), { headers: { Accept: "application/json" } });
    const body = await response.json().catch(function () { return {}; });
    if (!response.ok || body.status !== "success") throw new Error(body.message || "استعلام پرداخت ناموفق بود.");
    return body.data;
  }

  async function start(options) {
    const popup = window.open("about:blank", "macsaCampaignPayment", "width=520,height=720");
    if (!popup) throw new Error("برای اتصال به درگاه، نمایش پنجره جدید را در مرورگر مجاز کنید.");
    popupMessage(popup, "در حال اتصال به درگاه امن…");

    const payload = Object.assign({}, options, { idempotency_key: idempotencyKey() });
    delete payload.onSuccess;
    delete payload.onFailure;
    let response;
    try {
      response = await fetch(ENDPOINT, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify(payload),
      });
    } catch (error) {
      popup.close();
      throw error;
    }
    const body = await response.json().catch(function () { return {}; });
    if (!response.ok || body.status !== "success") {
      popup.close();
      throw new Error(body.message || "شروع پرداخت ناموفق بود.");
    }
    const payment = body.data;
    if (!payment || !payment.id) {
      popup.close();
      throw new Error("شناسه پرداخت از سرویس دریافت نشد.");
    }
    redirectPopup(popup, payment);

    let attempts = 0;
    const timer = window.setInterval(async function () {
      attempts += 1;
      try {
        const current = await status(options.center_slug || "hq", payment.id);
        if (current.status === "SUCCEEDED") {
          window.clearInterval(timer);
          if (!popup.closed) popup.close();
          if (typeof options.onSuccess === "function") options.onSuccess(current);
        } else if (current.status === "FAILED") {
          window.clearInterval(timer);
          if (!popup.closed) popup.close();
          if (typeof options.onFailure === "function") options.onFailure(current);
        }
      } catch (error) {
        if (attempts >= 80) window.clearInterval(timer);
      }
      if (attempts >= 120) window.clearInterval(timer);
    }, 5000);
    return payment;
  }

  window.MaksaCampaignPayment = { start: start, status: status };
})();
