<?php
require_once __DIR__ . '/../../../core/site-locale.php';
$homeCampaignSources = [];
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $homeCampaignSources = $pdo->query(
            "SELECT id, name, slug, is_hq FROM branches WHERE status = 'active' ORDER BY is_hq DESC, id ASC"
        )->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $homeCampaignSources=array_map(static fn($branch)=>content_branch_context($pdo,$branch),$homeCampaignSources);
    } catch (Throwable $error) {
        error_log('[home-campaigns] branch list: ' . $error->getMessage());
    }
}
?>
<section class="hc" id="home-campaigns" aria-labelledby="hc-title">
  <div class="hc-wrap">
    <div class="hc-head">
      <div>
        <span class="hc-kicker"><?= site_e('با هم، برای امید') ?></span>
        <h2 id="hc-title"><?= site_e('آخرین کمپین‌های مراکز مکسا') ?></h2>
        <p><?= site_e('تازه‌ترین فرصت‌های همراهی با بیماران در مراکز مختلف مکسا') ?></p>
      </div>
      <div class="hc-actions" aria-label="<?= site_e('کنترل کروسل') ?>">
        <button type="button" class="hc-arrow" id="hc-prev" aria-label="<?= site_e('کمپین‌های قبلی') ?>">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <button type="button" class="hc-arrow" id="hc-next" aria-label="<?= site_e('کمپین‌های بعدی') ?>">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        </button>
      </div>
    </div>

    <div class="hc-viewport" id="hc-viewport" tabindex="0" aria-live="polite" aria-busy="true">
      <div class="hc-track" id="hc-track">
        <?php foreach ($homeCampaignSources as $index => $_source): ?>
          <article class="bh-card hc-skeleton" data-skeleton="<?= (int)$index ?>" aria-hidden="true">
            <div class="bh-thumb hc-shimmer"></div>
            <div class="bh-body">
              <span class="hc-sk-line hc-sk-tag hc-shimmer"></span>
              <span class="hc-sk-line hc-sk-title hc-shimmer"></span>
              <span class="hc-sk-line hc-sk-text hc-shimmer"></span>
              <span class="hc-sk-line hc-sk-text short hc-shimmer"></span>
              <div class="hc-sk-progress hc-shimmer"></div>
              <div class="hc-sk-button hc-shimmer"></div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="hc-dots" id="hc-dots" aria-label="<?= site_e('صفحات کروسل') ?>"></div>
    <p class="hc-empty" id="hc-empty" hidden><?= site_e('در حال حاضر کمپین فعالی برای نمایش وجود ندارد.') ?></p>
  </div>
</section>

<style>
  .hc{
    --teal:#007b7a;--teal-d:#006665;--teal-l:#10aeb8;--orange:#f5a623;
    --text:#2f3437;--muted:#7e858a;--line:#e6e8ea;--surface:#fff;
    --radius:18px;--shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 6px rgba(16,40,40,.05);
    --shadow-md:0 10px 24px rgba(16,40,40,.07),0 4px 10px rgba(16,40,40,.05);
    --ease:cubic-bezier(.2,.8,.2,1);
    direction:var(--site-direction, rtl);color:var(--text);font-family:'Vazirmatn',Tahoma,sans-serif;
    padding:78px 0 72px;background:linear-gradient(180deg,#fff 0%,#f8fbfb 100%);overflow:hidden;
  }
  .hc *{box-sizing:border-box}
  .hc-wrap{max-width:1240px;margin:0 auto;padding:0 20px}
  .hc-head{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;margin-bottom:30px}
  .hc-kicker{display:inline-flex;color:var(--teal);background:rgba(0,123,122,.09);padding:5px 12px;
    border-radius:999px;font-size:12px;font-weight:800;margin-bottom:10px}
  .hc-head h2{margin:0;font-size:30px;line-height:1.45;font-weight:900;position:relative;padding-bottom:12px}
  .hc-head h2::after{content:"";position:absolute;right:0;bottom:0;width:48px;height:3px;border-radius:999px;background:var(--teal)}
  .hc-head p{margin:12px 0 0;color:var(--muted);font-size:14px;line-height:1.9}
  .hc-actions{display:flex;gap:9px;flex:0 0 auto}
  .hc-arrow{width:44px;height:44px;border-radius:50%;border:1px solid var(--line);background:#fff;color:var(--teal);
    display:grid;place-items:center;cursor:pointer;box-shadow:var(--shadow-sm);transition:.2s var(--ease)}
  .hc-arrow:hover:not(:disabled){transform:translateY(-2px);color:#fff;background:var(--teal);border-color:var(--teal)}
  .hc-arrow:disabled{opacity:.35;cursor:default}
  .hc-arrow svg{width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
  .hc-viewport{overflow-x:auto;overflow-y:hidden;scroll-snap-type:x mandatory;scroll-behavior:smooth;
    scrollbar-width:none;padding:4px 2px 18px;outline:none}
  .hc-viewport::-webkit-scrollbar{display:none}
  .hc-track{display:flex;gap:24px;min-width:100%}

  /* Same card grammar used by the campaign section on branch pages. */
  .hc .bh-card{flex:0 0 calc((100% - 72px)/4);min-width:0;scroll-snap-align:start;display:flex;flex-direction:column;
    background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);overflow:hidden;
    box-shadow:var(--shadow-sm);transition:transform .28s var(--ease),box-shadow .28s var(--ease),border-color .28s}
  .hc .bh-card:not(.hc-skeleton):hover{transform:translateY(-4px);box-shadow:var(--shadow-md);border-color:rgba(0,123,122,.28)}
  .hc .bh-thumb{position:relative;aspect-ratio:16/9;background:#eef1f2;overflow:hidden}
  .hc .bh-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform .45s var(--ease)}
  .hc .bh-card:hover .bh-thumb img{transform:scale(1.04)}
  .hc .bh-body{padding:18px 18px 20px;display:flex;flex-direction:column;gap:10px;flex:1}
  .hc .bh-tag{display:inline-flex;align-items:center;gap:5px;align-self:flex-start;font-size:11px;font-weight:700;
    color:var(--teal);background:rgba(0,123,122,.10);padding:3px 10px;border-radius:999px}
  .hc .bh-card h3{margin:0;font-size:17px;line-height:1.6;font-weight:800;color:#1c2022}
  .hc .bh-card p{margin:0;color:var(--muted);font-size:13.5px;line-height:1.9;min-height:51px}
  .hc .bh-prog{margin-top:auto;padding-top:4px}
  .hc .bh-prog-info{display:flex;justify-content:space-between;gap:8px;font-size:11.5px;color:var(--muted);margin-bottom:6px}
  .hc .bh-prog-bar{height:8px;border-radius:999px;background:#eef1f2;overflow:hidden}
  .hc .bh-prog-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--teal-l),var(--teal))}
  .hc .bh-prog-badge{font-size:12px;font-weight:800;color:var(--teal);white-space:nowrap}
  .hc .bh-help{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:100%;margin-top:12px;
    padding:12px 18px;border:none;cursor:pointer;border-radius:13px;color:#fff;font-weight:700;font-size:14px;
    font-family:inherit;background:linear-gradient(135deg,var(--teal),var(--teal-d));text-decoration:none;
    box-shadow:0 10px 22px -10px rgba(0,123,122,.7);transition:transform .15s var(--ease),box-shadow .22s var(--ease)}
  .hc .bh-help:hover{transform:translateY(-2px);box-shadow:0 16px 30px -10px rgba(0,123,122,.85)}
  .hc .bh-help svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}

  .hc-skeleton{pointer-events:none}
  .hc-shimmer{position:relative;overflow:hidden;background:#edf1f1!important}
  .hc-shimmer::after{content:"";position:absolute;inset:0;transform:translateX(100%);
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.78),transparent);animation:hc-shimmer 1.25s infinite}
  .hc-sk-line{display:block;height:13px;border-radius:7px}
  .hc-sk-tag{width:34%;height:22px;border-radius:999px}.hc-sk-title{width:78%;height:20px;margin-top:2px}
  .hc-sk-text{width:100%}.hc-sk-text.short{width:68%}.hc-sk-progress{height:8px;border-radius:999px;margin-top:auto}
  .hc-sk-button{height:43px;border-radius:13px;margin-top:12px}
  @keyframes hc-shimmer{100%{transform:translateX(-100%)}}
  @media(prefers-reduced-motion:reduce){.hc-shimmer::after{animation:none}.hc-viewport{scroll-behavior:auto}}

  .hc-dots{display:flex;align-items:center;justify-content:center;gap:7px;min-height:12px;margin-top:4px}
  .hc-dot{width:8px;height:8px;border:0;padding:0;border-radius:999px;background:#cfd8d8;cursor:pointer;transition:.2s}
  .hc-dot.active{width:24px;background:var(--orange)}
  .hc-empty{text-align:center;color:var(--muted);background:#fff;border:1px dashed var(--line);border-radius:16px;padding:28px;margin:0}
  @media(max-width:900px){.hc .bh-card{flex-basis:calc((100% - 24px)/2)}}
  @media(max-width:620px){
    .hc{padding:54px 0 48px}.hc-wrap{padding:0 14px}.hc-head{align-items:center;margin-bottom:22px}
    .hc-head h2{font-size:23px}.hc-head p{font-size:13px}.hc-actions{display:none}
    .hc-track{gap:14px}.hc .bh-card{flex-basis:88%;scroll-snap-align:center}.hc .bh-card p{min-height:0}
  }
</style>

<script src="/js/campaign-payment.js?v=20260927"></script>
<script src="/js/campaign-donation-ui.js?v=20260927"></script>
<script>
(function(){
  'use strict';
  var root=document.getElementById('home-campaigns');
  if(!root) return;
  var sources=<?= json_encode(array_values($homeCampaignSources), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;
  var viewport=document.getElementById('hc-viewport');
  var track=document.getElementById('hc-track');
  var dots=document.getElementById('hc-dots');
  var empty=document.getElementById('hc-empty');
  var prev=document.getElementById('hc-prev');
  var next=document.getElementById('hc-next');
  var skeletons=Array.prototype.slice.call(track.querySelectorAll('.hc-skeleton'));
  var filled=0;
  var placeholder='data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="1000" height="560"><rect width="100%" height="100%" fill="#e9edee"/><text x="50%" y="52%" dominant-baseline="middle" text-anchor="middle" fill="#9aa4a6" font-size="40" font-family="Tahoma">مکسا</text></svg>');

  function esc(value){return String(value==null?'':value).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
  function cut(value,max){var text=String(value||'').replace(/<[^>]*>/g,'').trim();return text.length>max?text.slice(0,max)+'…':text;}
  function money(value){return Number(value||0).toLocaleString('fa-IR');}
  function safeImage(value){var url=String(value||'').trim();return /^(?:https?:\/\/|\/)/i.test(url)?url:placeholder;}

  function makeCard(campaign){
    var target=Number(campaign.target_amount)||0;
    var collected=Number(campaign.collected_amount)||0;
    var percent=target>0?Math.min(100,Math.round(collected/target*100)):0;
    var progress=target>0?('<div class="bh-prog"><div class="bh-prog-info"><span class="bh-prog-badge">'+money(percent)+'٪</span>'+
      '<span>'+money(collected)+' از '+money(target)+' تومان</span></div>'+
      '<div class="bh-prog-bar"><div class="bh-prog-fill" style="width:'+percent+'%"></div></div></div>'):'';
    var card=document.createElement('article');
    card.className='bh-card';
    card.innerHTML='<div class="bh-thumb"><img alt=""></div>'+
      '<div class="bh-body">'+
        '<span class="bh-tag">مرکز '+esc(campaign.branch_name||<?= site_js_text('مکسا') ?>)+'</span>'+
        '<h3>'+esc(campaign.title||'')+'</h3>'+
        '<p>'+esc(cut(campaign.description||campaign.short_description||'',90))+'</p>'+
        progress+
        '<button type="button" class="bh-help">'+
          '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/></svg>همین حالا کمک کنید</button>'+
      '</div>';
    var image=card.querySelector('img');
    image.src=safeImage(campaign.image_url);
    image.addEventListener('error',function(){image.src=placeholder;},{once:true});
    card.querySelector('.bh-help').addEventListener('click',function(){
      if(window.MacsaCampaignDonation) window.MacsaCampaignDonation.open(campaign);
    });
    return card;
  }

  function cardsPerView(){return window.innerWidth<=620?1:(window.innerWidth<=900?2:4);}
  function realCards(){return Array.prototype.slice.call(track.querySelectorAll('.bh-card:not(.hc-skeleton)'));}
  function updateNavigation(){
    var cards=realCards();
    var pages=Math.max(1,Math.ceil(cards.length/cardsPerView()));
    var current=Math.min(pages-1,Math.max(0,Math.round(Math.abs(viewport.scrollLeft)/Math.max(1,viewport.clientWidth))));
    dots.innerHTML='';
    if(pages>1){for(var i=0;i<pages;i++){(function(index){var dot=document.createElement('button');dot.type='button';dot.className='hc-dot'+(index===current?' active':'');dot.setAttribute('aria-label','صفحه '+money(index+1));dot.addEventListener('click',function(){go(index*cardsPerView());});dots.appendChild(dot);})(i);}}
    prev.disabled=current<=0;next.disabled=current>=pages-1;
  }
  function go(index){var cards=realCards();if(!cards.length)return;cards[Math.max(0,Math.min(index,cards.length-1))].scrollIntoView({behavior:'smooth',block:'nearest',inline:'start'});}

  function accept(campaign){
    if(!campaign||!skeletons[filled]) return;
    skeletons[filled].replaceWith(makeCard(campaign));
    filled++;
    updateNavigation();
  }

  function load(source){
    var controller=typeof AbortController==='function'?new AbortController():null;
    var timer=controller?setTimeout(function(){controller.abort();},8500):null;
    return fetch('/dashboard/campaign-latest.php?branch='+encodeURIComponent(source.slug),{
      headers:{Accept:'application/json'},signal:controller?controller.signal:undefined
    }).then(function(response){return response.ok?response.json():null;})
      .then(function(payload){if(payload&&payload.status==='success'&&payload.data)accept(payload.data);})
      .catch(function(){return null;})
      .finally(function(){if(timer)clearTimeout(timer);});
  }

  prev.addEventListener('click',function(){var cards=realCards(),first=cards[0];if(!first)return;var width=first.getBoundingClientRect().width+24;viewport.scrollBy({left:width*cardsPerView(),behavior:'smooth'});});
  next.addEventListener('click',function(){var cards=realCards(),first=cards[0];if(!first)return;var width=first.getBoundingClientRect().width+24;viewport.scrollBy({left:-width*cardsPerView(),behavior:'smooth'});});
  var scrollTimer;
  viewport.addEventListener('scroll',function(){clearTimeout(scrollTimer);scrollTimer=setTimeout(updateNavigation,100);},{passive:true});
  window.addEventListener('resize',updateNavigation);

  if(!sources.length){viewport.hidden=true;empty.hidden=false;viewport.setAttribute('aria-busy','false');return;}
  Promise.allSettled(sources.map(load)).then(function(){
    skeletons.slice(filled).forEach(function(item){item.remove();});
    viewport.setAttribute('aria-busy','false');
    if(!filled){viewport.hidden=true;empty.hidden=false;}
    updateNavigation();
  });
})();
</script>
