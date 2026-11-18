const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const crypto = require('node:crypto');
const source = fs.readFileSync(require('node:path').join(__dirname, '../public_html/js/stand-checkout.js'), 'utf8');
function browser() {
  const store = new Map(), calls = [], redirects = [], forms = [];
  let payment = {status:'pending', redirect_url:'https://bank.example/pay', redirect_method:'GET'};
  let failCreate = false;
  const context = {crypto, sessionStorage:{getItem:k=>store.get(k)||null,setItem:(k,v)=>store.set(k,v),removeItem:k=>store.delete(k)},
    window:{location:{assign:url=>redirects.push(url)}},
    HTMLFormElement:{prototype:{submit(){forms.push(this);}}},
    document:{body:{appendChild(){}},createElement:()=>({children:[],appendChild(input){this.children.push(input);}})},
    fetch:async (path, options)=>{
      calls.push({path, body:JSON.parse(options.body), headers:options.headers});
      if (failCreate && path === '/api/orders') throw new Error('lost response');
      const data = path === '/api/orders' ? {tracking_code:'ORD-123'} : payment;
      return {ok:true,status:200,json:async()=>({success:true,data})};
    }};
  vm.runInNewContext(source, context);
  return {context, store, calls, redirects, forms, setPayment:p=>payment=p, failCreate:v=>failCreate=v, start:(payload)=>context.window.MaksaStandCheckout.start(payload,'jwt')};
}
(async()=>{
  const payload = {stand_id:17, sender_phone:'09121234567', sender_name:'Sender', receiver_name:'Receiver'};
  const env = browser();
  await env.start(payload);
  assert.equal(env.redirects[0], 'https://bank.example/pay');
  assert.equal(env.calls[1].path, '/api/orders/ORD-123/payment');
  assert.deepEqual(env.calls[1].body, {mobile:payload.sender_phone}, 'Payment amount/branch/campaign must not come from the browser');
  assert.equal(env.calls[1].headers.Authorization, 'Bearer jwt');
  const key = env.calls[0].body.checkout_key;
  assert.ok(env.store.size, 'Pending checkout remains recoverable until authoritative success');
  await env.start(payload);
  assert.equal(env.calls[2].body.checkout_key,key,'Retries reuse the same order identity');
  env.setPayment({status:'success',tracking_code:'ORD-123'});
  const paid = await env.start(payload);
  assert.equal(paid.status,'success');
  assert.equal(env.redirects.length,2,'Verified paid orders never redirect again');
  assert.equal(env.store.size,0,'Verified success clears the pending attempt');
  env.setPayment({status:'failed'});
  await assert.rejects(env.start(payload), /پرداخت سفارش تأیید نشد/);
  assert.equal(env.store.size,0,'Terminal failure allows a fresh checkout');
  env.setPayment({status:'pending',redirect_url:'https://bank.example/post',redirect_method:'POST',redirect_fields:{token:'bank-token'}});
  await env.start(payload);
  assert.equal(env.forms.length,1,'POST gateways submit a real form');
  assert.equal(env.forms[0].children[0].name,'token');
  assert.equal(env.forms[0].children[0].value,'bank-token');
  const broken = browser();
  broken.failCreate(true);
  await assert.rejects(broken.start(payload), /lost response/);
  const pendingKey=broken.calls[0].body.checkout_key;
  broken.failCreate(false);
  await broken.start(payload);
  assert.equal(broken.calls[1].body.checkout_key,pendingKey,'A lost create response cannot create duplicate orders on retry');
  const renderedSource = fs.readFileSync(require('node:path').join(__dirname, '../public_html/dashboard/components/stand-order/component.php'), 'utf8');
  const submit = renderedSource.slice(renderedSource.indexOf('const payment = await window.MaksaStandCheckout.start'));
  assert.match(submit, /if \(payment.status === 'success'\)\s*\{\s*openSuccessModal/, 'The component shows success only after verified payment');
  console.log('PASS stand checkout: auth, server-owned amounts, GET/POST redirects, duplicate/lost-response retries, pending/failed/success behavior');
})().catch(error=>{console.error(error);process.exitCode=1;});
