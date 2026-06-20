/* Check for unread ticket replies and badge the FAB.
   Lives in an external file because the /benefactor-dashboard CSP
   (`script-src 'self'`) blocks inline scripts. */
(function(){
  function getToken(){
    var keys=['access_token','token','jwt','maxa_token','auth_token','maxa_access_token'];
    for(var i=0;i<keys.length;i++){
      try{
        var v=localStorage.getItem(keys[i])||sessionStorage.getItem(keys[i]);
        if(v && v.split('.').length===3) return v;
      }catch(e){}
    }
    var objKeys=['auth','user','maxa_auth','maxa_user','benefactor_auth'];
    for(var j=0;j<objKeys.length;j++){
      try{
        var raw=localStorage.getItem(objKeys[j])||sessionStorage.getItem(objKeys[j]);
        if(!raw) continue;
        var obj=JSON.parse(raw);
        var t=obj&&(obj.token||obj.access_token||obj.jwt||(obj.data&&(obj.data.token||obj.data.access_token)));
        if(t && typeof t==='string' && t.split('.').length===3) return t;
      }catch(e){}
    }
    return null;
  }
  function checkUnread(){
    var token=getToken();
    if(!token) return;
    fetch('/api/tickets',{headers:{'Authorization':'Bearer '+token,'Content-Type':'application/json'}})
      .then(function(r){return r.ok?r.json():null;})
      .then(function(json){
        if(!json||!json.data) return;
        var tickets=json.data.tickets||[];
        var unread=tickets.filter(function(t){return t.has_unread;}).length;
        var fab=document.getElementById('maxa-ticket-fab');
        var badge=document.getElementById('fabBadge');
        if(!fab||!badge) return;
        if(unread>0){
          fab.classList.add('has-unread');
          badge.textContent=unread>9?'۹+':String(unread);
        } else {
          fab.classList.remove('has-unread');
        }
      })
      .catch(function(){});
  }
  /* Run after page loads to avoid blocking the React app */
  if(document.readyState==='complete'){
    setTimeout(checkUnread,2000);
  } else {
    window.addEventListener('load',function(){setTimeout(checkUnread,2000);});
  }
})();
