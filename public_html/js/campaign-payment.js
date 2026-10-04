(function () {
  "use strict";

  const ENDPOINT = "/dashboard/campaign-payment.php";

  function idempotencyKey() {
    const random = window.crypto && typeof window.crypto.randomUUID === "function"
      ? window.crypto.randomUUID()
      : Date.now().toString(36) + Math.random().toString(36).slice(2);
    return "macsa:payment:" + random;
  }

  function redirectToGateway(payment) {
    if (!payment.redirect_url) throw new Error("آدرس درگاه از سرویس دریافت نشد.");
    if ((payment.redirect_method || "GET").toUpperCase() === "POST") {
      const form = document.createElement("form");
      form.method = "POST";
      form.target = "_self";
      form.hidden = true;
      form.action = payment.redirect_url;
      Object.entries(payment.redirect_fields || {}).forEach(function (entry) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = entry[0];
        input.value = String(entry[1]);
        form.appendChild(input);
      });
      document.body.appendChild(form);
      HTMLFormElement.prototype.submit.call(form);
      return;
    }
    window.location.assign(payment.redirect_url);
  }

  async function status(centerSlug, paymentId) {
    const query = new URLSearchParams({ center_slug: centerSlug, payment_id: paymentId });
    const response = await fetch(ENDPOINT + "?" + query.toString(), { headers: { Accept: "application/json" } });
    const body = await response.json().catch(function () { return {}; });
    if (!response.ok || body.status !== "success") throw new Error(body.message || "استعلام پرداخت ناموفق بود.");
    return body.data;
  }

  async function start(options) {
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
      throw error;
    }
    const body = await response.json().catch(function () { return {}; });
    if (!response.ok || body.status !== "success") {
      throw new Error(body.message || "شروع پرداخت ناموفق بود.");
    }
    const payment = body.data;
    if (!payment || !payment.id) {
      throw new Error("شناسه پرداخت از سرویس دریافت نشد.");
    }
    // Verification and the final receipt are handled by the MACSA callback page.
    redirectToGateway(payment);
    return payment;
  }

  window.MaksaCampaignPayment = { start: start, status: status };
})();
