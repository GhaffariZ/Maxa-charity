/* Wayglim HTML export engine, Sirolius: https://sirolius.com/playground/wayglim/
 * Includes Paper Shaders, Copyright 2026 Paper, Apache-2.0.
 * See wayglim-LICENSE.txt. Adapted for slow-device fallback and low-power graphics. */
(function () {
const PAPER_GRAIN_NOISE_GLSL=`vec3 permute(vec3 x){return mod(((x*34.0)+ 1.0)*x,289.0);}
float snoise(vec2 v){
const vec4 C=vec4(0.211324865405187,0.366025403784439,
-0.577350269189626,0.024390243902439);
vec2 i=floor(v+dot(v,C.yy));
vec2 x0=v-i+dot(i,C.xx);
vec2 i1;
i1=(x0.x>x0.y)?vec2(1.0,0.0):vec2(0.0,1.0);
vec4 x12=x0.xyxy+C.xxzz;
x12.xy -=i1;
i=mod(i,289.0);
vec3 p=permute(permute(i.y+vec3(0.0,i1.y,1.0))
+ i.x+vec3(0.0,i1.x,1.0));
vec3 m=max(0.5-vec3(dot(x0,x0),dot(x12.xy,x12.xy),
dot(x12.zw,x12.zw)),0.0);
m=m*m;
m=m*m;
vec3 x=2.0*fract(p*C.www)- 1.0;
vec3 h=abs(x)- 0.5;
vec3 ox=floor(x+0.5);
vec3 a0=x-ox;
m*=1.79284291400159-0.85373472095314*(a0*a0+h*h);
vec3 g;
g.x=a0.x*x0.x+h.x*x0.y;
g.yz=a0.yz*x12.xz+h.yz*x12.yw;
return 130.0*dot(m,g);
}
vec2 rotate(vec2 uv,float th){
return mat2(cos(th),sin(th),-sin(th),cos(th))*uv;
}
float randomR(vec2 p){
vec2 uv=floor(p)/100.+.5;
return texture(u_noiseTexture,fract(uv)).r;
}
float valueNoiseR(vec2 st){
vec2 i=floor(st);
vec2 f=fract(st);
float a=randomR(i);
float b=randomR(i+vec2(1.0,0.0));
float c=randomR(i+vec2(0.0,1.0));
float d=randomR(i+vec2(1.0,1.0));
vec2 u=f*f*(3.0-2.0*f);
float x1=mix(a,b,u.x);
float x2=mix(c,d,u.x);
return mix(x1,x2,u.y);
}
vec4 fbmR(vec2 n0,vec2 n1,vec2 n2,vec2 n3){
float amplitude=0.2;
vec4 total=vec4(0.);
for(int i=0;i<3;i++){
n0=rotate(n0,0.3);
n1=rotate(n1,0.3);
n2=rotate(n2,0.3);
n3=rotate(n3,0.3);
total.x +=valueNoiseR(n0)*amplitude;
total.y +=valueNoiseR(n1)*amplitude;
total.z +=valueNoiseR(n2)*amplitude;
total.z +=valueNoiseR(n3)*amplitude;
n0*=1.99;
n1*=1.99;
n2*=1.99;
n3*=1.99;
amplitude*=0.6;
}
return total;
}`,PAPER_GRAIN_SHAPE_GLSL=`float baseNoise=snoise(grain_uv*.5);
vec4 fbmVals=fbmR(
.002*grain_uv+10.,
.003*grain_uv,
.001*grain_uv,
rotate(.4*grain_uv,2.)
);
float grainDist=baseNoise*snoise(grain_uv*.2)- fbmVals.x-fbmVals.y;
float rawNoise=.75*baseNoise-fbmVals.w-fbmVals.z;
float noise=clamp(rawNoise,0.,1.);
shape +=u_intensity*2./u_colorsCount*(grainDist+.5);
shape +=u_noise*10./u_colorsCount*noise;
float aa=fwidth(shape);
shape=clamp(shape-.5/u_colorsCount,0.,1.);
float totalShape=smoothstep(0.,u_softness+2.*aa,clamp(shape*u_colorsCount,0.,1.));
float mixer=shape*(u_colorsCount-1.);`,PAPER_LENS_FRAGMENT_GLSL=`#version 300 es
precision mediump float;
uniform sampler2D u_image;
uniform float u_imageAspectRatio;
uniform float u_spread;
uniform float u_bias;
uniform float u_angle;
uniform float u_perspective;
uniform float u_count;
uniform float u_dispersion;
uniform float u_dispersionShift;
uniform float u_dispersionColor;
uniform float u_focusCenter;
uniform float u_focusEdges;
uniform float u_swirl;
uniform float u_noise;
uniform float u_noiseFrequency;
uniform float u_noiseOffset;
uniform float u_lensBulge;
uniform float u_lensCircle;
uniform float u_grainMixer;
uniform float u_grainOverlay;
uniform float u_imageX;
uniform float u_imageY;
in vec2 v_imageUV;
out vec4 fragColor;
#define TWO_PI 6.28318530718
#define PI 3.14159265358979323846
float hash21(vec2 p){
p=fract(p*vec2(0.3183099,0.3678794))+ 0.1;
p +=dot(p,p+19.19);
return fract(p.x*p.y);
}
vec2 rotate(vec2 uv,float th){
return mat2(cos(th),sin(th),-sin(th),cos(th))*uv;
}
float valueNoise(vec2 st){
vec2 i=floor(st);
vec2 f=fract(st);
float a=hash21(i);
float b=hash21(i+vec2(1.,0.));
float c=hash21(i+vec2(0.,1.));
float d=hash21(i+vec2(1.,1.));
vec2 u=f*f*(3.-2.*f);
return mix(mix(a,b,u.x),mix(c,d,u.x),u.y);
}
float getUvFrame(vec2 uv){
vec2 invAA=1./clamp(fwidth(uv),1e-5,.02);
vec2 lo=clamp(uv*invAA+.5,0.,1.);
vec2 hi=clamp((1.-uv)*invAA+.5,0.,1.);
return lo.x*hi.x*lo.y*hi.y;
}
vec4 sampleOverWhite(vec2 uv){
vec4 img=texture(u_image,uv);
float cover=img.a*getUvFrame(uv);
vec3 colorOverWhite=mix(vec3(1.),img.rgb,cover);
return vec4(colorOverWhite,cover);
}
vec3 hueColor(float hue){
vec3 rgb=clamp(abs(mod(hue*6.+vec3(0.,4.,2.),6.)- 3.)- 1.,0.,1.);
rgb=rgb*rgb*(3.-2.*rgb);
return rgb;
}
float boxInradius(){
return .5*min(u_imageAspectRatio,1.);
}
float boxOutradius(){
return .5*length(vec2(u_imageAspectRatio,1.));
}
float spreadReach(){
return .7*pow(u_spread,1.3+2.7*u_spread);
}
float innerCircleMask(float radius,float inradius){
return 1.-smoothstep(.5*inradius,1.1*inradius,radius);
}
float dispersionCurve(float t,float biasPow){
float mirroredT=u_bias<0.?1.-t:t;
float curved=pow(mirroredT,biasPow);
return u_bias<0.?1.-curved:curved;
}
vec2 getSpread(vec2 fromCenter,float radius,vec2 warpedUV,float edgeAA,float inradius,float outradius,float reach,out float outStrength){
float angleRad=radians(u_angle);
vec2 uniformDir=vec2(cos(angleRad),sin(angleRad));
float invInradius=1./inradius;
vec2 radialDir=fromCenter*invInradius;
float maxLen=(outradius+reach)*invInradius;
float radialLen=radius*invInradius;
if(radialLen>maxLen)radialDir*=maxLen/radialLen;
vec2 spreadDir=mix(uniformDir,radialDir,u_perspective);
float bandProximity=smoothstep(inradius*.8,inradius,radius);
float lensCircleMaxing=bandProximity*u_lensCircle*u_lensCircle*u_lensCircle;
spreadDir=mix(spreadDir,radialDir,lensCircleMaxing);
vec2 warpedAbs=abs(warpedUV-.5);
float inner=mix(1.,smoothstep(0.,mix(inradius,outradius,u_focusCenter),radius),u_focusCenter);
float boxDist=max(warpedAbs.x,warpedAbs.y)*2.;
float outer=mix(1.,1.-min(boxDist,1.),u_focusEdges);
float strength=inner*outer;
strength*=mix(1.,mix(.15,.03,max(-u_lensBulge,0.)),lensCircleMaxing);
vec2 outside=max(warpedAbs-.5,0.);
float margin=max(reach*length(spreadDir)*(1.-u_lensCircle),edgeAA);
strength*=1.-smoothstep(0.,margin,length(outside));
outStrength=strength;
vec2 axis=spreadDir*(reach*strength);
if(u_noise>0.){
float turn=(valueNoise(fromCenter*u_noiseFrequency*18.+u_noiseOffset*30.)- .5)*2.*u_noise;
float cs=cos(turn),sn=sin(turn);
axis=mat2(cs,-sn,sn,cs)*axis;
}
axis.x/=u_imageAspectRatio;
return axis;
}
vec2 lensWarp(vec2 fromCenter,float radius,float inradius,out float bulgeFade){
bulgeFade=1.;
if(u_lensBulge==0.&&u_lensCircle<=0.)return fromCenter;
if(radius<1e-5)return fromCenter;
float r=radius;
if(u_lensBulge!=0.){
float rn=radius/inradius;
float bulge=abs(u_lensBulge)*(u_lensBulge>0.?1.4:1.2);
if(u_lensBulge>0.)bulgeFade=1.-smoothstep(1.45,1.53,rn*bulge);
float map=u_lensBulge>0.
?tan(min(rn*bulge,1.53))/tan(bulge)
:atan(rn*tan(bulge))/bulge;
float bulgeScale=map/rn;
fromCenter*=bulgeScale;
r*=bulgeScale;
}
if(u_lensCircle>0.){
vec2 dir=fromCenter/max(r,1e-5);
vec2 halfBox=vec2(u_imageAspectRatio,1.)*.5;
float rBox=min(halfBox.x/max(abs(dir.x),1e-4),halfBox.y/max(abs(dir.y),1e-4));
float band=inradius*mix(.03,.2,.5*(u_lensBulge+1.));
float innerEdge=inradius-band;
float over=smoothstep(0.,1.,(r-innerEdge)/band);
float g=r +(rBox-inradius)*pow(over,14.);
fromCenter=dir*mix(r,g,u_lensCircle);
}
return fromCenter;
}
void main(){
vec2 uv=v_imageUV;
float invAspect=1./u_imageAspectRatio;
float inradius=boxInradius();
float outradius=boxOutradius();
float reach=spreadReach();
vec2 fromCenter=uv-.5;
fromCenter.x*=u_imageAspectRatio;
float radius=length(fromCenter);
float bulgeFade;
vec2 warpedFromCenter=lensWarp(fromCenter,radius,inradius,bulgeFade);
vec2 baseUV=vec2(warpedFromCenter.x*invAspect,warpedFromCenter.y)+ .5;
vec2 baseDerivative=fwidth(baseUV);
float edgeAA=clamp(2.*max(baseDerivative.x,baseDerivative.y),.001,.02);
float spreadStrength;
vec2 spreadAxis=getSpread(fromCenter,radius,baseUV,edgeAA,inradius,outradius,reach,spreadStrength);
vec2 grainUV=vec2(0.);
if(u_grainMixer>0.||u_grainOverlay>0.){
vec2 dudx=dFdx(v_imageUV);
vec2 dudy=dFdy(v_imageUV);
grainUV=(v_imageUV-.5)*(.8/vec2(length(dudx),length(dudy)))+ .5;
}
if(u_grainMixer>0.){
float grainSeed=valueNoise(grainUV);
vec2 jit=fract(grainSeed*vec2(157.31,113.57))*2.-1.;
spreadAxis +=jit*.3*u_grainMixer*length(spreadAxis);
}
float count=floor(u_count);
int countInteger=int(count);
float invCount=1./count;
float invSpan=1./max(count-1.,1.);
float hueBase=u_dispersionColor+.5;
float biasPower=1.+2.*abs(u_bias);
vec2 tapBias=.5-vec2(u_imageX,u_imageY);
float centerAmount=clamp(1.-u_dispersionShift,0.,1.);
float edgesAmount=clamp(1.+u_dispersionShift,0.,1.);
float dispersion=u_dispersion*mix(edgesAmount,centerAmount,innerCircleMask(radius,inradius));
float dispersionPower=pow(dispersion,.8);
float warpedRadius=length(warpedFromCenter);
float swirlAngle=u_swirl*.4*PI*spreadStrength*u_spread*min(1.,inradius/max(warpedRadius,1e-4));
vec3 colorSum=vec3(0.);
vec3 weightSum=vec3(0.);
float coverSum=0.;
for(int i=0;i<50;i++){
if(i>=countInteger)break;
float layer=float(i);
float hue=hueBase+layer*invCount;
float spread=layer*invSpan;
if(u_bias!=0.)spread=dispersionCurve(spread,biasPower);
float fanPos=1.-2.*spread;
vec2 tapUV=baseUV+spreadAxis*fanPos-.5;
if(u_swirl!=0.){
tapUV.x*=u_imageAspectRatio;
tapUV=rotate(tapUV,swirlAngle*fanPos);
tapUV.x*=invAspect;
}
vec4 tap=sampleOverWhite(tapUV+tapBias);
vec3 weight=1.-dispersionPower*hueColor(hue);
colorSum +=tap.rgb*weight;
weightSum +=weight;
coverSum +=tap.a;
}
vec3 color=colorSum/max(weightSum,1e-4);
float coverAvg=coverSum*invCount;
float ground=min(color.r,min(color.g,color.b));
float alpha=max(coverAvg,1.-ground);
vec3 premult=max(color -(1.-alpha),0.);
fragColor=vec4(premult,alpha)*bulgeFade;
if(u_grainOverlay>0.){
float grain=valueNoise(rotate(grainUV,1.)+ vec2(3.));
grain=mix(grain,valueNoise(rotate(grainUV,2.)+ vec2(-1.)),.5);
grain=pow(grain,1.3);
float grainV=grain*2.-1.;
float grainStrength=pow(u_grainOverlay*abs(grainV),.8)*fragColor.a;
fragColor.rgb=mix(fragColor.rgb,vec3(step(0.,grainV))*fragColor.a,.35*grainStrength);
}
}`,PAPER_NOISE_TEXTURE_R8_BASE64="",CF_MOTION_MAX_MASKS=12,CF_MOTION_MAX_PATH=48,CF_MOTION_QUAD_VERTEX=`#version 300 es
precision highp float;
in vec2 aPosition;
out vec2 vUv;
void main(){
vUv=aPosition*0.5+0.5;
gl_Position=vec4(aPosition,0.0,1.0);
}`,CF_MOTION_FRAME_GLSL=`uniform int uFrameShape;
uniform float uFrameRadius;
uniform vec2 uFrameOffset;
uniform vec2 uFrameSize;
float cfFrameCoverage(vec2 fragCoord){
if(uFrameShape==0)return 1.0;
vec2 halfSize=uFrameSize*0.5;
vec2 p=fragCoord+uFrameOffset-halfSize;
float shortEdge=min(uFrameSize.x,uFrameSize.y);
if(uFrameShape==2)return clamp(shortEdge*0.5-length(p)+ 0.5,0.0,1.0);
float r=min(shortEdge*0.5,shortEdge*uFrameRadius);
vec2 q=abs(p)- halfSize+r;
float d=length(max(q,0.0))+ min(max(q.x,q.y),0.0)- r;
return clamp(0.5-d,0.0,1.0);
}`,CF_MOTION_REMOVE_BACKDROP_GLSL=`vec4 dispersionRemoveBackdrop(vec4 composite,vec3 background){
vec3 color=clamp(composite.rgb+background*(1.0-composite.a),0.0,1.0);
vec3 delta=color-background;
vec3 coverage=max(
delta/max(vec3(1.0)- background,vec3(1e-5)),
-delta/max(background,vec3(1e-5))
);
float alpha=clamp(max(coverage.r,max(coverage.g,coverage.b)),0.0,1.0);
return vec4(clamp(color-background*(1.0-alpha),0.0,alpha),alpha);
}`,CF_MOTION_PAPER_GRAIN_GLSL=`vec3 paperGrainShape(
vec2 grain_uv,
float u_intensity,
float u_noise,
float u_softness
){
float u_colorsCount=4.0;
float shape=0.0;
${PAPER_GRAIN_SHAPE_GLSL}
return vec3(totalShape,mixer,aa);
}
vec3 paperSpectrumStop(float angle){
vec3 color=0.5*(
uPaletteBase +
uPaletteAmp*cos(angle+uPalettePhase)+
uPalette2Amp*cos(2.0*angle)
);
color=clamp(color,0.0,1.0);
float luminance=dot(color,vec3(0.2125,0.7154,0.0721));
return mix(vec3(luminance),color,uChroma);
}
float paperGradientTransition(float mixer,float stopIndex,float softness,float aa){
float localT=clamp(mixer-stopIndex,0.0,1.0);
return smoothstep(0.5-0.5*softness-aa,0.5+0.5*softness+aa,localT);
}
vec3 paperSpectrumGradient(float mixer,float softness,float aa){
const float quarterTurn=1.5707963267948966;
float baseAngle=uPaletteShift+uPointerColor*cfPointerFalloff+2.0*quarterTurn;
vec3 gradient=paperSpectrumStop(baseAngle);
gradient=mix(gradient,paperSpectrumStop(baseAngle+quarterTurn),paperGradientTransition(mixer,0.0,softness,aa));
gradient=mix(gradient,paperSpectrumStop(baseAngle+2.0*quarterTurn),paperGradientTransition(mixer,1.0,softness,aa));
gradient=mix(gradient,paperSpectrumStop(baseAngle+3.0*quarterTurn),paperGradientTransition(mixer,2.0,softness,aa));
return gradient;
}
vec4 paperGrainSample(vec2 grainUv,float intensity,float amount,float softness){
vec3 paperShape=paperGrainShape(grainUv,intensity,amount,softness);
vec3 grainColor=paperSpectrumGradient(paperShape.y,softness,paperShape.z);
return vec4(grainColor,paperShape.x);
}`,CF_MOTION_MASK_GLSL=`const int DISPERSION_MAX_MASKS=12;
uniform int uMaskCount;
uniform vec2 uMaskCenter[12];
uniform vec4 uMaskAxes[12];
uniform float uMaskBlur[12];
uniform float uMaskEnabled;
uniform float uMaskPreview;
float dispersionMaskCoverage(vec2 fragCoord){
if(uMaskCount<=0)return 1.0;
vec2 screenPoint=fragCoord/max(iResolution.xy,vec2(1.0));
float shortEdge=max(min(iResolution.x,iResolution.y),1.0);
vec2 shortEdgeScale=iResolution.xy/shortEdge;
float remaining=1.0;
for(int index=0;index<DISPERSION_MAX_MASKS;index++){
if(index>=uMaskCount)break;
vec2 delta=(screenPoint-uMaskCenter[index])*shortEdgeScale;
vec4 axes=uMaskAxes[index];
vec2 localPoint=vec2(
delta.x*axes.x+delta.y*axes.y,
-delta.x*axes.y+delta.y*axes.x
);
float distanceToCenter=length(localPoint*axes.zw);
float feather=clamp(uMaskBlur[index],0.0,1.0);
float coverage=feather<=0.00001
?1.0-step(1.000001,distanceToCenter)
:1.0-smoothstep(1.0-feather,1.0+feather,distanceToCenter);
remaining*=1.0-coverage;
}
return 1.0-remaining;
}`,CF_MOTION_FORM_GLSL=`uniform float uForm;
uniform float uFormSize;
uniform float uFormRound;
uniform float uFormAngle;
uniform vec4 uPath[${48/2}];
uniform float uPathCount;
vec2 cfPathPoint(int i){
vec4 q=uPath[i/2];
return((i % 2==0?q.xy:q.zw)*2.0-1.0)*vec2(1.0,-1.0)*iResolution.xy/min(iResolution.x,iResolution.y);
}
vec2 cfPathUv(vec2 p){
vec2 a=cfPathPoint(0);
float m=1e9,run=0.0,wsum=0.0,asum=0.0,xsum=0.0;
for(int i=1;i<48;i++){
if(float(i)>=uPathCount)break;
vec2 b=cfPathPoint(i),ba=b-a,pa=p-a;
float len=max(length(ba),1e-5);
float h=clamp(dot(pa,ba)/(len*len),0.0,1.0);
float d=length(pa-ba*h);
if(d<m){float k=exp((d-m)*8.0);wsum*=k;asum*=k;xsum*=k;m=d;}
float w=exp((m-d)*8.0);
wsum +=w;
asum +=w*(run+h*len);
xsum +=w*(ba.x*pa.y-ba.y*pa.x)/len;
run +=len;
a=b;
}
float along=asum/wsum-0.5*run;
return vec2(sqrt(along*along+0.36),xsum/wsum);
}
float cfTriangle(vec2 p,float r){
const float k=1.7320508;
p.x=abs(p.x)- r;
p.y=p.y+r/k;
if(p.x+k*p.y>0.0)p=vec2(p.x-k*p.y,-k*p.x-p.y)/2.0;
p.x -=clamp(p.x,-2.0*r,0.0);
return -length(p)*sign(p.y);
}
float cfBox(vec2 p,vec2 b){
vec2 q=abs(p)- b;
return length(max(q,0.0))+ min(max(q.x,q.y),0.0);
}
float cfFormDistance(vec2 p,float R,float rr){
if(uForm<1.5)return length(p)- R;
if(uForm<2.5){float inr=0.606*R;float k=max(1.0-rr/inr,0.05);return cfTriangle(p/k,1.05*R)*k-rr;}
float inr=0.86*R;
float k=max(1.0-rr/inr,0.05);
return cfBox(p/k,vec2(inr))*k-rr;
}
vec2 cfFormUv(vec2 p){
if(uForm>3.5)return cfPathUv(p);
float c=cos(uFormAngle);
float s=sin(uFormAngle);
p=vec2(c*p.x-s*p.y,s*p.x+c*p.y);
float R=uFormSize;
float rr=uFormRound*R*0.45;
if(uForm>1.5&&uForm<2.5)p.y +=0.3*R;
float a=atan(abs(p.x),-p.y);
float along=0.6*R*(a-1.5707963-0.5*sin(2.0*a));
return vec2(along,cfFormDistance(p,R,rr));
}`,CF_MOTION_SHEET_GLSL=`float dispersionCurveProfile(float u){
if(uCurveForm<0.5)return 0.0;
if(uCurveForm<1.5)return u*u;
if(uCurveForm<2.5)return -(u*u);
if(uCurveForm<3.5)return u*(u*u-1.1);
if(uCurveForm<4.5)return sin(u*1.7);
float c=exp(1.15*u);
return 0.5*(c+1.0/c)- 1.0;
}
float dispersionCurveSlope(float u){
if(uCurveForm<0.5)return 0.0;
if(uCurveForm<1.5)return 2.0*u;
if(uCurveForm<2.5)return -2.0*u;
if(uCurveForm<3.5)return 3.0*u*u-1.1;
if(uCurveForm<4.5)return 1.7*cos(u*1.7);
float c=exp(1.15*u);
return 0.575*(c-1.0/c);
}
struct DispersionSheetResult{
vec3 color;
float sheetW;
vec3 sheetAcc;
};
DispersionSheetResult marchDispersionSheet(vec2 viewUv,float timeElapsed){
vec3 rayDir=normalize(vec3(viewUv,1.0));
float paletteShift=uPaletteShift+uPointerColor*cfPointerFalloff;
vec3 accumulatedColor=vec3(0.0);
float totalDistance=0.0;
float sheetW=0.0;
vec3 sheetAcc=vec3(0.0);
for(int marchIndex=0;marchIndex<38;marchIndex++){
vec3 currentPos=rayDir*totalDistance;
currentPos.z -=2.0;
float wTime=timeElapsed*uWaveSpeed;
float waveHeight=(
sin(currentPos.x*uWave1Freq+wTime)*uWave1Amp +
sin(currentPos.z*uWave2Freq-wTime*0.6)*uWave2Amp +
sin(currentPos.x*uWave3FreqX-currentPos.z*uWave3FreqZ+wTime*1.6)*uWave3Amp
)*uWaveHeight;
waveHeight +=uCurveAmp*7.0*dispersionCurveProfile(currentPos.x/7.0);
float distToWave=abs(currentPos.y-waveHeight);
float distToWaveR=abs(currentPos.y -(waveHeight-uChannelSplit));
float distToWaveB=abs(currentPos.y -(waveHeight+uChannelSplit));
if(totalDistance>0.7){
float sheetBlend=0.15/(distToWave*distToWave+0.01);
float causticW=sheetBlend*sheetBlend;
sheetW +=causticW;
sheetAcc +=vec3(currentPos.x,currentPos.z,waveHeight)*causticW;
}
currentPos/=uFoldingOffset;
float stepSize=min(distToWave-0.080,0.0)+ uStepBase;
totalDistance +=stepSize;
if(totalDistance>=36.5)break;
float patternX=sin(currentPos.x+cos(currentPos.y)*cos(currentPos.z));
float patternY=sin(currentPos.z+sin(currentPos.y)*cos(currentPos.x+timeElapsed));
float basePattern=smoothstep(0.5-0.34*uPatternSoft,0.7+0.22*uPatternSoft,patternX*patternY);
basePattern=mix(basePattern,0.78,uPatternSoft*0.7);
float blendFactor=0.15/(distToWave*distToWave+0.01);
float mixedPattern=mix(basePattern,1.0,blendFactor);
vec3 channelMix=vec3(
mix(basePattern,1.0,0.15/(distToWaveR*distToWaveR+0.01)),
mixedPattern,
mix(basePattern,1.0,0.15/(distToWaveB*distToWaveB+0.01))
);
float glowIntensity=uGlowIntensity/(uGlowSpread+stepSize);
float distanceFade=smoothstep(36.5,7.3,totalDistance);
vec3 paletteColor=uPaletteBase+uPaletteAmp*cos(totalDistance*uPaletteFreq+uPalettePhase+paletteShift);
paletteColor +=uPalette2Amp*cos(2.0*(totalDistance*uPaletteFreq+paletteShift));
paletteColor=max(paletteColor,vec3(0.0));
paletteColor=mix(vec3(dot(paletteColor,vec3(1.0/3.0))),paletteColor,uChroma);
float shadeMix=uShading*clamp(blendFactor,0.0,1.0);
if(shadeMix>0.0){
vec3 wavePos=currentPos*uFoldingOffset;
float dWdx=(
cos(wavePos.x*uWave1Freq+wTime)*uWave1Freq*uWave1Amp +
cos(wavePos.x*uWave3FreqX-wavePos.z*uWave3FreqZ+wTime*1.6)*uWave3FreqX*uWave3Amp
)*uWaveHeight+uCurveAmp*dispersionCurveSlope(wavePos.x/7.0);
float dWdz=(
cos(wavePos.z*uWave2Freq-wTime*0.6)*uWave2Freq*uWave2Amp -
cos(wavePos.x*uWave3FreqX-wavePos.z*uWave3FreqZ+wTime*1.6)*uWave3FreqZ*uWave3Amp
)*uWaveHeight;
vec3 sheetNormal=normalize(vec3(-dWdx,1.0,-dWdz));
float lit=0.5+0.5*dot(sheetNormal,normalize(vec3(-0.4,0.85,-0.3)));
float shade=mix(1.0,0.28+1.25*lit,shadeMix);
paletteColor*=shade;
}
accumulatedColor +=glowIntensity*channelMix*distanceFade*paletteColor;
}
DispersionSheetResult result;
result.color=accumulatedColor;
result.sheetW=sheetW;
result.sheetAcc=sheetAcc;
return result;
}`,CF_MOTION_COMPOSE_GLSL=`float generateFineNoise(vec2 p){
vec3 p3=fract(vec3(p.xyx)*8.6231);
p3 +=dot(p3,p3.yzx+67.92);
return fract((p3.x+p3.y)*p3.z);
}
float dispersionEffectRegionMask(float area,vec3 lightColor){
float peak=max(lightColor.r,max(lightColor.g,lightColor.b));
float trough=min(lightColor.r,min(lightColor.g,lightColor.b));
float chroma=max(peak-trough,0.0);
float wholeWave=smoothstep(0.015,0.12,peak);
float core=smoothstep(0.14,0.65,peak);
float glow=smoothstep(0.045,0.20,peak)*(1.0-smoothstep(0.50,0.90,peak));
float bands=wholeWave*smoothstep(0.035,0.24,chroma);
float veil=smoothstep(0.008,0.075,peak)*(1.0-smoothstep(0.18,0.48,peak));
if(area<0.5)return wholeWave;
if(area<1.5)return core;
if(area<2.5)return glow;
if(area<3.5)return bands;
return veil;
}
vec4 composeDispersionSheet(
DispersionSheetResult sheet,
vec2 fragCoord,
float effectTime,
float grainDistribution,
float edgeFade
){
vec3 light=sheet.color*(1.0+uPointerGlow*cfPointerFalloff);
float areaMask=dispersionEffectRegionMask(uEffectArea,light);
float grainAmount=min(1.0,uGrainAmount+uPointerGrain*cfPointerFalloff);
if(grainAmount>0.0&&areaMask>0.0){
float driftPhase=effectTime*0.1;
vec2 drift=vec2(cos(driftPhase),sin(driftPhase))*(96.0*uGrainDrift);
float grainReferenceScale=720.0/max(min(iResolution.x,iResolution.y),1.0);
vec2 normalizedGrainCoord=fragCoord*grainReferenceScale;
vec2 grain_uv=normalizedGrainCoord*uGrainScale+drift;
float projectedSurfaceHit=1.0-exp(-0.035*max(sheet.sheetW,0.0));
float surfaceDistribution=pow(clamp(projectedSurfaceHit,0.0,1.0),0.75);
float distributionMask=mix(1.0,surfaceDistribution,grainDistribution);
float mappedGrainAmount=grainAmount;
float amountGate=smoothstep(0.0,0.05,mappedGrainAmount);
vec4 primaryGrain=paperGrainSample(grain_uv,uGrainDistortion*amountGate,mappedGrainAmount,uGrainSoftness);
vec4 secondaryGrain=paperGrainSample(grain_uv+vec2(173.17,-91.73),uGrainDistortion*amountGate,mappedGrainAmount,uGrainSoftness);
vec4 tertiaryGrain=paperGrainSample(grain_uv+vec2(-247.61,149.29),uGrainDistortion*amountGate,mappedGrainAmount,uGrainSoftness);
float secondaryDensity=smoothstep(0.48,0.82,mappedGrainAmount);
float tertiaryDensity=smoothstep(0.72,1.0,mappedGrainAmount);
float grainMask=areaMask*distributionMask;
float primaryOpacity=clamp(primaryGrain.a*grainMask*1.55,0.0,1.0);
float secondaryOpacity=clamp(secondaryGrain.a*grainMask*secondaryDensity*1.35,0.0,1.0);
float tertiaryOpacity=clamp(tertiaryGrain.a*grainMask*tertiaryDensity*1.20,0.0,1.0);
float grainOpacity=1.0 -(1.0-primaryOpacity)*(1.0-secondaryOpacity)*(1.0-tertiaryOpacity);
float grainWeight=primaryOpacity+secondaryOpacity+tertiaryOpacity;
vec3 grainColor=(
primaryGrain.rgb*primaryOpacity +
secondaryGrain.rgb*secondaryOpacity +
tertiaryGrain.rgb*tertiaryOpacity
)/max(grainWeight,1e-5);
vec3 paperOver=mix(light,grainColor,grainOpacity);
vec3 grainEmission=grainColor*grainOpacity;
vec3 luminousSurface=light +(vec3(1.0)- clamp(light,0.0,1.0))*grainEmission;
light=mix(paperOver,luminousSurface,grainDistribution);
}
float maskCoverage=dispersionMaskCoverage(fragCoord);
float appliedMaskCoverage=mix(1.0,maskCoverage,uMaskEnabled);
if(uMaskPreview<0.5){
light*=appliedMaskCoverage;
}
float luminance=dot(light,vec3(0.2125,0.7154,0.0721));
light=mix(vec3(luminance),light,uSaturationX);
vec3 balanceStops=vec3(
0.45*uColorBalance.x+0.25*uColorBalance.y,
-0.25*uColorBalance.x+0.25*uColorBalance.y,
-0.25*uColorBalance.x-0.45*uColorBalance.y
);
light*=exp2(balanceStops);
if(edgeFade>0.0){
vec2 screen=fragCoord/max(iResolution.xy,vec2(1.0));
vec2 edge=min(screen,1.0-screen);
float fade=smoothstep(0.0,edgeFade,edge.x)*smoothstep(0.0,edgeFade,edge.y);
light*=fade;
}
float dither=(generateFineNoise(fragCoord+vec2(12.34,56.78))- 0.5)/128.0;
light +=vec3(dither*(uMaskPreview<0.5?appliedMaskCoverage:1.0));
float lum=clamp(max(light.r,max(light.g,light.b)),0.0,1.0);
float keyed=mix(lum,lum*lum*(3.0-2.0*lum),0.5);
float alpha=mix(keyed,1.0,uOpaque);
float bgLum=dot(uBg,vec3(0.2125,0.7154,0.0721));
float sourceGain=mix(keyed,1.0,bgLum*uOpaque);
vec3 rgb=light*sourceGain+uBg*(1.0-keyed)*uOpaque;
if(uMaskPreview>=0.5&&uMaskCount>0){
float maskOverlayAlpha=0.35*maskCoverage;
rgb=mix(rgb,vec3(0.92,0.12,0.12),maskOverlayAlpha);
alpha=max(alpha,maskOverlayAlpha);
}
vec4 composite=vec4(rgb,alpha);
return uRemoveBackdrop>0.5?dispersionRemoveBackdrop(composite,uBg):composite;
}`,CF_MOTION_FIELD_FRAGMENT=`#version 300 es
precision highp float;
precision highp int;
uniform vec3 iResolution;
uniform vec2 uViewOffset;
uniform float uZoom;
uniform float iTime;
uniform float uTimeScale;
uniform float uWaveSpeed;
uniform float uWaveHeight;
uniform float uWave1Freq;
uniform float uWave1Amp;
uniform float uWave2Freq;
uniform float uWave2Amp;
uniform float uWave3FreqX;
uniform float uWave3FreqZ;
uniform float uWave3Amp;
uniform float uFoldingOffset;
uniform float uStepBase;
uniform float uGlowIntensity;
uniform float uGlowSpread;
uniform float uOpaque;
uniform float uRemoveBackdrop;
uniform vec3 uBg;
uniform float uYShift;
uniform float uTiltX;
uniform float uCurveForm;
uniform float uCurveAmp;
uniform float uShading;
uniform vec3 uPalettePhase;
uniform vec3 uPaletteBase;
uniform vec3 uPaletteAmp;
uniform vec3 uPalette2Amp;
uniform float uPaletteFreq;
uniform float uPaletteShift;
uniform float uChroma;
uniform float uSaturationX;
uniform vec2 uColorBalance;
uniform float uEdgeFade;
uniform float uPatternSoft;
uniform float uChannelSplit;
uniform float uEffectArea;
uniform float uGrainAmount;
uniform float uGrainDistribution;
uniform float uGrainScale;
uniform float uGrainSoftness;
uniform float uGrainDistortion;
uniform float uGrainDrift;
uniform sampler2D u_noiseTexture;
uniform float uPremultiply;
uniform vec3 uPointer;
uniform float uPointerReach;
uniform float uPointerPull;
uniform float uPointerColor;
uniform float uPointerGrain;
uniform float uPointerGlow;
float cfPointerFalloff=0.0;
out vec4 cfFragColor;
${CF_MOTION_REMOVE_BACKDROP_GLSL}
${CF_MOTION_MASK_GLSL}
${CF_MOTION_FRAME_GLSL}
${PAPER_GRAIN_NOISE_GLSL}
${CF_MOTION_PAPER_GRAIN_GLSL}
${CF_MOTION_FORM_GLSL}
${CF_MOTION_SHEET_GLSL}
${CF_MOTION_COMPOSE_GLSL}
void main(){
vec2 fragCoord=gl_FragCoord.xy+uViewOffset;
vec2 sheetCoord=fragCoord;
if(uPointer.z>0.001){
vec2 toPointer=(fragCoord-uPointer.xy)/min(iResolution.x,iResolution.y);
cfPointerFalloff=uPointer.z*exp(-dot(toPointer,toPointer)/(uPointerReach*uPointerReach));
sheetCoord=uPointer.xy +(fragCoord-uPointer.xy)*(1.0+uPointerPull*cfPointerFalloff);
}
vec2 uv;
if(uForm>0.5){
uv=cfFormUv((2.0*sheetCoord-iResolution.xy)/min(iResolution.x,iResolution.y))*uZoom;
}else{
uv=(2.0*sheetCoord-iResolution.xy)/iResolution.y*uZoom;
uv.y +=uYShift;
uv.y +=uTiltX*uv.x;
}
DispersionSheetResult sheet=marchDispersionSheet(uv,iTime*uTimeScale);
vec4 color=composeDispersionSheet(sheet,fragCoord,iTime,uGrainDistribution,uEdgeFade);
color=clamp(color,0.0,1.0);
if(uPremultiply>0.5)color.rgb*=color.a;
cfFragColor=color*cfFrameCoverage(gl_FragCoord.xy);
}`;function cfLensFragment(){return`#version 300 es
${PAPER_LENS_FRAGMENT_GLSL.replace(/^#version 300 es\s*/u,"").replace("uniform sampler2D u_image;",`uniform sampler2D u_image;
uniform highp vec4 uViewWindow;`).replace("texture(u_image,uv)","texture(u_image,(uv-uViewWindow.xy)/uViewWindow.zw)").replace("img.a*getUvFrame(uv)","img.a").replace("float rn=radius/inradius;","float rn=radius/(u_lensBulge>0.?boxOutradius():inradius);").replace("fromCenter=dir*mix(r,g,u_lensCircle);","fromCenter=dir*mix(r,g,u_lensCircle*(1.-smoothstep(inradius,inradius*1.015,r)));").replace("void main(){","void paperLensMain(){")}
precision highp float;
uniform float uRemoveBackdrop;
uniform vec3 uBg;
${CF_MOTION_REMOVE_BACKDROP_GLSL}
${CF_MOTION_FRAME_GLSL}
void main(){
paperLensMain();
if(uRemoveBackdrop>0.5){
fragColor=dispersionRemoveBackdrop(fragColor,uBg);
}
fragColor*=cfFrameCoverage(gl_FragCoord.xy);
}`}const CF_MOTION_LENS_FRAGMENT=cfLensFragment(),CF_MOTION_LENS_VERTEX=`#version 300 es
precision highp float;
in vec2 aPosition;
uniform highp vec4 uViewWindow;
out vec2 v_imageUV;
void main(){
vec2 uv=aPosition*0.5+0.5;
v_imageUV=uv*uViewWindow.zw+uViewWindow.xy;
gl_Position=vec4(aPosition,0.0,1.0);
}`,CF_MOTION_COPY_FRAGMENT=`#version 300 es
precision highp float;
in vec2 vUv;
uniform sampler2D uTexture;
out vec4 cfFragColor;
${CF_MOTION_FRAME_GLSL}
void main(){
cfFragColor=texture(uTexture,vUv)*cfFrameCoverage(gl_FragCoord.xy);
}`;function cfRequire(e,o){if(e===null)throw new Error(`Wayglim Motion could not create ${o}.`);return e}function cfCompileShader(e,o,t){const a=cfRequire(e.createShader(o),"a shader");if(e.shaderSource(a,t),e.compileShader(a),!e.getShaderParameter(a,e.COMPILE_STATUS)&&!e.isContextLost()){const n=e.getShaderInfoLog(a)??"unknown error";throw e.deleteShader(a),new Error(`Wayglim Motion shader failed to compile: ${n}`)}return a}function cfCreateProgram(e,o,t){const a=cfCompileShader(e,e.VERTEX_SHADER,o),n=cfCompileShader(e,e.FRAGMENT_SHADER,t),r=cfRequire(e.createProgram(),"a shader program");if(e.attachShader(r,a),e.attachShader(r,n),e.bindAttribLocation(r,0,"aPosition"),e.linkProgram(r),e.deleteShader(a),e.deleteShader(n),!e.getProgramParameter(r,e.LINK_STATUS)&&!e.isContextLost()){const i=e.getProgramInfoLog(r)??"unknown error";throw e.deleteProgram(r),new Error(`Wayglim Motion program failed to link: ${i}`)}return{program:r,uniforms:new Map}}function cfUniform(e,o,t){return o.uniforms.has(t)||o.uniforms.set(t,e.getUniformLocation(o.program,t)),o.uniforms.get(t)??null}function cfCreateQuad(e){const o=cfRequire(e.createBuffer(),"a quad buffer");return e.bindBuffer(e.ARRAY_BUFFER,o),e.bufferData(e.ARRAY_BUFFER,new Float32Array([-1,-1,3,-1,-1,3]),e.STATIC_DRAW),o}function cfDrawQuad(e,o){e.bindBuffer(e.ARRAY_BUFFER,o),e.enableVertexAttribArray(0),e.vertexAttribPointer(0,2,e.FLOAT,!1,0,0),e.drawArrays(e.TRIANGLES,0,3)}function cfMaxSize(e){const o=e.getParameter(e.MAX_VIEWPORT_DIMS),t=Number(e.getParameter(e.MAX_RENDERBUFFER_SIZE))||4096,a=o?Math.min(o[0]??t,o[1]??t):t;return Math.max(1,Math.min(t,a))}function cfCreateTarget(e,o,t){const a=cfRequire(e.createTexture(),"a render texture");e.bindTexture(e.TEXTURE_2D,a),e.texStorage2D(e.TEXTURE_2D,1,e.RGBA8,o,t),e.texParameteri(e.TEXTURE_2D,e.TEXTURE_MIN_FILTER,e.LINEAR),e.texParameteri(e.TEXTURE_2D,e.TEXTURE_MAG_FILTER,e.LINEAR),e.texParameteri(e.TEXTURE_2D,e.TEXTURE_WRAP_S,e.MIRRORED_REPEAT),e.texParameteri(e.TEXTURE_2D,e.TEXTURE_WRAP_T,e.MIRRORED_REPEAT);const n=cfRequire(e.createFramebuffer(),"a framebuffer");return e.bindFramebuffer(e.FRAMEBUFFER,n),e.framebufferTexture2D(e.FRAMEBUFFER,e.COLOR_ATTACHMENT0,e.TEXTURE_2D,a,0),e.bindFramebuffer(e.FRAMEBUFFER,null),{framebuffer:n,height:t,texture:a,width:o}}function cfDeleteTarget(e,o){e.deleteFramebuffer(o.framebuffer),e.deleteTexture(o.texture)}const CF_NOISE_SIZE=128;function cfDecodeNoise(e){const o=atob(e||""),t=CF_NOISE_SIZE,a=new Uint8Array(t*t).fill(128);if(o.length<t*t)return console.warn("Wayglim Prism Flow: the grain noise texture is missing."),a;for(let n=0;n<t;n+=1)for(let r=0;r<t;r+=1)a[n*t+r]=o.charCodeAt((t-1-n)*t+r);return a}function cfCreateNoiseTexture(e,o){const t=cfDecodeNoise(o),a=cfRequire(e.createTexture(),"the noise texture");e.bindTexture(e.TEXTURE_2D,a);const n=cfRequire(e.createBuffer(),"the noise upload buffer");return e.bindBuffer(e.PIXEL_UNPACK_BUFFER,n),e.bufferData(e.PIXEL_UNPACK_BUFFER,t,e.STATIC_DRAW),e.texStorage2D(e.TEXTURE_2D,1,e.R8,CF_NOISE_SIZE,CF_NOISE_SIZE),e.pixelStorei(e.UNPACK_ALIGNMENT,1),e.texSubImage2D(e.TEXTURE_2D,0,0,0,CF_NOISE_SIZE,CF_NOISE_SIZE,e.RED,e.UNSIGNED_BYTE,0),e.pixelStorei(e.UNPACK_ALIGNMENT,4),e.bindBuffer(e.PIXEL_UNPACK_BUFFER,null),e.deleteBuffer(n),e.texParameteri(e.TEXTURE_2D,e.TEXTURE_MIN_FILTER,e.NEAREST),e.texParameteri(e.TEXTURE_2D,e.TEXTURE_MAG_FILTER,e.NEAREST),e.texParameteri(e.TEXTURE_2D,e.TEXTURE_WRAP_S,e.REPEAT),e.texParameteri(e.TEXTURE_2D,e.TEXTURE_WRAP_T,e.REPEAT),a}const CF_LOOP_RADIANS=20*Math.PI,CF_DEFAULT_LOOP_SECONDS=60,cfModeFraming={central:{glowSpreadScale:1,tilt:0,wave1AmpScale:1,wave1FreqScale:1,waveHeightScale:1,yShift:0,zoom:1},diagonal:{glowSpreadScale:1,tilt:.34,wave1AmpScale:1,wave1FreqScale:1,waveHeightScale:1,yShift:0,zoom:1},edge:{glowSpreadScale:.9,tilt:0,wave1AmpScale:1,wave1FreqScale:1,waveHeightScale:.5,yShift:.62,zoom:.92},halo:{glowSpreadScale:.85,tilt:0,wave1AmpScale:1.2,wave1FreqScale:1.3,waveHeightScale:1.7,yShift:0,zoom:1.55},ripple:{glowSpreadScale:1,tilt:0,wave1AmpScale:1.6,wave1FreqScale:2.2,waveHeightScale:.85,yShift:0,zoom:1}},cfCurveIndex={arch:2,cradle:5,drape:4,line:0,scurve:3,valley:1},cfAreaIndex={all:0,bands:3,core:1,glow:2,veil:4},cfShapeIndex={circle:2,rect:0,rounded:1},cfFormIndex={flow:0,path:4,ring:1,square:3,triangle:2},cfSpectra={aurora:{amp:[.78,1.02,1.08],amp2:[0,0,0],base:[.78,1.02,1.08],chroma:1,phase:[2.1,1.05,0]},dusk:{amp:[.58,.34,.44],amp2:[0,0,0],base:[.72,.56,.56],chroma:.95,phase:[0,1,2.7]},ice:{amp:[.35,.55,.75],amp2:[0,0,0],base:[.78,.95,1.1],chroma:.9,phase:[2.4,2,1.5]},mono:{amp:[1,1,1],amp2:[0,0,0],base:[1,1,1],chroma:0,phase:[0,1,2]},porcelain:{amp:[.31,.24,.05],amp2:[0,0,0],base:[.93,.98,1.05],chroma:1,phase:[Math.PI/2,Math.PI/2,Math.PI*1.5]},prism:{amp:[1,1,1],amp2:[0,0,0],base:[1,1,1],chroma:1,phase:[0,1,2]},sunset:{amp:[1.08,.96,.82],amp2:[0,0,0],base:[1.08,.96,.82],chroma:1,phase:[0,1.05,2.35]}},CF_LENS_KEYS=["angle","bias","dispersion","dispersionColor","dispersionShift","focusCenter","focusEdges","grainMixer","grainOverlay","imageX","imageY","lensBulge","lensCircle","noise","noiseFrequency","noiseOffset","perspective","spread","swirl"];function cfClamp01(e){return Math.min(1,Math.max(0,e))}function cfCustomPalette(e){const o=[0,0,0],t=[0,0,0],a=[0,0,0],n=[0,0,0];for(let r=0;r<3;r+=1){const[i,u,f,s]=e.map(h=>(h[r]??0)*2),l=((i??0)-(f??0))/2,d=((u??0)-(s??0))/2;o[r]=Math.hypot(l,d),t[r]=((i??0)-(u??0)+(f??0)-(s??0))/4,a[r]=((i??0)+(u??0)+(f??0)+(s??0))/4,n[r]=Math.atan2(-d,l)}return{amp:o,amp2:t,base:a,chroma:1,phase:n}}const CF_PACE_COMPRESSION=15;function cfLoopCycles(e,o){const t=Math.min(900,Math.max(40,250*2**((60-Math.min(100,Math.max(0,e)))/25))),a=Number.isFinite(o)&&o>0?o:CF_DEFAULT_LOOP_SECONDS;return Math.min(200,Math.max(1,Math.round(a*CF_PACE_COMPRESSION/t)))}function cfLoopTime(e,o){const t=(o.loopProgress%1+1)%1,a=cfLoopCycles(e.speed,o.durationSeconds);return(t+cfClamp01(e.phase/100))*a%1*CF_LOOP_RADIANS}function cfSetFieldUniforms(e,o,t,a){const n=s=>cfUniform(e,o,s),r=cfModeFraming[t.mode]??cfModeFraming.central,i=t.spectrum==="custom"?cfCustomPalette(t.customColors):cfSpectra[t.spectrum]??cfSpectra.prism,u=cfClamp01(t.softness/100);e.uniform1f(n("iTime"),a),e.uniform1f(n("uTimeScale"),1),e.uniform1f(n("uWaveSpeed"),.5);const f=t.form&&t.form!=="flow"&&t.form!=="path"?.55:1;e.uniform1f(n("uWaveHeight"),t.wobble/21*r.waveHeightScale*f),e.uniform1f(n("uWave1Freq"),3*r.wave1FreqScale),e.uniform1f(n("uWave1Amp"),t.secondaryMotion/100*.10526*r.wave1AmpScale),e.uniform1f(n("uWave2Freq"),.4),e.uniform1f(n("uWave2Amp"),-(t.refraction/80)*.26667),e.uniform1f(n("uWave3FreqX"),-.7),e.uniform1f(n("uWave3FreqZ"),-.7),e.uniform1f(n("uWave3Amp"),t.refraction/80*.26667),e.uniform1f(n("uChannelSplit"),t.chromaSplit/100*.12),e.uniform1f(n("uFoldingOffset"),20),e.uniform1f(n("uStepBase"),.104+u*.014),e.uniform1f(n("uPatternSoft"),u),e.uniform1f(n("uGlowIntensity"),t.glow/72*.011*(1+.3*(u-.44))),e.uniform1f(n("uGlowSpread"),4*2**((36-t.height)/24)*r.glowSpreadScale),e.uniform1f(n("uZoom"),r.zoom),e.uniform1f(n("uYShift"),(50-t.position)/100*1.6+r.yShift),e.uniform1f(n("uTiltX"),(t.bend-50)/50*.45+r.tilt),e.uniform1f(n("uCurveForm"),cfCurveIndex[t.curve]??0),e.uniform1f(n("uCurveAmp"),t.form==="path"?0:cfClamp01(t.curveDepth/100)**1.2*2.4),e.uniform1f(n("uShading"),cfClamp01(t.shading/100)),e.uniform1f(n("uPaletteFreq"),t.spread/58*3),e.uniform3f(n("uPalettePhase"),i.phase[0],i.phase[1],i.phase[2]),e.uniform3f(n("uPaletteBase"),i.base[0],i.base[1],i.base[2]),e.uniform3f(n("uPaletteAmp"),i.amp[0],i.amp[1],i.amp[2]),e.uniform3f(n("uPalette2Amp"),i.amp2[0],i.amp2[1],i.amp2[2]),e.uniform1f(n("uChroma"),i.chroma),e.uniform1f(n("uPaletteShift"),(t.lightTravel-52)/100*Math.PI*2),e.uniform1f(n("uSaturationX"),.55+cfClamp01(t.saturation/88)*.45),e.uniform2f(n("uColorBalance"),t.balanceX,t.balanceY),e.uniform1f(n("uEdgeFade"),(t.inset/40)**1.6*.25)}function cfSetPointerUniforms(e,o,t,a,n){const r=s=>cfUniform(e,o,s),i=t.pointer,u=i&&i.enabled&&a&&a.presence>.001?a:null;if(e.uniform3f(r("uPointer"),u?u.x*n[0]:0,u?(1-u.y)*n[1]:0,u?cfClamp01(u.presence):0),!u)return;const f=Math.max(-100,Math.min(100,i.pull))/100;e.uniform1f(r("uPointerReach"),.04+cfClamp01(i.reach/100)*.56),e.uniform1f(r("uPointerPull"),f*(f>0?1.2:.75)),e.uniform1f(r("uPointerColor"),cfClamp01(i.color/100)*Math.PI),e.uniform1f(r("uPointerGrain"),t.grainEnabled===!1?0:cfClamp01(i.grain/100)*.6),e.uniform1f(r("uPointerGlow"),cfClamp01(i.glow/100)*1.4)}function cfFollowPointer(e,o,t,a){const n=Math.max(0,Math.min(.25,t)),r=1-Math.exp(-n/(.02+cfClamp01(a/100)**1.5*.5)),i=1-Math.exp(-n/.18);e.presence<.01&&o.presence>0&&(e.x=o.x,e.y=o.y);const u=o.x-e.x,f=o.y-e.y,s=o.presence-e.presence;return e.x+=u*r,e.y+=f*r,e.presence+=s*i,Math.abs(s)<.002&&(e.presence=o.presence),Math.abs(u)+Math.abs(f)>5e-4||Math.abs(s)>.002}function cfSetFormUniforms(e,o,t,a){const n=l=>cfUniform(e,o,l),r=(a%1+1)%1,i=Math.round(t.formSpin)*r;e.uniform1f(n("uForm"),cfFormIndex[t.form]??0),e.uniform1f(n("uFormSize"),.2+cfClamp01(t.formSize/100)*.9),e.uniform1f(n("uFormRound"),cfClamp01(t.formRound/100)),e.uniform1f(n("uFormAngle"),t.form==="path"?0:t.formRotate/180*Math.PI+i*Math.PI*2);const u=t.form==="path"&&t.path?t.path:[],f=Math.min(48,Math.floor(u.length/2)),s=new Float32Array(96);for(let l=0;l<f*2;l+=1)s[l]=Number(u[l])||0;e.uniform4fv(n("uPath"),s),e.uniform1f(n("uPathCount"),f)}function cfSetEffectUniforms(e,o,t){const a=n=>cfUniform(e,o,n);e.uniform1f(a("uEffectArea"),cfAreaIndex[t.effectArea]??0),e.uniform1f(a("uGrainAmount"),t.grainEnabled===!1?0:t.grainAmount/100),e.uniform1f(a("uGrainDistribution"),t.surfaceGrain?1:0),e.uniform1f(a("uGrainScale"),1.6*(.5+t.grainScale/100)),e.uniform1f(a("uGrainSoftness"),t.grainSoftness/100),e.uniform1f(a("uGrainDistortion"),t.grainDistortion/100),e.uniform1f(a("uGrainDrift"),t.grainDrift/100)}function cfSetMaskUniforms(e,o,t,a){const n=s=>cfUniform(e,o,s),r=Math.min(12,t.masks.length),i=new Float32Array(24),u=new Float32Array(48),f=new Float32Array(12);for(let s=0;s<12;s+=1){const l=t.masks[s];if(!l){i.set([.5,.5],s*2),u.set([1,0,1,1],s*4);continue}const d=l.rotation*Math.PI/180;i.set([(l.centerX+1)/2,(1-l.centerY)/2],s*2),u.set([Math.cos(d),Math.sin(d),200/l.width,200/l.height],s*4),f[s]=l.blur/100}e.uniform1i(n("uMaskCount"),r),e.uniform2fv(n("uMaskCenter[0]"),i),e.uniform4fv(n("uMaskAxes[0]"),u),e.uniform1fv(n("uMaskBlur[0]"),f),e.uniform1f(n("uMaskEnabled"),t.masksEnabled?1:0),e.uniform1f(n("uMaskPreview"),a?1:0)}function cfSetFrameUniforms(e,o,t,a){e.uniform1i(cfUniform(e,o,"uFrameShape"),a?cfShapeIndex[t.frameShape]??0:0),e.uniform1f(cfUniform(e,o,"uFrameRadius"),cfClamp01(t.cornerRadius/100)),e.uniform2f(cfUniform(e,o,"uFrameOffset"),a?.offsetX??0,a?.offsetY??0),e.uniform2f(cfUniform(e,o,"uFrameSize"),a?.width??1,a?.height??1)}function cfBeginPass(e,o,t,a){e.bindFramebuffer(e.FRAMEBUFFER,o),e.viewport(0,0,t.width,t.height),e.disable(e.BLEND),e.clearColor(0,0,0,0),e.clear(e.COLOR_BUFFER_BIT),e.useProgram(a.program)}function cfDrawFieldPass(e,o,t,a,n,r,i){const u=o.field,{scale:f,view:s}=i,l=r.composite==="over-backdrop",d=r.background;cfBeginPass(e,t,a,u),e.uniform3f(cfUniform(e,u,"iResolution"),s?s.width*f:a.width,s?s.height*f:a.height,1),e.uniform2f(cfUniform(e,u,"uViewOffset"),s?s.offsetX*f:0,s?s.offsetY*f:0),cfSetFieldUniforms(e,u,n,cfLoopTime(n,r)),cfSetFormUniforms(e,u,n,r.loopProgress),cfSetPointerUniforms(e,u,n,r.pointer,[s?s.width*f:a.width,s?s.height*f:a.height]),cfSetEffectUniforms(e,u,n),cfSetMaskUniforms(e,u,n,r.maskPreview===!0),cfSetFrameUniforms(e,u,n,i.box),e.uniform1f(cfUniform(e,u,"uOpaque"),r.composite==="transparent"?0:1),e.uniform1f(cfUniform(e,u,"uRemoveBackdrop"),l&&!n.lens.enabled?1:0),e.uniform1f(cfUniform(e,u,"uPremultiply"),l?0:1),e.uniform3f(cfUniform(e,u,"uBg"),d[0],d[1],d[2]),e.activeTexture(e.TEXTURE0),e.bindTexture(e.TEXTURE_2D,o.noise),e.uniform1i(cfUniform(e,u,"u_noiseTexture"),0),cfDrawQuad(e,o.quad)}function cfDrawLensPass(e,o,t,a,n,r,i,u){const f=o.lens,s=i.background;cfBeginPass(e,t,a,f),e.activeTexture(e.TEXTURE0),e.bindTexture(e.TEXTURE_2D,n),e.uniform1i(cfUniform(e,f,"u_image"),0),e.uniform4f(cfUniform(e,f,"uViewWindow"),0,0,1,1),e.uniform1f(cfUniform(e,f,"uRemoveBackdrop"),i.composite==="over-backdrop"?1:0),e.uniform3f(cfUniform(e,f,"uBg"),s[0],s[1],s[2]),e.uniform1f(cfUniform(e,f,"u_imageAspectRatio"),a.width/a.height);for(const l of CF_LENS_KEYS)e.uniform1f(cfUniform(e,f,`u_${l}`),r.lens[l]);e.uniform1f(cfUniform(e,f,"u_count"),Math.min(50,Math.round(r.lens.count))),cfSetFrameUniforms(e,f,r,u),cfDrawQuad(e,o.quad)}function cfDrawCopyPass(e,o,t,a,n,r){const i=o.copy;cfBeginPass(e,null,t,i),e.activeTexture(e.TEXTURE0),e.bindTexture(e.TEXTURE_2D,a),e.uniform1i(cfUniform(e,i,"uTexture"),0),cfSetFrameUniforms(e,i,n,r),cfDrawQuad(e,o.quad)}function createCfMotion(e,o={}){const t=e.getContext("webgl2",{alpha:!0,antialias:!1,depth:!1,powerPreference:"low-power",premultipliedAlpha:!0,preserveDrawingBuffer:o.preserveDrawingBuffer===!0,stencil:!1});if(!t)return null;let a=null,n=null,r=null;const i=cfMaxSize(t),u=()=>(a??(a={copy:cfCreateProgram(t,CF_MOTION_QUAD_VERTEX,CF_MOTION_COPY_FRAGMENT),field:cfCreateProgram(t,CF_MOTION_QUAD_VERTEX,CF_MOTION_FIELD_FRAGMENT),lens:cfCreateProgram(t,CF_MOTION_LENS_VERTEX,CF_MOTION_LENS_FRAGMENT),noise:cfCreateNoiseTexture(t,o.noiseTexture),quad:cfCreateQuad(t)}),a),f=(d,h,p)=>d&&d.width===h&&d.height===p?d:(d&&cfDeleteTarget(t,d),cfCreateTarget(t,h,p));return{dispose:()=>{t.isContextLost()||(a&&(t.deleteProgram(a.copy.program),t.deleteProgram(a.field.program),t.deleteProgram(a.lens.program),t.deleteTexture(a.noise),t.deleteBuffer(a.quad)),n&&cfDeleteTarget(t,n),r&&cfDeleteTarget(t,r)),a=null,n=null,r=null},isLost:()=>t.isContextLost(),maxSize:()=>i,render:(d,h)=>{if(t.isContextLost())return;const p=Math.max(1,Math.round(h.width)),g=Math.max(1,Math.round(h.height));e.width!==p&&(e.width=p),e.height!==g&&(e.height=g);const y=u(),A=Math.min(1,Math.max(.1,h.internalScale??1)),v={height:Math.max(1,Math.round(g*A)),width:Math.max(1,Math.round(p*A))},w=d.lens.enabled,_=v.width===p&&v.height===g,S=!w&&_,k=!w&&h.view?h.view:null,F=k??{height:g,offsetX:0,offsetY:0,width:p};if(S||(n=f(n,v.width,v.height)),cfDrawFieldPass(t,y,S?null:n?.framebuffer??null,v,d,h,{box:S?F:null,scale:A,view:k}),S||!n)return;let M=n.texture;if(w){if(_||(r=f(r,v.width,v.height)),cfDrawLensPass(t,y,_?null:r?.framebuffer??null,v,M,d,h,_?F:null),_||!r)return;M=r.texture}cfDrawCopyPass(t,y,{height:g,width:p},M,d,F)},reset:()=>{a=null,n=null,r=null},surface:e}}function mountCfMotion(e,o){const t=document.createElement("canvas");t.setAttribute("aria-hidden","true"),t.style.cssText="display:block;position:absolute;max-width:none;pointer-events:none;",e.appendChild(t);const a=createCfMotion(t,{noiseTexture:o.noise});if(!a)return t.remove(),{destroy:()=>{},setPaused:()=>{}};let n=!1,r=!1,i=!1,u=!0,f=typeof document>"u"||document.visibilityState!=="hidden",s=0;const l=Math.max(.5,o.durationSeconds);let d=((o.startSeconds||0)%l+l)%l;const h=typeof o.pace=="number"&&o.pace>0?Math.min(4,o.pace):1;let p=null,slowFrames=0,g=1,y=1,A=1,v=1,w=1,_=1,S=0;const k=o.background?"opaque":"transparent",F=o.background??[0,0,0],M=o.params.lens.enabled,D=o.respectReducedMotion&&typeof window.matchMedia=="function"?window.matchMedia("(prefers-reduced-motion: reduce)"):null,G=!!(o.params.pointer&&o.params.pointer.enabled),E={presence:0,x:.5,y:.5},R={presence:0,x:.5,y:.5};let I=!1;const W=c=>{const m=e.getBoundingClientRect(),b=o.aspectRatio>0?o.aspectRatio:m.width/Math.max(1,m.height),x=Math.max(m.width,m.height*b),P=x/b,T=c.clientX>=m.left&&c.clientX<=m.right&&c.clientY>=m.top&&c.clientY<=m.bottom;E.x=Math.min(1,Math.max(0,(c.clientX-m.left-(m.width-x)/2)/x)),E.y=Math.min(1,Math.max(0,(c.clientY-m.top-(m.height-P)/2)/P)),E.presence=T?1:0,I=!0,C()},O=()=>{E.presence=0,I=!0,C()},q=c=>{c.pointerType!=="mouse"&&O()},z=c=>2*Math.round(c/4),U=()=>{if(n||r)return;const c=_/Math.max(1,w),m=Math.max(1,Math.round(g*c)),b=Math.max(1,Math.round(y*c)),x=Math.max(m,Math.round(A*w*c)),P=Math.max(b,Math.round(v*w*c));a.render(o.params,{background:[F[0],F[1],F[2]],composite:k,durationSeconds:l,height:y,internalScale:c,loopProgress:d/l,pointer:G&&R.presence>.001?{presence:R.presence,x:R.x,y:R.y}:void 0,view:M?void 0:{height:P/c,offsetX:z(x-m)/c,offsetY:z(P-b)/c,width:x/c},width:g})},V=()=>!n&&!r&&!i&&f&&u&&!(D&&D.matches),H=c=>{if(s=0,!V()){p=null;return}if(p!==null){const m=c-p;slowFrames=m>64?slowFrames+1:0;if(slowFrames>=12&&o.onSlow){o.onSlow();return}d=(d+Math.min(m,250)/1e3*h)%l,S=m>64?S+1:Math.max(0,S-1),S>24&&_>.5&&(_=Math.max(.5,_-.125),S=0)}if(G&&(I||R.presence>.001)){const m=p===null?.016666666666666666:(c-p)/1e3;I=cfFollowPointer(R,E,m,o.params.pointer.smoothing)}p=c,U(),s=window.requestAnimationFrame(H)},C=()=>{s||!V()||(s=window.requestAnimationFrame(H))},$=()=>{const c=Math.min(window.devicePixelRatio||1,Math.max(.5,o.maxPixelRatio)),m=e.getBoundingClientRect(),b=Math.max(1,m.width),x=Math.max(1,m.height),P=o.aspectRatio>0?o.aspectRatio:b/x,T=Math.max(b,x*P),N=T/P,L=M?T:b,X=M?N:x;t.style.width=`${L}px`,t.style.height=`${X}px`,t.style.left=`${(b-L)/2}px`,t.style.top=`${(x-X)/2}px`;const ee=a.maxSize(),B=Math.min(1,ee/Math.max(T*c,N*c));g=Math.max(1,Math.round(L*c*B)),y=Math.max(1,Math.round(X*c*B)),A=T*B,v=N*B,w=c,U()},Y=typeof ResizeObserver=="function"?new ResizeObserver($):null;Y?.observe(e);const K=o.pauseOffscreen&&typeof IntersectionObserver=="function"?new IntersectionObserver(c=>{u=c.some(m=>m.isIntersecting),C()}):null;K?.observe(e);const Z=()=>{f=document.visibilityState!=="hidden",C()};document.addEventListener("visibilitychange",Z),G&&(window.addEventListener("pointermove",W,{passive:!0}),window.addEventListener("pointerdown",W,{passive:!0}),window.addEventListener("pointerup",q,{passive:!0}),window.addEventListener("pointercancel",O,{passive:!0}),document.documentElement.addEventListener("pointerleave",O));const j=()=>{U(),C()};D?.addEventListener?.("change",j);const Q=c=>{c.preventDefault(),r=!0},J=()=>{r=!1,a.reset(),U(),C()};if(t.addEventListener("webglcontextlost",Q),t.addEventListener("webglcontextrestored",J),getComputedStyle(e).position==="absolute"){const c=e.offsetParent;(!c||c===document.documentElement||c===document.body)&&getComputedStyle(document.body).position==="static"&&(console.warn("Wayglim Prism Flow: no positioned parent found (add position: relative to the hero section), so it renders as a block with the studio aspect ratio."),e.style.position="relative",e.style.top="",e.style.left="",e.style.width="100%",e.style.height="auto",e.style.aspectRatio=String(o.aspectRatio>0?o.aspectRatio:16/9))}return e.getBoundingClientRect().height<1&&typeof console<"u"&&console.warn("Wayglim Prism Flow: the container has no height. Give it a height, or place it inside a positioned (position: relative) section with a height."),$(),C(),{destroy:()=>{n=!0,s&&window.cancelAnimationFrame(s),Y?.disconnect(),K?.disconnect(),document.removeEventListener("visibilitychange",Z),window.removeEventListener("pointermove",W),window.removeEventListener("pointerdown",W),window.removeEventListener("pointerup",q),window.removeEventListener("pointercancel",O),document.documentElement.removeEventListener("pointerleave",O),D?.removeEventListener?.("change",j),t.removeEventListener("webglcontextlost",Q),t.removeEventListener("webglcontextrestored",J),a.dispose(),t.remove()},setPaused:c=>{i=c,c||C()}}}
window.Wayglim = { mount: mountCfMotion };
})();
