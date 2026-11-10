<?php
declare(strict_types=1);
// Provider contract is mocked: no real API key, network calls, or billable requests.
require_once __DIR__.'/../public_html/core/content-translation.php';
function translation_check(bool $value,string $message): void {if(!$value)throw new RuntimeException($message);}
function translation_reject(callable $operation,int $status): void {try{$operation();throw new LogicException('Expected rejection');}catch(Throwable $error){translation_check($error->getCode()===$status,'Expected HTTP '.$status.', got '.$error->getCode());}}
$calls=0;$config=['api_key'=>'local-test-key','cache_dir'=>''];
$provider=static function($url,$headers,$body)use(&$calls){
    $calls++;translation_check($url==='https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent','Only requested text model can be invoked');
    translation_check(in_array('x-goog-api-key: local-test-key',$headers,true),'Key travels in a server-side header');
    $body=json_decode($body,true,64,JSON_THROW_ON_ERROR);translation_check($body['generationConfig']['responseFormat']['text']['mimeType']==='APPLICATION_JSON','Structured JSON output');
    $input=json_decode($body['contents'][0]['parts'][0]['text'],true,64,JSON_THROW_ON_ERROR);
    $rows=[];foreach($input['segments'] as $segment){translation_check(!str_contains($segment['text'],'https://')&&!str_contains($segment['text'],'@example.test'),'Addresses are protected before provider calls');$rows[]=['id'=>$segment['id'],'text'=>($input['target_language']==='en'?'English: ':'عربي: ').$segment['text']];}
    return ['status'=>200,'body'=>json_encode(['candidates'=>[['finishReason'=>'STOP','content'=>['parts'=>[['text'=>json_encode(['translations'=>$rows],JSON_UNESCAPED_UNICODE)]]]]]],JSON_UNESCAPED_UNICODE)];
};
function translation_fixture(array $schema): array {
    $fields=[];foreach($schema as $key=>$definition)$fields[$key]=match($definition['type']){
        'asset'=>['source_fa'=>true], 'number'=>12, 'checkbox'=>1, 'select'=>array_key_first($definition['options']),
        'list'=>[translation_fixture($definition['fields'])], 'html'=>'<p>متن مکسا <strong>مراقبت تسکینی</strong></p>',default=>'متن فارسی '.$key};
    foreach(['button_link','banner_link','registration_url','instagram','linkedin']as $key)if(isset($fields[$key]))$fields[$key]='https://example.test/persian';
    return $fields;
}
$types=0;
foreach(content_types()as $type=>$definition){
    $source=translation_fixture($definition['fields']);$source['private_unregistered']='must never reach provider';
    if($type==='courses'){$source['curriculum'][0]['lessons'][0]['type']='text';$source['curriculum'][0]['lessons'][0]['content']='متن درس فارسی';}
    foreach(['en','ar']as $locale){
        $before=$calls;$translated=content_translate_fields($type,$locale,$source,$config,'test',$provider);
        translation_check($calls===$before+1,$type.': one provider request for all text fields');
        translation_check(!isset($translated['private_unregistered']),$type.': editorial allowlist');
        foreach($definition['fields']as $key=>$field){
            if($field['type']==='asset')translation_check($translated[$key]===$source[$key],$type.': files retained');
            if(in_array($key,['button_link','banner_link','registration_url','instagram','linkedin'],true))translation_check($translated[$key]===$source[$key],$type.': links retained');
        }
    }$types++;
}
$html='<style>.x::after{content:"فارسی"}</style><script>const label="فارسی";</script><div class="x" data-key="فارسی"><p title="راهنما"> متن فارسی {{image1}} <a href="https://example.test/fa">بیشتر</a></p><img src="/uploads/fa.jpg" alt="تصویر فارسی"></div>';
$component=content_translate_fields('components','en',['content'=>$html,'images'=>[['url'=>['source_fa'=>true]]]],$config,'test',$provider);
translation_check(str_contains($component['content'],'<style>.x::after{content:"فارسی"}</style><script>const label="فارسی";</script>'),'Executable code is never translated');
translation_check(str_contains($component['content'],'data-key="فارسی"')&&str_contains($component['content'],'href="https://example.test/fa"')&&str_contains($component['content'],'src="/uploads/fa.jpg"'),'Attributes and file paths retained');
translation_check(str_contains($component['content'],'title="English: راهنما"')&&str_contains($component['content'],'alt="English: تصویر فارسی"'),'Accessible labels translated');
translation_check(str_contains($component['content'],'{{image1}}'),'Component image placeholders retained');
$course=translation_fixture(content_type('courses')['fields']);$lesson=&$course['curriculum'][0]['lessons'][0];$lesson['type']='pdf';$lesson['content']='https://example.test/fa.pdf';unset($lesson);
$translated=content_translate_fields('courses','en',$course,$config,'test',$provider);
translation_check($translated['curriculum'][0]['lessons'][0]['content']==='https://example.test/fa.pdf','Lesson media is retained, not translated');
translation_check($translated['curriculum'][0]['lessons'][0]['duration']===12,'Lesson duration is unchanged');
$withUrl=content_translate_fields('news','en',['title'=>'تماس info@example.test و https://example.test/fa','content'=>'<p>راهنما {{image2}}</p>'],$config,'test',$provider);
translation_check(str_contains($withUrl['title'],'info@example.test')&&str_contains($withUrl['title'],'https://example.test/fa'),'Inline addresses restored');
$malicious=static fn()=>['status'=>200,'body'=>json_encode(['candidates'=>[['finishReason'=>'STOP','content'=>['parts'=>[['text'=>json_encode(['translations'=>[['id'=>'s0','text'=>'<script>alert(1)</script>']]])]]]]]])];
$safe=content_translate_fields('news','en',['content'=>'<p>خبر</p>'],$config,'test',$malicious);
translation_check(!str_contains($safe['content'],'<script>')&&str_contains($safe['content'],'&lt;script&gt;'),'Provider text cannot inject HTML');
$before=$calls;$code='<style>.a{color:red}</style><img src="/uploads/one.jpg">';$codeOnly=content_translate_fields('components','en',['content'=>$code],$config,'test',$provider);
translation_check($calls===$before&&$codeOnly['content']===$code,'Code-only components copy without wasting provider quota');
translation_reject(fn()=>content_translate_fields('news','fa',['title'=>'خبر'],$config,'test',$provider),422);
translation_reject(fn()=>content_translate_fields('news','en',[],$config,'test',$provider),422);
translation_reject(fn()=>content_translate_fields('news','en',['title'=>str_repeat('خبر',50000)],$config,'test',$provider),413);
translation_reject(fn()=>content_translate_fields('news','en',['title'=>'خبر'],['api_key'=>'','cache_dir'=>''],'test',$provider),503);
foreach([429=>429,401=>503,402=>503,503=>502]as $remote=>$expected)translation_reject(fn()=>content_translate_fields('news','en',['title'=>'خبر'],$config,'test',static fn()=>['status'=>$remote,'body'=>'secret provider diagnostic']),$expected);
translation_reject(fn()=>content_translate_fields('news','en',['title'=>'خبر'],$config,'test',static fn()=>['status'=>200,'body'=>'invalid JSON']),502);
translation_reject(fn()=>content_translate_fields('news','en',['title'=>'خبر'],$config,'test',static fn()=>['status'=>200,'body'=>json_encode(['candidates'=>[['finishReason'=>'MAX_TOKENS']]])]),502);
translation_reject(fn()=>content_translate_fields('news','en',['title'=>'خبر {{image1}}'],$config,'test',$malicious),502);
$cache=dirname(__DIR__).'/.local/translation-test-'.bin2hex(random_bytes(6));$cachedConfig=['api_key'=>'local-test-key','cache_dir'=>$cache];
try{
    $before=$calls;$first=content_translate_fields('news','en',['title'=>'خبر'],$cachedConfig,'user1',$provider);$again=content_translate_fields('news','en',['title'=>'خبر'],$cachedConfig,'user1',$provider);
    translation_check($calls===$before+1&&$first===$again,'Unchanged source reuses cached translations');
    content_translate_fields('news','en',['title'=>'خبر جدید'],$cachedConfig,'user1',$provider);content_translate_fields('news','ar',['title'=>'خبر'],$cachedConfig,'user1',$provider);content_translate_fields('news','en',['title'=>'خبر'],$cachedConfig,'user2',$provider);
    translation_check($calls===$before+4,'Source, language and account isolate caches');
}finally{foreach(glob($cache.'/*')?:[]as $file)unlink($file);if(is_dir($cache))rmdir($cache);}
echo "PASS $types editorial schemas: fixed Gemini model/contract, nested text, safe HTML, media/links, caching and provider errors; no live calls.\n";
