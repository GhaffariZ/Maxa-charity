<?php
declare(strict_types=1);

require_once __DIR__.'/site-locale.php';
require_once __DIR__.'/html-sanitizer.php';

/** Editorial content only. Account, finance and workflow tables never enter this registry. */
function content_types(): array
{
    $text = static fn(string $label, string $input = '') => ['label'=>$label,'type'=>'text','input'=>$input];
    $area = static fn(string $label, string $input = '') => ['label'=>$label,'type'=>'textarea','input'=>$input];
    $html = static fn(string $label, string $input = '') => ['label'=>$label,'type'=>'html','input'=>$input];
    $media = static fn(string $label, string $input = '', string $accept = 'image/*') => ['label'=>$label,'type'=>'asset','input'=>$input,'accept'=>$accept];
    $list = static fn(string $label, array $fields) => ['label'=>$label,'type'=>'list','fields'=>$fields];
    $person = ['name'=>$text('نام'),'title'=>$text('عنوان / سمت'),'image'=>$media('تصویر')];
    return [
        'hero_slides'=>['permission'=>'hero','branch'=>true,'fields'=>[
            'title'=>$text('عنوان','title'),'description'=>$html('توضیحات','#editor'),'image'=>$media('تصویر هیرو','image'),
            'button_link'=>$text('لینک دکمه','link'),'category'=>$text('دسته‌بندی','category')]],
        'news'=>['permission'=>'news','branch'=>true,'fields'=>[
            'title'=>$text('عنوان','title'),'subtitle'=>$text('زیرعنوان','subtitle'),'content'=>$html('متن خبر','#editor'),
            'author'=>$text('نویسنده','author'),'category_name'=>$text('نام دسته‌بندی'),'keywords'=>$text('کلیدواژه‌ها','keywords'),
            'tags'=>$text('برچسب‌ها','tags'),'featured_image'=>$media('تصویر شاخص','featured_image')]],
        'employee_profiles'=>['permission'=>'partners','branch'=>true,'fields'=>[
            'fullname'=>$text('نام و نام خانوادگی','fullname'),'role'=>$text('سمت','role'),
            'branch'=>$text('نام محل خدمت'),'study_field'=>$text('رشتهٔ تحصیلی','study_field'),
            'bio_professional'=>$area('معرفی حرفه‌ای','bio_professional'),'academic_background'=>$area('سوابق علمی','academic_background'),
            'maxa_responsibilities'=>$area('مسئولیت‌ها در مکسا','maxa_responsibilities'),'profile_pic'=>$media('عکس همکار','profile_pic'),
            'instagram'=>$text('اینستاگرام','instagram'),'linkedin'=>$text('لینکدین','linkedin')]],
        'branches'=>['permission'=>'super','fields'=>['name'=>$text('نام شعبه','name'),'province'=>$text('استان','province'),'city'=>$text('شهر','city')]],
        'branch_intros'=>['permission'=>'branch','key'=>'branch_id','branch'=>true,'fields'=>[
            'title'=>$text('عنوان','title'),'intro_text'=>$area('متن معرفی','intro_text'),'address'=>$area('نشانی','address'),
            'working_hours'=>$text('ساعت کاری','working_hours'),
            'images'=>$list('تصاویر شعبه',['url'=>$media('تصویر')])]],
        'courses'=>['permission'=>'courses','branch'=>true,'fields'=>[
            'title'=>$text('عنوان دوره','title'),'subtitle'=>$text('زیرعنوان','subtitle'),'description'=>$html('معرفی دوره','description'),
            'category'=>$text('نام دسته‌بندی','category'),'thumbnail'=>$media('تصویر دوره','thumbnail'),'promo_video'=>$media('ویدیوی معرفی','promo_video','video/*'),
            'instructor'=>$text('مدرس','instructor'),'instructor_bio'=>$area('معرفی مدرس','instructor_bio'),
            'what_you_learn'=>$area('آنچه یاد می‌گیرید','what_you_learn'),'requirements'=>$area('پیش‌نیازها','requirements'),
            'curriculum'=>$list('سرفصل‌ها',['title'=>$text('عنوان فصل'),'lessons'=>$list('درس‌ها',[
                'title'=>$text('عنوان درس'),'type'=>['label'=>'نوع درس','type'=>'select','options'=>['video'=>'ویدیو','text'=>'متن','image'=>'تصویر','pdf'=>'PDF','quiz'=>'آزمون']],
                'content'=>$html('محتوا یا نشانی فایل'),'duration'=>['label'=>'مدت به دقیقه','type'=>'number']])])]],
        'events'=>['permission'=>'events','hq'=>true,'fields'=>[
            'title'=>$text('عنوان رویداد','title'),'short_description'=>$area('خلاصه','short_description'),'about'=>$html('دربارهٔ رویداد','about'),
            'poster'=>$media('پوستر','poster'),'schedule_pdf'=>$media('برنامهٔ رویداد','schedule_pdf','application/pdf'),
            'registration_url'=>$text('لینک ثبت‌نام','registration_url'),'banner_label'=>$text('برچسب بنر','banner_label'),
            'banner_cta'=>$text('متن دکمهٔ بنر','banner_cta'),'banner_link'=>$text('لینک بنر','banner_link'),
            'heroes'=>$list('هیروهای رویداد',['title'=>$text('عنوان'),'description'=>$area('توضیحات'),'image'=>$media('تصویر'),'button_label'=>$text('متن دکمه'),'button_link'=>$text('لینک دکمه')]),
            'people'=>$list('مسئولان رویداد',['role'=>['label'=>'مسئولیت','type'=>'select','options'=>['scientific_secretary'=>'دبیر علمی','executive_secretary'=>'دبیر اجرایی','chair'=>'رئیس']]]+$person),
            'speakers'=>$list('سخنرانان',$person),'partners'=>$list('همکاران رویداد',['name'=>$text('نام'),'logo'=>$media('لوگو')])]],
        'event_news'=>['permission'=>'events','hq'=>true,'fields'=>[
            'title'=>$text('عنوان','title'),'excerpt'=>$area('خلاصه','subtitle'),'content'=>$html('متن خبر','#editor'),'image'=>$media('تصویر','featured_image')]],
        'macsa_stories'=>['permission'=>'stories','fields'=>[
            'title'=>$text('عنوان','title'),'narrator_name'=>$text('نام راوی','narrator_name'),'narrator_role'=>$text('معرفی راوی','narrator_role'),
            'tag'=>$text('دسته‌بندی','tag'),'excerpt'=>$area('خلاصه','excerpt'),'content'=>$area('متن روایت','content'),
            'image'=>$media('تصویر','image'),'read_time'=>$text('زمان مطالعه','read_time')]],
        'maxapedia_items'=>['permission'=>'maxapedia','hq'=>true,'fields'=>[
            'title'=>$text('عنوان','title'),'category'=>$text('دسته‌بندی','category'),'description'=>$area('توضیحات','description'),
            'url'=>$media('فایل یا نشانی محتوا','url','application/pdf,application/epub+zip,application/x-mobipocket-ebook,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-zip-compressed,application/vnd.rar,application/x-rar,application/x-7z-compressed,audio/*,video/*,image/*'),'thumbnail'=>$media('تصویر جلد','thumbnail'),
            'photos'=>$list('عکس‌های آلبوم',['image_url'=>$media('عکس'),'caption'=>$text('شرح تصویر'),'layout_span'=>['label'=>'اندازه','type'=>'select','options'=>['normal'=>'عادی','wide'=>'عریض','tall'=>'بلند','featured'=>'ویژه']]])]],
        'stands'=>['permission'=>'stands','branch'=>true,'fields'=>['title'=>$text('نام طرح','title'),'description'=>$area('توضیحات','description'),'image'=>$media('تصویر','image')]],
        'components'=>['permission'=>'pages','fields'=>['content'=>$html('محتوای کامپوننت','component_code'),'images'=>$list('تصاویر کامپوننت',['url'=>$media('تصویر')])]],
        'pages'=>['permission'=>'pages','branch'=>true,'fields'=>['title'=>$text('عنوان صفحه','title')]],
        'campaigns'=>['permission'=>'campaigns','remote'=>true,'fields'=>['title'=>$text('عنوان کمپین','title'),'description'=>$html('شرح کمپین','description'),'image_url'=>$media('تصویر کمپین','featured_image')]],
    ];
}

function content_type(string $type): array
{
    $types=content_types();
    if (!isset($types[$type])) throw new InvalidArgumentException('نوع محتوا معتبر نیست.');
    return $types[$type];
}

function content_pdo(): PDO
{
    static $db;
    if (!$db) {
        $cfg=require __DIR__.'/db-config.php';
        $db=new PDO("mysql:host={$cfg['host']};dbname={$cfg['name']};charset={$cfg['charset']}",$cfg['user'],$cfg['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    }
    return $db;
}

/** Idempotent additive migration; public requests never perform DDL. */
function content_install(PDO $db): void
{
    static $installed=[];if(isset($installed[spl_object_id($db)]))return;
    $db->exec("CREATE TABLE IF NOT EXISTS public_content_locales (
        content_type VARCHAR(48) NOT NULL, content_id VARCHAR(190) NOT NULL, locale CHAR(2) NOT NULL,
        enabled TINYINT(1) NOT NULL DEFAULT 0, copy_fa TINYINT(1) NOT NULL DEFAULT 0,
        payload LONGTEXT NOT NULL, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY(content_type,content_id,locale), INDEX locale_listing(content_type,locale,enabled)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $installed[spl_object_id($db)]=true;
}

function content_ready(PDO $db): bool
{
    static $ready=[]; $key=spl_object_id($db);
    if (!array_key_exists($key,$ready)) {
        try { $db->query('SELECT 1 FROM public_content_locales LIMIT 0'); $ready[$key]=true; }
        catch (PDOException $e) { if (in_array((string)$e->getCode(),['42S02','42P01'],true)) $ready[$key]=false; else throw $e; }
    }
    return $ready[$key];
}

function content_language(): string
{
    if(!content_public())return 'fa';
    $language=defined('MACSA_PUBLIC_PATH') ? site_locale() : ($_GET['lang'] ?? site_locale());
    return in_array($language,['fa','ar','en'],true) ? $language : 'fa';
}

function content_public(): bool
{
    if(defined('MACSA_PUBLIC_PATH'))return true;
    $path=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)?:'/';
    return !str_starts_with($path,'/dashboard/')||in_array($path,['/dashboard/hero-list.php','/dashboard/recent-news-feed.php','/dashboard/stories-feed.php','/dashboard/branch-courses-feed.php','/dashboard/campaign-latest.php','/dashboard/campaign-list.php','/dashboard/news-view.php','/dashboard/personal-resume-detail.php'],true);
}

/** Apply before ORDER BY, LIMIT, search and aggregate counts. Never infer a language from Referer. */
function content_where(PDO $db, string $type, string $idColumn='id', ?string $locale=null): string
{
    content_type($type); $locale ??= content_language();
    if (!in_array($locale,['fa','ar','en'],true) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)?$/D',$idColumn)) throw new InvalidArgumentException('Invalid content scope');
    if (!content_ready($db)) return $locale==='fa' ? '1=1' : '0=1';
    $match="cl.content_type='$type' AND cl.content_id=CAST($idColumn AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci AND cl.locale='$locale'";
    return $locale==='fa' ? "NOT EXISTS(SELECT 1 FROM public_content_locales cl WHERE $match AND cl.enabled=0)" : "EXISTS(SELECT 1 FROM public_content_locales cl WHERE $match AND cl.enabled=1)";
}

function content_rows(PDO $db,string $type,array $rows,?string $locale=null,string $idKey='id'): array
{
    $definition=content_type($type); $locale ??= content_language();
    if (!$rows) return [];
    if (!content_ready($db)) return $locale==='fa' ? $rows : [];
    $ids=array_values(array_unique(array_map(static fn($row)=>(string)($row[$idKey]??''),$rows)));
    $statement=$db->prepare('SELECT content_id,enabled,payload FROM public_content_locales WHERE content_type=? AND locale=? AND content_id IN ('.implode(',',array_fill(0,count($ids),'?')).')');
    $statement->execute(array_merge([$type,$locale],$ids)); $translations=[];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $translation) $translations[$translation['content_id']]=$translation;
    $result=[];
    foreach ($rows as $row) {
        // Filters use stable categories while the displayed label remains editorial content.
        if($type==='employee_profiles')foreach(['role','job_category'] as $field)$row[$field.'_key']=preg_match('/^[a-z_]+$/D',(string)($row[$field]??''))?(string)$row[$field]:'';
        if($type==='macsa_stories'){
            $row['tag_key']='';
            foreach(['staff'=>'کادر درمان','survivor'=>'بهبودی','diagnosis'=>'تشخیص','caregiver'=>'همراهان','treatment'=>'درمان'] as $key=>$label)if(str_contains((string)($row['tag']??''),$label)){$row['tag_key']=$key;break;}
        }
        $translation=$translations[(string)($row[$idKey]??'')]??null;
        if ($locale==='fa') { if (!$translation || $translation['enabled']) $result[]=$row; continue; }
        if (!$translation || !$translation['enabled']) continue;
        $data=json_decode($translation['payload'],true,512,JSON_THROW_ON_ERROR);
        foreach ($definition['fields'] as $key=>$field) $row[$key]=$data[$key]??($field['type']==='list'?[]:'');
        if($type==='campaigns'){
            $row['short_description']=trim(strip_tags($row['description']));
            $categories=['en'=>['food'=>'Food','drug'=>'Medicine','education'=>'Skills training'],'ar'=>['food'=>'الغذاء','drug'=>'الدواء','education'=>'التدريب على المهارات']];
            $row['category_name']=$categories[$locale][$row['category']??'']??'';
        }
        if($type==='components')$row['content']=preg_replace_callback('/\{\{image([1-9][0-9]*)\}\}/',static fn($match)=>(string)($row['images'][(int)$match[1]-1]['url']??''),$row['content']);
        if($type==='branch_intros')$row['images']=json_encode(array_map(static fn($image)=>(string)($image['url']??''),$row['images']),JSON_UNESCAPED_UNICODE);
        if ($type==='news') $row['read_time']=max(1,(int)ceil(count(preg_split('/\s+/u',trim(strip_tags($row['content'])),-1,PREG_SPLIT_NO_EMPTY))/200));
        $row['_content_locale']=$locale; $result[]=$row;
    }
    return $result;
}

function content_row(PDO $db,string $type,?array $row,?string $locale=null,string $idKey='id'): ?array
{
    return $row ? (content_rows($db,$type,[$row],$locale,$idKey)[0]??null) : null;
}

/** Search and taxonomy labels use the selected version, never the Persian text. */
function content_expression(PDO $db,string $type,string $idColumn,string $field,string $baseColumn): string
{
    $definition=content_type($type);$locale=content_language();
    if(!isset($definition['fields'][$field])||!preg_match('/^[a-zA-Z_][a-zA-Z0-9_.]*$/D',$idColumn.$baseColumn))throw new InvalidArgumentException('Invalid content field');
    if($locale==='fa')return $baseColumn;
    if(!content_ready($db))return "''";
    return "(JSON_UNQUOTE(JSON_EXTRACT((SELECT cx.payload FROM public_content_locales cx WHERE cx.content_type='$type' AND cx.content_id=CAST($idColumn AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci AND cx.locale='$locale' AND cx.enabled=1 LIMIT 1),'$.".$field."')) COLLATE utf8mb4_unicode_ci)";
}

/** Source snapshots include nested public content, not administrative or payment data. */
function content_branch_labels(PDO $db,array $rows,string $idKey='branch_id'): array
{
    foreach($rows as &$row){
        $context=content_branch_context($db,['id'=>$row[$idKey],'name'=>$row['branch_name']??$row['name']??'','province'=>$row['province']??'','city'=>$row['city']??'']);
        $row['branch_name']=$context['name'];$row['province']=$context['province']?:$context['name'];$row['city']=$context['city']?:$context['name'];
    }
    unset($row);return $rows;
}

/** A branch id is routing context, even when its editorial name has no version yet. */
function content_branch_context(PDO $db,?array $branch): ?array
{
    if(!$branch)return null;
    $localized=content_row($db,'branches',$branch);
    if($localized)return $localized;
    foreach(content_type('branches')['fields'] as $key=>$field)$branch[$key]='';
    $labels=['fa'=>'مکسا','ar'=>'فرع مكسا','en'=>'MACSA branch'];
    $branch['name']=$labels[content_language()].' '.(int)$branch['id'];
    return $branch;
}

function content_source(PDO $db,string $type,string $id): array
{
    $definition=content_type($type);
    if ($type==='components') {
        if (!preg_match('/^[\p{L}\p{N}_-]+$/uD',$id)) return [];
        $path=dirname(__DIR__).'/dashboard/components/'.$id.'/data.json';
        $row=is_file($path)?json_decode((string)file_get_contents($path),true):[];
        if($row){require_once __DIR__.'/component-renderer.php';$row['images']=array_map(static fn($image)=>['url'=>'/dashboard/components/'.rawurlencode($id).'/images/'.rawurlencode($image)],dashboard_component_images($id));}
    } elseif (!empty($definition['remote'])) return [];
    else {
        $key=$definition['key']??'id';
        $s=$db->prepare("SELECT * FROM `$type` WHERE `$key`=? LIMIT 1"); $s->execute([$id]); $row=$s->fetch(PDO::FETCH_ASSOC)?:[];
    }
    if (!$row) return [];
    if ($type==='news') {
        $s=$db->prepare('SELECT name FROM news_categories WHERE id=?'); $s->execute([$row['category_id']]); $row['category_name']=$s->fetchColumn()?:'';
        if (!empty($row['featured_image']) && $row['featured_image'][0]!=='/') $row['featured_image']='/uploads/news/'.$row['news_code'].'/'.$row['featured_image'];
    }
    if ($type==='events') foreach (['heroes'=>'event_heroes','people'=>'event_people','speakers'=>'event_speakers','partners'=>'event_partners'] as $key=>$table) {
        $s=$db->prepare("SELECT * FROM $table WHERE event_id=? ORDER BY sort_order,id"); $s->execute([$id]); $row[$key]=$s->fetchAll(PDO::FETCH_ASSOC);
    }
    if ($type==='courses') {
        $s=$db->prepare('SELECT * FROM course_sections WHERE course_id=? ORDER BY sort_order,id');$s->execute([$id]);$row['curriculum']=$s->fetchAll(PDO::FETCH_ASSOC);
        foreach($row['curriculum'] as &$section){$s=$db->prepare('SELECT * FROM course_lessons WHERE section_id=? ORDER BY sort_order,id');$s->execute([$section['id']]);$section['lessons']=$s->fetchAll(PDO::FETCH_ASSOC);}unset($section);
    }
    if ($type==='maxapedia_items') {$s=$db->prepare('SELECT * FROM maxapedia_gallery_photos WHERE item_id=? ORDER BY sort_order,id');$s->execute([$id]);$row['photos']=$s->fetchAll(PDO::FETCH_ASSOC);}
    if ($type==='branch_intros') $row['images']=array_map(static fn($url)=>['url'=>$url],json_decode((string)$row['images'],true)?:[]);
    return array_intersect_key($row,$definition['fields']);
}

function content_state(PDO $db,string $type,string $id): array
{
    $state=['enabled'=>['fa'],'translations'=>[],'source'=> $id!=='' ? content_source($db,$type,$id):[]];
    if ($id==='' || !content_ready($db)) return $state;
    $s=$db->prepare('SELECT locale,enabled,copy_fa,payload FROM public_content_locales WHERE content_type=? AND content_id=?');$s->execute([$type,$id]);
    foreach($s->fetchAll(PDO::FETCH_ASSOC) as $r){if($r['locale']==='fa'&&!$r['enabled'])$state['enabled']=array_values(array_diff($state['enabled'],['fa']));elseif($r['locale']!=='fa'&&$r['enabled'])$state['enabled'][]=$r['locale'];if($r['locale']!=='fa')$state['translations'][$r['locale']]=['copy'=>(bool)$r['copy_fa'],'fields'=>json_decode($r['payload'],true,512,JSON_THROW_ON_ERROR)];}
    return $state;
}

function content_delete(PDO $db,string $type,string $id): void
{
    content_type($type);if(!content_ready($db))return;
    $db->prepare('DELETE FROM public_content_locales WHERE content_type=? AND content_id=?')->execute([$type,$id]);
}

function content_access(PDO $db,string $type,string $id): void
{
    $definition=content_type($type);
    $permission=$definition['permission'];
    if ($permission==='super') { if(!dash_is_super()) throw new RuntimeException('دسترسی غیرمجاز.'); }
    elseif($permission==='maxapedia') {if(!dash_can_maxapedia()) throw new RuntimeException('دسترسی غیرمجاز.');}
    elseif($permission==='stories') {if((!dash_is_hq_view()&&!dash_is_super())||dash_is_finance_only()||dash_is_finance_user())throw new RuntimeException('دسترسی غیرمجاز.');}
    elseif($permission==='stands') {if(!dash_is_super())dash_require('stands');}
    elseif($permission==='branch'){if(dash_is_hq_view())throw new RuntimeException('این بخش مخصوص شعب است.');}
    else dash_require($permission);
    if(!empty($definition['hq']))dash_require_hq();
    if(!empty($definition['remote'])&&$id!==''){
        require_once __DIR__.'/campaign-service.php';$branch=content_campaign_branch($db,(string)($_GET['branch']??''));$found=false;
        foreach((new CampaignServiceClient($branch['slug']))->managementCampaigns() as $campaign)if((string)($campaign['id']??'')===$id){$found=true;break;}
        if(!$found)throw new RuntimeException('کمپین در این مرکز یافت نشد.');
    }
    if($id!==''&&!empty($definition['branch'])){
        $key=$definition['key']??'id';$s=$db->prepare("SELECT branch_id FROM `$type` WHERE `$key`=?");$s->execute([$id]);$branch=$s->fetchColumn();
        if($branch===false||(!dash_is_hq_view()&&(int)$branch!==dash_active_branch_id()))throw new RuntimeException('محتوا در دسترس شما نیست.');
    }
}

function content_campaign_branch(PDO $db,string $slug=''): array
{
    $branch=dash_load_branch(dash_active_branch_id());
    if($slug!==''&&$slug!==($branch['slug']??'')){
        if(!dash_is_super()||!dash_is_hq_view())throw new RuntimeException('دسترسی به این مرکز مجاز نیست.');
        $s=$db->prepare("SELECT * FROM branches WHERE slug=? AND status='active'");$s->execute([$slug]);$branch=$s->fetch();
    }
    if(!$branch)throw new RuntimeException('مرکز فعال یافت نشد.');return $branch;
}

/** Persist a validated editorial snapshot; absence of the envelope preserves older clients. */
function content_save(PDO $db,string $type,string $id,?string $raw=null,?array $source=null): void
{
    $raw ??= is_string($_POST['content_languages']??null)?$_POST['content_languages']:null;
    if($raw===null)return;
    $data=json_decode($raw,true,64,JSON_THROW_ON_ERROR);
    $enabled=$data['enabled']??[];
    if(!is_array($enabled)||!$enabled||array_diff($enabled,['fa','en','ar']))throw new InvalidArgumentException('حداقل یک زبان معتبر برای انتشار انتخاب کنید.');
    $definition=content_type($type);$source ??= content_source($db,$type,$id);
    $rows=[];
    foreach(['fa','ar','en'] as $locale){
        $translation=$data['translations'][$locale]??[];
        $copy=!empty($translation['copy']);
        $fields=$copy?$source:($translation['fields']??[]);
        if($locale!=='fa'){
            $fields=content_clean_fields($definition['fields'],is_array($fields)?$fields:[],$source,$type,$locale);
            if($type==='components')$fields['content']=preg_replace_callback('/\{\{image([1-9][0-9]*)\}\}/',static fn($match)=>(string)($fields['images'][(int)$match[1]-1]['url']??''),$fields['content']);
            if($type==='courses')foreach($fields['curriculum'] as $si=>&$section){$section['id']=$source['curriculum'][$si]['id']??($si+1);foreach($section['lessons'] as $li=>&$lesson){$lesson['id']=$source['curriculum'][$si]['lessons'][$li]['id']??(1000000000+($si*1000)+$li);$lesson['is_preview']=(int)($source['curriculum'][$si]['lessons'][$li]['is_preview']??0);}unset($lesson);}unset($section);
            if(in_array($locale,$enabled,true)){
                $titleKey=isset($definition['fields']['title'])?'title':(isset($definition['fields']['fullname'])?'fullname':(isset($definition['fields']['name'])?'name':null));
                if($titleKey&&trim((string)($fields[$titleKey]??''))==='')throw new InvalidArgumentException('عنوان / نام را در تب '.($locale==='en'?'انگلیسی':'عربی').' وارد کنید.');
            }
        }
        $rows[]=[$type,$id,$locale,(int)in_array($locale,$enabled,true),(int)$copy,json_encode($locale==='fa'?[]:$fields,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)];
    }
    $ownTransaction=!$db->inTransaction();if($ownTransaction)$db->beginTransaction();
    try{$s=$db->prepare('INSERT INTO public_content_locales(content_type,content_id,locale,enabled,copy_fa,payload) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE enabled=VALUES(enabled),copy_fa=VALUES(copy_fa),payload=VALUES(payload)');foreach($rows as $row)$s->execute($row);if($ownTransaction)$db->commit();}
    catch(Throwable $e){if($ownTransaction&&$db->inTransaction())$db->rollBack();throw $e;}
}

function content_clean_fields(array $definitions,array $fields,array $source,string $type,string $locale,bool $deferFa=false): array
{
    $clean=[];
    foreach($definitions as $key=>$field){
        if($type==='courses'&&$key==='content'&&isset($fields['type'])){
            $accept=['video'=>'video/*','image'=>'image/*','pdf'=>'application/pdf'];
            $field=array_merge($field,isset($accept[$fields['type']])?['type'=>'asset','accept'=>$accept[$fields['type']]]:['type'=>'textarea']);
        }
        $value=$fields[$key]??($field['type']==='list'?[]:'');
        if($field['type']==='list'){
            if(!is_array($value)||count($value)>200)throw new InvalidArgumentException('تعداد موارد محتوا معتبر نیست.');
            $clean[$key]=[];foreach($value as $index=>$child)$clean[$key][]=content_clean_fields($field['fields'],is_array($child)?$child:[],is_array($source[$key][$index]??null)?$source[$key][$index]:[],$type,$locale,$deferFa);
        }elseif($field['type']==='asset'){
            if(is_array($value)&&isset($value['source_fa'])){if($deferFa){$clean[$key]=['source_fa'=>true];continue;}$value=$source[$key]??'';}
            if(is_array($value)&&isset($value['upload']))$value=content_upload((string)$value['upload'],$field,$type,$locale);
            if(!is_string($value))throw new InvalidArgumentException('نشانی فایل معتبر نیست.');
            if($type==='courses'&&$key==='content'&&preg_match('~<iframe\b[^>]*\bsrc=["\']([^"\']+)["\']~i',$value,$embed))$value=html_entity_decode($embed[1],ENT_QUOTES,'UTF-8');
            $value=trim($value);
            if(preg_match('~["\'<>\\\\\x00-\x20]~u',$value))throw new InvalidArgumentException('نشانی فایل معتبر نیست.');
            if(str_starts_with($value,'/')&&preg_match('~(?:^|/)\.\.(?:/|$)~',rawurldecode(explode('?',$value,2)[0])))throw new InvalidArgumentException('مسیر فایل معتبر نیست.');
            if($value!==''&&!preg_match('~^(?:/(?!/)|https?://)~i',$value))throw new InvalidArgumentException('نشانی فایل باید داخلی یا HTTPS باشد.');
            $clean[$key]=$deferFa?$value:content_snapshot_asset($value,$type,$locale);
        }elseif($field['type']==='checkbox')$clean[$key]=!empty($value)?1:0;
        elseif($field['type']==='number')$clean[$key]=max(0,(int)$value);
        elseif($field['type']==='select'){
            if(!is_scalar($value))throw new InvalidArgumentException('گزینهٔ انتخاب‌شده معتبر نیست.');
            $value=(string)$value;if($value==='')$value=(string)array_key_first($field['options']);
            if(!array_key_exists($value,$field['options']))throw new InvalidArgumentException('گزینهٔ انتخاب‌شده معتبر نیست.');
            $clean[$key]=$value;
        }
        else{
            if(!is_scalar($value)&&$value!==null)throw new InvalidArgumentException('مقدار فیلد معتبر نیست.');
            $value=(string)$value;if(strlen($value)>3000000)throw new InvalidArgumentException('حجم محتوا بیش از حد مجاز است.');
            $clean[$key]=$field['type']==='html'?($type==='components'?HtmlSanitizer::sanitizeComponent($value):HtmlSanitizer::sanitize($value)):trim(strip_tags($value));
            if(in_array($key,['button_link','banner_link','registration_url','instagram','linkedin'],true)&&!HtmlSanitizer::isSafeUrl($clean[$key]))throw new InvalidArgumentException('لینک واردشده معتبر نیست.');
            if($field['type']==='html'&&!$deferFa)$clean[$key]=preg_replace_callback('~\b(src|href)=(["\'])(/(?:uploads|dashboard/components)/[^"\']+\.(?:jpe?g|png|gif|webp|pdf|mp3|m4a|ogg|mp4|webm))\2~i',static fn($match)=>$match[1].'='.$match[2].htmlspecialchars(content_snapshot_asset(html_entity_decode($match[3],ENT_QUOTES,'UTF-8'),$type,$locale),ENT_QUOTES,'UTF-8').$match[2],$clean[$key]);
        }
    }
    return $clean;
}

/** Validate every selected language and upload before changing the original record. */
function content_prepare(PDO $db,string $type): void
{
    if(!isset($_POST['content_languages']))return;
    csrf_check();content_install($db);
    if(!is_string($_POST['content_languages']))throw new InvalidArgumentException('اطلاعات زبان‌ها معتبر نیست.');
    $data=json_decode($_POST['content_languages'],true,64,JSON_THROW_ON_ERROR);
    if(!is_array($data)||!is_array($data['enabled']??null)||!$data['enabled']||array_diff($data['enabled'],['fa','en','ar']))throw new InvalidArgumentException('حداقل یک زبان معتبر برای انتشار انتخاب کنید.');
    $definition=content_type($type);
    foreach(['ar','en'] as $locale){
        $t=$data['translations'][$locale]??['copy'=>false,'fields'=>[]];
        if(!is_array($t)||!is_array($t['fields']??[]))throw new InvalidArgumentException('ساختار تب زبان معتبر نیست.');
        if(empty($t['copy'])){
            $t['fields']=content_clean_fields($definition['fields'],$t['fields']??[],[],$type,$locale,true);
            $key=isset($definition['fields']['title'])?'title':(isset($definition['fields']['fullname'])?'fullname':(isset($definition['fields']['name'])?'name':null));
            if(in_array($locale,$data['enabled'],true)&&$key&&trim((string)($t['fields'][$key]??''))==='')throw new InvalidArgumentException('عنوان / نام را در تب '.($locale==='en'?'انگلیسی':'عربی').' وارد کنید.');
        }
        $data['translations'][$locale]=$t;
    }
    $_POST['content_languages']=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
}

/** A copy is an independent file snapshot; replacing the Persian file cannot overwrite it. */
function content_snapshot_asset(string $url,string $type,string $locale): string
{
    if((!str_starts_with($url,'/uploads/')&&!str_starts_with($url,'/dashboard/components/'))||str_starts_with($url,'/uploads/content-locales/'.$type.'/'.$locale.'/'))return $url;
    $root=realpath(dirname(__DIR__).(str_starts_with($url,'/uploads/')?'/uploads':'/dashboard/components'));$file=realpath(dirname(__DIR__).$url);
    if(!$root||!$file||!str_starts_with($file,$root.DIRECTORY_SEPARATOR)||!is_file($file))return $url;
    $extension=strtolower(pathinfo($file,PATHINFO_EXTENSION));
    if(!in_array($extension,['jpg','jpeg','png','gif','webp','bmp','pdf','epub','mobi','doc','docx','ppt','pptx','xls','xlsx','zip','rar','7z','mp3','m4a','ogg','mp4','webm'],true))throw new InvalidArgumentException('فایل قابل کپی نیست.');
    $directory='/uploads/content-locales/'.$type.'/'.$locale.'/';$target=dirname(__DIR__).$directory;
    if(!is_dir($target)&&!mkdir($target,0755,true)&&!is_dir($target))throw new RuntimeException('پوشهٔ فایل زبان ایجاد نشد.');
    $name=bin2hex(random_bytes(16)).'.'.$extension;
    if(!copy($file,$target.$name))throw new RuntimeException('کپی فایل زبان انجام نشد.');
    return $directory.$name;
}

function content_upload(string $token,array $field,string $type,string $locale): string
{
    if(!preg_match('/^[a-zA-Z0-9_-]{1,100}$/D',$token))throw new InvalidArgumentException('شناسهٔ فایل نامعتبر است.');
    $files=$_FILES['content_uploads']??[];$file=[];
    foreach(['name','type','tmp_name','error','size'] as $key)$file[$key]=$files[$key][$token]??null;
    if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||!is_uploaded_file((string)$file['tmp_name']))throw new InvalidArgumentException('بارگذاری فایل زبان انتخاب‌شده کامل نشد.');
    $size=(int)filesize($file['tmp_name']);if($size<1||$size>30*1024*1024)throw new InvalidArgumentException('حداکثر اندازهٔ فایل ۳۰ مگابایت است.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif','image/bmp'=>'bmp','application/pdf'=>'pdf','video/mp4'=>'mp4','video/webm'=>'webm','audio/mpeg'=>'mp3','audio/ogg'=>'ogg','audio/mp4'=>'m4a',
        'application/epub+zip'=>'epub','application/x-mobipocket-ebook'=>'mobi','application/msword'=>'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx','application/vnd.ms-powerpoint'=>'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'pptx','application/vnd.ms-excel'=>'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xlsx','application/zip'=>'zip','application/x-zip-compressed'=>'zip',
        'application/vnd.rar'=>'rar','application/x-rar'=>'rar','application/x-7z-compressed'=>'7z'];
    $accept=$field['accept']??'image/*';$allowed=false;
    foreach(explode(',',$accept) as $pattern)if($mime===$pattern||(str_ends_with($pattern,'/*')&&str_starts_with($mime,substr($pattern,0,-1))))$allowed=true;
    if(!$allowed||!isset($extensions[$mime]))throw new InvalidArgumentException('نوع فایل برای این فیلد مجاز نیست.');
    $directory='/uploads/content-locales/'.$type.'/'.$locale.'/';$root=dirname(__DIR__).$directory;
    if(!is_dir($root)&&!mkdir($root,0755,true)&&!is_dir($root))throw new RuntimeException('پوشهٔ بارگذاری ایجاد نشد.');
    $extension=$extensions[$mime];$originalExtension=strtolower(pathinfo((string)$file['name'],PATHINFO_EXTENSION));
    if(in_array($mime,['application/zip','application/x-zip-compressed'],true)&&in_array($originalExtension,['epub','docx','pptx','xlsx'],true))$extension=$originalExtension;
    $name=bin2hex(random_bytes(16)).'.'.$extension;
    if(str_starts_with($mime,'image/')){
        if(!@getimagesize($file['tmp_name']))throw new InvalidArgumentException('تصویر معتبر نیست.');
        require_once __DIR__.'/image-optimizer.php';
        if(maxa_optimize_image_to_webp($file['tmp_name'],$root.$name.'.webp',82,1920))return $directory.$name.'.webp';
    }
    if(!move_uploaded_file($file['tmp_name'],$root.$name))throw new RuntimeException('ذخیرهٔ فایل انجام نشد.');
    return $directory.$name;
}

/** A shared in-page editor; each existing form keeps its own Persian editor and save workflow. */
function content_editor(string $type,string $id,string $selector,array $options=[]): void
{
    $db=content_pdo();content_install($db);
    $config=['type'=>$type,'id'=>$id,'selector'=>$selector,'fields'=>content_type($type)['fields'],'state'=>content_state($db,$type,$id),'csrf'=>csrf_token()]+$options;
    if(isset($options['source']))$config['state']['source']=array_intersect_key($options['source'],content_type($type)['fields']);
    echo '<script type="application/json" class="content-locale-config">'.json_encode($config,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR).'</script>';
    static $assets=false;if(!$assets){$assets=true;echo '<link rel="stylesheet" href="/assets/content-editor.css?v='.filemtime(dirname(__DIR__).'/assets/content-editor.css').'"><script src="/assets/content-editor-layout.js?v='.filemtime(dirname(__DIR__).'/assets/content-editor-layout.js').'" defer></script><script src="/assets/content-editor.js?v='.filemtime(dirname(__DIR__).'/assets/content-editor.js').'" defer></script>';}
}
