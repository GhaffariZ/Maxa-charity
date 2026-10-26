<?php
declare(strict_types=1);
// Isolated integration check. All test records are rolled back in the local database.
require_once __DIR__.'/../public_html/core/content-locales.php';
$root=dirname(__DIR__);
$secrets=json_decode((string)file_get_contents($root.'/.local/conf/secrets.json'),true,512,JSON_THROW_ON_ERROR);
$db=new PDO('mysql:host=127.0.0.1;dbname=macsa_local;charset=utf8mb4','macsa_local',$secrets['dbPassword'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
if($db->query('SELECT DATABASE()')->fetchColumn()!=='macsa_local')throw new RuntimeException('Local test database required');
function verify(bool $condition,string $label): void {if(!$condition)throw new RuntimeException($label);}
content_install($db);$db->beginTransaction();$cases=0;
try{
    $category=(int)$db->query('SELECT id FROM news_categories LIMIT 1')->fetchColumn();
    $branch=(int)$db->query('SELECT id FROM branches WHERE is_hq=0 LIMIT 1')->fetchColumn();
    foreach(content_types() as $type=>$definition){
        $id='locale-test-'.bin2hex(random_bytes(6));
        $base=['id'=>$id];
        if(empty($definition['remote'])&&$type!=='components'){
            $row=$db->query("SELECT * FROM `$type` LIMIT 1")->fetch()?:[];unset($row['id']);
            if(!$row){
                foreach($db->query("SHOW COLUMNS FROM `$type`")->fetchAll() as $column){
                    if($column['Field']==='id'||$column['Null']==='YES'||$column['Default']!==null)continue;
                    $row[$column['Field']]=str_contains($column['Type'],'int')?0:'';
                }
            }
            foreach(['title','fullname','name'] as $field)if(array_key_exists($field,$row))$row[$field]='FA fixture';
            if(array_key_exists('slug',$row))$row['slug']=$id;
            if(array_key_exists('news_code',$row))$row['news_code']=$id;
            if(array_key_exists('branch_id',$row))$row['branch_id']=$branch;
            if(array_key_exists('category_id',$row))$row['category_id']=$category;
            if($type==='branch_intros'){
                $row['branch_id']=(int)$db->query('SELECT MAX(id) FROM branches')->fetchColumn()+100000;
                // This table has no foreign key; it uses the branch id as its editorial key.
            }
            $columns=implode(',',array_map(static fn($key)=>'`'.$key.'`',array_keys($row)));
            $s=$db->prepare("INSERT INTO `$type`($columns) VALUES(".implode(',',array_fill(0,count($row),'?')).')');$s->execute(array_values($row));
            $id=(string)(($definition['key']??'id')==='branch_id'?$row['branch_id']:$db->lastInsertId());
            $base=['id'=>$id]+$row;
        }
        $key=$definition['key']??'id';if($key!=='id')$base[$key]=$id;
        verify(content_rows($db,$type,[$base],'en',$key)===[],$type.': no English fallback');
        verify(count(content_rows($db,$type,[$base],'fa',$key))===1,$type.': legacy Persian');
        $source=[];
        foreach($definition['fields'] as $field=>$spec)$source[$field]=$spec['type']==='list'?[]:($spec['type']==='asset'?'':'FA '.$field);
        if($type==='courses')$source['curriculum']=[['id'=>101,'title'=>'فصل','lessons'=>[['id'=>202,'title'=>'درس','type'=>'text','content'=>'متن','duration'=>5,'is_preview'=>1]]]];
        if($type==='components')$source['content']='<p>فارسی</p>';
        $en=$source;foreach($definition['fields'] as $field=>$spec)if($spec['type']==='text')$en[$field]='EN '.$field;
        $payload=['enabled'=>['fa','en','ar'],'translations'=>['en'=>['copy'=>false,'fields'=>$en],'ar'=>['copy'=>true,'fields'=>[]]]];
        content_save($db,$type,$id,json_encode($payload,JSON_UNESCAPED_UNICODE),$source);
        $english=content_row($db,$type,$base,'en',$key);$arabic=content_row($db,$type,$base,'ar',$key);
        verify($english!==null&&$arabic!==null,$type.': both versions available');
        $firstText=array_key_first(array_filter($definition['fields'],static fn($spec)=>$spec['type']==='text'));
        if($firstText){verify($english[$firstText]==='EN '.$firstText,$type.': independent English');verify($arabic[$firstText]===$source[$firstText],$type.': explicit Persian snapshot');}
        $payload['enabled']=['en'];content_save($db,$type,$id,json_encode($payload,JSON_UNESCAPED_UNICODE),$source);
        verify(content_row($db,$type,$base,'fa',$key)===null&&content_row($db,$type,$base,'ar',$key)===null,$type.': disabling a language');
        verify(isset(content_state($db,$type,$id)['translations']['ar']),$type.': disabling keeps editorial draft');
        if(empty($definition['remote'])&&$type!=='components'){
            $scope=content_where($db,$type,$key,'en');$s=$db->prepare("SELECT COUNT(*) FROM `$type` WHERE `$key`=? AND $scope");$s->execute([$id]);verify((int)$s->fetchColumn()===1,$type.': SQL scope / database collation');
        }
        if($type==='courses')verify($english['curriculum'][0]['lessons'][0]['id']===202,'Course identities survive localization');
        $cases++;
    }
    foreach(['tickets','orders','panel_users','donation_impacts'] as $private){try{content_type($private);throw new RuntimeException('Private registry accepted');}catch(InvalidArgumentException $expected){}}
    $bad=['enabled'=>['fr'],'translations'=>[]];try{content_save($db,'hero_slides','bad',json_encode($bad),[]);throw new RuntimeException('Invalid locale accepted');}catch(InvalidArgumentException $expected){}
    foreach([
        ['hero_slides',['button_link'=>'javascript:alert(1)']],
        ['hero_slides',['image'=>'/uploads/%2e%2e/core/db-config.php']],
        ['maxapedia_items',['photos'=>[['layout_span'=>'wide" onclick="alert(1)']]]],
        ['courses',['curriculum'=>[['lessons'=>[['type'=>'video','content'=>'javascript:alert(1)']]]]]],
    ] as [$type,$input]){
        try{content_clean_fields(content_type($type)['fields'],$input,[],$type,'en',true);throw new RuntimeException('Unsafe editorial input accepted');}catch(InvalidArgumentException $expected){}
    }
    $media=content_clean_fields(content_type('courses')['fields'],['curriculum'=>[['lessons'=>[['type'=>'video','content'=>'<iframe src="https://example.test/lesson.mp4"></iframe>']]]]],[],'courses','en',true);
    verify($media['curriculum'][0]['lessons'][0]['content']==='https://example.test/lesson.mp4','Copied course embeds become safe editable media URLs');
    echo "PASS $cases editorial content types: independent versions, explicit copy, strict visibility, SQL scopes, preserved drafts and private-module exclusion.\n";
}finally{if($db->inTransaction())$db->rollBack();}
