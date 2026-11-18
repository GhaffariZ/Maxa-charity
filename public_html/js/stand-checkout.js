(function () {
  'use strict';
  const storageKey = 'macsa-stand-checkout';
  let attempt;
  async function request(path, token, body) {
    const response = await fetch(path, {method:'POST', credentials:'include',
      headers:{'Content-Type':'application/json', Accept:'application/json', Authorization:'Bearer ' + token},
      body:JSON.stringify(body)});
    const result = await response.json();
    if (!response.ok || !result.success) {
      const error = new Error(result.error?.message || 'شروع پرداخت سفارش ناموفق بود.');
      error.status = response.status;
      throw error;
    }
    return result.data;
  }
  async function start(payload, token) {
    const signature = JSON.stringify(payload);
    try { attempt = attempt || JSON.parse(sessionStorage.getItem(storageKey)); } catch (_) {}
    if (!attempt || attempt.signature !== signature) {
      attempt = {signature, key:crypto.randomUUID().replaceAll('-', '')};
      try { sessionStorage.setItem(storageKey, JSON.stringify(attempt)); } catch (_) {}
    }
    const order = await request('/api/orders', token, {...payload, checkout_key:attempt.key});
    const payment = await request('/api/orders/' + encodeURIComponent(order.tracking_code) + '/payment', token,
      {mobile:payload.sender_phone});
    if (payment.status === 'success' || payment.status === 'failed') {
      attempt = null;
      try { sessionStorage.removeItem(storageKey); } catch (_) {}
      if (payment.status === 'failed') throw new Error('پرداخت سفارش تأیید نشد؛ برای تلاش مجدد دوباره ثبت سفارش کنید.');
      return payment;
    }
    if (!payment.redirect_url) throw new Error('نشانی درگاه دریافت نشد؛ دوباره تلاش کنید.');
    if (payment.redirect_method === 'POST') {
      const form = document.createElement('form');
      form.method = 'POST'; form.action = payment.redirect_url; form.hidden = true;
      for (const [name, value] of Object.entries(payment.redirect_fields || {})) {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = name; input.value = String(value); form.appendChild(input);
      }
      document.body.appendChild(form);
      HTMLFormElement.prototype.submit.call(form);
    } else window.location.assign(payment.redirect_url);
    return payment;
  }
  window.MaksaStandCheckout = {start};
})();
