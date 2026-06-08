<section class="travel-stats-section">

<div class="travel-box">


<div class="travel-stats-container">

<div class="travel-content">

<!-- RIGHT SIDE -->
<div class="travel-right">

<div class="travel-header">
<h2>روایتی از آمار مکسا</h2>
<p>
این دستاورد حاصل اعتماد و همراهی شما، تلاش همکاران و حمایت ارزشمند خیرینی است که در مسیر خدمت به بیماران در کنار ما هستند.
</p>
</div>

<div class="travel-stats-top">

<div class="travel-stat">
<div class="travel-number" data-target="110392">0</div>
<div class="travel-label">جذب کمک های مردمی</div>
</div>

<div class="travel-stat">
<div class="travel-number" data-target="16">0</div>
<div class="travel-label">سال فعالیت</div>
</div>

<div class="travel-stat">
<div class="travel-number" data-target="690000">0</div>
<div class="travel-label">خدمات مکسا</div>
</div>

<div class="travel-stat">
<div class="travel-number" data-target="59000">0</div>
<div class="travel-label">تعداد کل بیماران</div>
</div>

</div>
</div>


<!-- LEFT SIDE -->
<div class="travel-left">

<div class="iran-map-box">

<img src="{{image1}}">

<div class="map-marker m1"><span class="marker-label">تهران</span></div>
<div class="map-marker m2"><span class="marker-label">مشهد</span></div>
<div class="map-marker m3"><span class="marker-label">اصفهان</span></div>
<div class="map-marker m4"><span class="marker-label">تبریز</span></div>
<div class="map-marker m5"><span class="marker-label">کرمان</span></div>
<div class="map-marker m6"><span class="marker-label">قم</span></div>
<div class="map-marker m7"><span class="marker-label">اهواز</span></div>
<div class="map-marker m8"><span class="marker-label">مرکز ارتباطات کشوری</span></div>
<div class="map-marker m9"><span class="marker-label">شعبه کاشان</span></div>


</div>

</div>

</div>
</div>

    <!-- Vazirmatn Variable Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
</section>



<style>
.travel-stats-section{
padding:90px 20px;
background:transparent;
}

/* box */

.travel-box{

max-width:1250px;
margin:auto;
position:relative;
overflow:hidden;
background:rgba(255,255,255,0.15);

backdrop-filter:blur(12px);
-webkit-backdrop-filter:blur(12px);

border:1px solid rgba(255,255,255,0.35);

padding:35px 30px 20px 30px;

border-radius:22px;

box-shadow:
0 20px 40px rgba(0,0,0,.08),
inset 0 1px 0 rgba(255,255,255,.6);

color:#0f5f63;

}

.travel-box::before{
content:"";
position:absolute;
top:0;
left:0;
right:0;
height:60px;

background:linear-gradient(
to bottom,
rgba(255,255,255,.45),
rgba(255,255,255,0)
);

border-radius:22px 22px 0 0;
pointer-events:none;
}
/* header */
.travel-header{
text-align:center;
margin-bottom:55px;   /* قبلا 35 بود */
}

.travel-header h2{
font-size:40px;
margin-bottom:8px;
font-weight:700;
}

.travel-header p{
opacity:.9;
font-size:22px;
color:#0F6F73;   /* teal تیره */
font-weight:500;

text-align:center;
max-width:820px;   /* باعث می‌شود متن در دو خط قرار بگیرد */
margin:10px auto 0 auto;
line-height:1.8;
}

/* stats */

.travel-stats-top{
display:flex;
justify-content:center;
gap:60px;
text-align:center;
flex-wrap:wrap;

margin-top:10px;
margin-bottom:25px;
}

.travel-stat{
min-width:160px;
}

.travel-number{
font-size:38px;
font-weight:700;
color:#0f6e72;
}

.travel-label{
font-size:14px;
color:#3c6e71;
}

/* map */

.iran-map-box{
position:relative;
max-width:1000px;   /* بین 900 و 1100 */
margin:0 auto -15px auto;
}

.iran-map-img{
width:100%;
opacity:.22;
transform:scale(1.04);   /* قبلا 1.15 بود */
transform-origin:center;
}

/* markers */

.map-marker{
position:absolute;
width:14px;
height:14px;
background:#FFBF00;
border-radius:50%;
box-shadow:0 0 0 8px rgba(255,255,255,.15);
}

.map-marker::after{
content:"";
position:absolute;
width:30px;
height:30px;
border-radius:50%;
border:1px solid rgba(255,255,255,.4);
top:50%;
left:50%;
transform:translate(-50%,-50%);
animation:pulse 3s infinite;
}

/* label */

.marker-label{
position:absolute;
top:-38px;
left:50%;
transform:translateX(-50%);
background:#fff;
color:#333;
padding:6px 12px;
border-radius:8px;
font-size:13px;
white-space:nowrap;
box-shadow:0 4px 12px rgba(0,0,0,.15);
}

.marker-label::after{
content:"";
position:absolute;
bottom:-6px;
left:50%;
transform:translateX(-50%);
border-width:6px;
border-style:solid;
border-color:#fff transparent transparent transparent;
}
.travel-content{
display:flex;
align-items:center;
justify-content:space-between;
gap:40px;
}

.travel-right{
flex:1;
text-align:right;
}

.travel-left{
flex:1;
display:flex;
flex-direction:column;
align-items:center;
justify-content:center;
text-align:center;
}


/* animation */

@keyframes pulse{
0%{transform:translate(-50%,-50%) scale(.5);opacity:.7}
100%{transform:translate(-50%,-50%) scale(1.6);opacity:0}
}

/* marker positions */

.m1{top:35%;left:40%;}
.m2{top:30%;left:70%;}
.m3{top:50%;left:50%;}
.m4{top:20%;left:15%;}
.m5{top:58%;left:62%;}
.m6{top:40%;left:35%;}
.m7{top:55%;left:25%;}
.m8{top:68%;left:17%;}
.m9{top:42%;left:45%;}

/* responsive */

@media(max-width:900px){

.travel-stats-top{
gap:30px;
}

.iran-map-box{
width:100%;
}

.travel-content{
flex-direction:column;
}

.travel-right{
text-align:center;
}

.travel-stats-top{
justify-content:center;
}

  .marker-label{
    display:inline-block !important;
    width:auto !important;
    max-width:none !important;
    white-space:nowrap !important;
    font-size:14px !important;
    padding:8px 14px !important;
    border-radius:10px !important;
  }

  .marker-label::after{
    bottom:-8px !important;
    border-width:7px !important;
  }

}
  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }

</style>
<script>
document.addEventListener("DOMContentLoaded",function(){

const counters=document.querySelectorAll(".travel-number");

const startCounter=(counter)=>{

const target=parseInt(counter.dataset.target);
let count=0;

const speed=20;
const step=Math.ceil(target/120);

const timer=setInterval(()=>{

count+=step;

if(count>=target){
counter.innerText=target;
clearInterval(timer);
}else{
counter.innerText=count;
}

},speed);

};

const observer=new IntersectionObserver(entries=>{
entries.forEach(entry=>{
if(entry.isIntersecting){

counters.forEach(counter=>{
startCounter(counter);
});

observer.disconnect();

}
});
});

observer.observe(document.querySelector(".travel-stats-section"));

});
</script>