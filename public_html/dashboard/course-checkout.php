<?php
/* ============================================================================
 *  تسویه‌حساب و خرید دوره — آکادمی مکسا
 *  سبد خرید (localStorage) + خلاصه‌ی پرداخت + ثبت‌نام
 * ========================================================================== */
require __DIR__ . '/course-db.php';
require __DIR__ . '/course-ui.php';

/* همه‌ی دوره‌های منتشرشده برای غنی‌سازی اقلام سبد در سمت کلاینت */
$all = load_courses($pdo, $coursesSchemaReady, 'published');
$map = [];
foreach ($all as $c) {
  $id = (int)($c['id'] ?? 0);
  $isFree = !empty($c['is_free']) || (int)($c['price'] ?? 0) === 0;
  $price = (float)($c['price'] ?? 0);
  $disc = ($c['discount_price'] ?? null) ? (float)$c['discount_price'] : 0;
  $map[$id] = [
    'id'=>$id, 'title'=>$c['title'] ?? '', 'instructor'=>$c['instructor'] ?? 'مدرس مکسا',
    'category'=>$c['category'] ?? '', 'thumbnail'=>$c['thumbnail'] ?? '', 'accent'=>course_accent($id),
    'price'=>(int)$price, 'eff'=>$isFree?0:($disc>0?(int)$disc:(int)$price), 'free'=>$isFree?1:0,
  ];
}

$pageCss = <<<CSS
.co-wrap{display:grid;grid-template-columns:1fr 360px;gap:26px;align-items:start;margin-top:8px}
@media(max-width:900px){.co-wrap{grid-template-columns:1fr}}
.ci{display:flex;align-items:center;gap:14px;padding:15px;border:1px solid var(--color-border);border-radius:16px;background:var(--color-surface);margin-bottom:12px;transition:box-shadow .2s}
.ci:hover{box-shadow:var(--shadow-md)}
.ci-thumb{width:96px;height:62px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;color:#fff;overflow:hidden;position:relative}
.ci-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.ci-thumb .ic{width:22px;height:22px;opacity:.85}
.ci-info{flex:1;min-width:0}
.ci-title{font-weight:700;font-size:14px;line-height:1.5}
.ci-inst{font-size:12px;color:var(--color-muted);margin-top:2px}
.ci-cat{font-size:10.5px;font-weight:700;color:var(--color-primary-dark);background:var(--primary-08);padding:3px 9px;border-radius:99px;display:inline-block;margin-top:6px}
.ci-right{text-align:left;flex-shrink:0;display:flex;flex-direction:column;align-items:flex-end;gap:8px}
.ci-price{font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}
.ci-price small{font-size:10px;font-weight:600;color:var(--color-muted)}
.ci-remove{font-size:11.5px;color:var(--danger);font-weight:600;display:inline-flex;align-items:center;gap:4px}
.ci-remove .ic{width:13px;height:13px}
.summary{position:sticky;top:86px}
.sum-row{display:flex;justify-content:space-between;align-items:center;font-size:13.5px;padding:9px 0}
.sum-row.total{border-top:1px solid var(--color-border);margin-top:6px;padding-top:14px;font-size:16px;font-weight:800}
.sum-row .muted{color:var(--color-muted)}
.sum-row .green{color:var(--success);font-weight:700}
.coupon{display:flex;gap:8px;margin:14px 0}
.coupon input{flex:1}
.pay-methods{display:flex;gap:10px;margin:14px 0}
.pay-m{flex:1;border:1.5px solid var(--color-border);border-radius:13px;padding:12px;text-align:center;cursor:pointer;font-size:12px;font-weight:700;color:var(--color-muted);transition:all .2s}
.pay-m.on{border-color:var(--color-primary-light);background:var(--primary-08);color:var(--color-primary-dark)}
.pay-m .ic{width:22px;height:22px;margin:0 auto 6px}
.empty-cart{text-align:center;padding:60px 20px}
.empty-cart .ei{width:80px;height:80px;border-radius:22px;display:grid;place-items:center;margin:0 auto 16px;background:var(--primary-08);color:var(--color-primary)}
.empty-cart .ei .ic{width:38px;height:38px}
.trust{display:flex;align-items:center;gap:8px;justify-content:center;font-size:11.5px;color:var(--color-muted);margin-top:14px}
.trust .ic{width:15px;height:15px;color:var(--success)}
/* مودال موفقیت */
.ok-ov{position:fixed;inset:0;background:rgba(16,40,40,.5);backdrop-filter:blur(3px);z-index:100002;display:none;place-items:center;padding:20px}
.ok-ov.show{display:grid}
.ok-card{background:var(--color-surface);border-radius:22px;box-shadow:var(--shadow-lg);max-width:420px;width:100%;padding:34px 26px;text-align:center;animation:fadeUp .4s var(--ease)}
.ok-card .oic{width:74px;height:74px;border-radius:50%;display:grid;place-items:center;margin:0 auto 18px;background:rgba(22,163,122,.14);color:var(--success)}
.ok-card .oic .ic{width:38px;height:38px;stroke-width:2.5}
.ok-card h2{font-size:21px;font-weight:800;margin-bottom:8px}
.ok-card p{font-size:13.5px;color:var(--color-muted);margin-bottom:22px;line-height:1.8}
CSS;

course_site_head('تسویه‌حساب', $pageCss);
?>

<div class="page">
  <div class="page-head">
    <div class="ph-icon"><?= cic('cart') ?></div>
    <div><h1>سبد خرید و تسویه‌حساب</h1><div class="ph-sub">دوره‌های انتخابی خود را بررسی و ثبت‌نام را تکمیل کنید.</div></div>
  </div>

  <div id="cartArea">
    <div class="co-wrap" id="coWrap" style="display:none">
      <!-- اقلام -->
      <div>
        <div style="font-weight:800;font-size:15px;margin-bottom:14px"><span id="itemCount">۰</span> دوره در سبد شما</div>
        <div id="items"></div>
        <a href="/courses" class="btn btn-ghost" target="_top" style="margin-top:6px"><?= cic('arrow') ?> افزودن دوره‌ی بیشتر</a>
      </div>
      <!-- خلاصه -->
      <aside class="summary card card-pad">
        <h3 style="font-size:16px;font-weight:800;margin-bottom:8px">خلاصه‌ی سفارش</h3>
        <div class="sum-row"><span class="muted">جمع کل دوره‌ها</span><span id="sumSubtotal">۰ تومان</span></div>
        <div class="sum-row"><span class="muted">تخفیف</span><span class="green" id="sumDiscount">۰ تومان</span></div>
        <div class="coupon">
          <input class="inp" id="couponInput" placeholder="کد تخفیف (مثلاً MAXA20)">
          <button class="btn btn-soft" id="couponBtn">اعمال</button>
        </div>
        <div class="lbl" style="margin-top:4px">روش پرداخت</div>
        <div class="pay-methods" id="payMethods">
          <div class="pay-m on" data-m="zarin"><?= cic('wallet') ?>زرین‌پال</div>
          <div class="pay-m" data-m="card"><?= cic('cart') ?>کارت به کارت</div>
        </div>
        <div class="sum-row total"><span>مبلغ قابل پرداخت</span><span id="sumTotal">۰ تومان</span></div>
        <button class="btn btn-primary btn-block btn-lg" id="payBtn" style="margin-top:14px"><?= cic('lock') ?> پرداخت امن و ثبت‌نام</button>
        <div class="trust"><?= cic('check') ?> پرداخت امن · ضمانت بازگشت وجه تا ۷ روز</div>
      </aside>
    </div>

    <!-- سبد خالی -->
    <div class="empty-cart card card-pad" id="emptyCart" style="display:none">
      <div class="ei"><?= cic('cart') ?></div>
      <h2 style="font-size:19px;font-weight:800">سبد خرید شما خالی است</h2>
      <p style="color:var(--color-muted);margin:8px 0 20px">هنوز دوره‌ای انتخاب نکرده‌اید. از فروشگاه ما دیدن کنید!</p>
      <a href="/courses" class="btn btn-primary" target="_top"><?= cic('grad') ?> مشاهده‌ی دوره‌ها</a>
    </div>
  </div>
</div>

<!-- مودال موفقیت -->
<div class="ok-ov" id="okModal">
  <div class="ok-card">
    <div class="oic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
    <h2>ثبت‌نام موفق! 🎉</h2>
    <p>دوره‌های شما با موفقیت ثبت شد. هم‌اکنون می‌توانید یادگیری را شروع کنید.</p>
    <a href="/courses/my" class="btn btn-primary btn-block btn-lg" target="_top"><?= cic('play') ?> رفتن به دوره‌های من</a>
    <a href="/courses" class="btn btn-ghost btn-block" target="_top" style="margin-top:10px">ادامه‌ی خرید</a>
  </div>
</div>

<div class="toast" id="toast"><div class="ti"><?= cic('check') ?></div><span id="toastMsg"></span></div>

<script>
(function(){
  "use strict";
  var COURSES = <?= json_encode($map, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var toFa=function(s){return String(s).replace(/\d/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[d];});};
  var sep=function(n){return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g,'٬');};
  var money=function(n){return n>0?(toFa(sep(n))+' تومان'):'رایگان';};
  var $=function(id){return document.getElementById(id);};
  var BOOK='<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>';
  var X='<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';

  function getCart(){ try{ return JSON.parse(localStorage.getItem('maxa-cart')||'[]'); }catch(e){ return []; } }
  function setCart(c){ try{ localStorage.setItem('maxa-cart', JSON.stringify(c)); }catch(e){} updateNavCart(); }
  function updateNavCart(){ if(window.maxaCartSync) window.maxaCartSync(); }

  var coupon=0; // درصد تخفیف کد

  function info(item){ var c=COURSES[item.id]; if(c) return c; // fallback به داده‌ی ذخیره‌شده
    return {id:item.id,title:item.title||'دوره',instructor:item.instructor||'مدرس مکسا',category:'',thumbnail:'',accent:'#007b7a',price:item.price||0,eff:item.price||0,free:(item.price||0)===0?1:0}; }

  function render(){
    var cart=getCart();
    if(!cart.length){ $('coWrap').style.display='none'; $('emptyCart').style.display='block'; updateNavCart(); return; }
    $('coWrap').style.display='grid'; $('emptyCart').style.display='none';
    var subtotal=0, totalOrig=0, html='';
    cart.forEach(function(it){
      var c=info(it); subtotal+=c.eff; totalOrig+=(c.price||c.eff);
      html+='<div class="ci">'+
        '<div class="ci-thumb" style="background:linear-gradient(135deg,'+c.accent+','+c.accent+'cc)">'+(c.thumbnail?'<img src="'+c.thumbnail+'" alt="">':BOOK)+'</div>'+
        '<div class="ci-info"><div class="ci-title">'+c.title+'</div><div class="ci-inst">'+c.instructor+'</div>'+(c.category?'<span class="ci-cat">'+c.category+'</span>':'')+'</div>'+
        '<div class="ci-right"><span class="ci-price">'+(c.eff>0?(toFa(sep(c.eff))+'<small> ت</small>'):'<span style="color:var(--violet)">رایگان</span>')+'</span>'+
        '<button class="ci-remove" data-id="'+c.id+'">'+X+' حذف</button></div></div>';
    });
    $('items').innerHTML=html;
    $('itemCount').textContent=toFa(cart.length);
    var discountAmt=(totalOrig-subtotal); // تخفیف دوره‌ها
    var couponAmt=Math.round(subtotal*coupon/100);
    var total=Math.max(0, subtotal-couponAmt);
    $('sumSubtotal').textContent=money(totalOrig);
    $('sumDiscount').textContent='− '+money(discountAmt+couponAmt);
    $('sumTotal').textContent=money(total);
    $('items').querySelectorAll('.ci-remove').forEach(function(b){
      b.addEventListener('click', function(){ var id=+b.getAttribute('data-id'); setCart(getCart().filter(function(x){return x.id!==id;})); coupon=0; $('couponInput').value=''; render(); });
    });
  }

  function toast(m){ var t=$('toast'); $('toastMsg').textContent=m; t.classList.add('show'); clearTimeout(t._t); t._t=setTimeout(function(){t.classList.remove('show');},2600); }

  // کد تخفیف نمونه
  $('couponBtn').addEventListener('click', function(){
    var code=($('couponInput').value||'').trim().toUpperCase();
    if(code==='MAXA20'){ coupon=20; toast('کد تخفیف ۲۰٪ اعمال شد.'); }
    else if(code==='MAXA50'){ coupon=50; toast('کد تخفیف ۵۰٪ اعمال شد.'); }
    else { coupon=0; toast('کد تخفیف نامعتبر است.'); }
    render();
  });
  $('payMethods').addEventListener('click', function(e){ var m=e.target.closest('.pay-m'); if(!m) return;
    $('payMethods').querySelectorAll('.pay-m').forEach(function(x){x.classList.remove('on');}); m.classList.add('on'); });

  // پرداخت → افزودن به دوره‌های خریداری‌شده
  $('payBtn').addEventListener('click', function(){
    var cart=getCart(); if(!cart.length) return;
    var purchased=[]; try{ purchased=JSON.parse(localStorage.getItem('maxa-purchased')||'[]'); }catch(e){}
    cart.forEach(function(it){ if(purchased.indexOf(it.id)<0) purchased.push(it.id); });
    try{ localStorage.setItem('maxa-purchased', JSON.stringify(purchased)); }catch(e){}
    setCart([]); // خالی‌کردن سبد
    $('okModal').classList.add('show');
  });

  render(); updateNavCart();
})();
</script>
<?php course_site_footer(); ?>
