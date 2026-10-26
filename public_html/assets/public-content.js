/* Only public, read-only content feeds receive a language. Private APIs are untouched. */
(() => {
  const locale=document.documentElement.lang;
  if(!['fa','ar','en'].includes(locale))return;
  const feeds=new Set(['/api-employees.php','/api-stories.php','/dashboard/hero-list.php','/dashboard/recent-news-feed.php','/dashboard/stories-feed.php','/dashboard/branch-courses-feed.php','/dashboard/campaign-latest.php','/dashboard/campaign-list.php','/api/stands','/api/stands/provinces']);
  const fetchOriginal=window.fetch;
  window.fetch=function(input,options){
    const method=String(options?.method||(input instanceof Request?input.method:'GET')).toUpperCase();
    if(method==='GET'){
      const url=new URL(input instanceof Request?input.url:input,document.baseURI);
      const path=url.pathname.replace(/^\/(?:en|ar)(?=\/)/,'');
      if(url.origin===location.origin&&feeds.has(path)){
        url.pathname=path;url.searchParams.set('lang',locale);
        input=input instanceof Request?new Request(url,input):url.href;
      }
    }
    return fetchOriginal.call(this,input,options);
  };
})();
