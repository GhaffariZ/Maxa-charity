<?php
declare(strict_types=1);
require_once __DIR__.'/_guard.php';dash_require('events');dash_require_hq();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('روش درخواست مجاز نیست.');}
csrf_check();$id=(int)($_POST['id']??0);
if($id){
    $pdo->beginTransaction();
    try{
        $s=$pdo->prepare('SELECT id FROM event_news WHERE event_id=?');$s->execute([$id]);foreach($s->fetchAll(PDO::FETCH_COLUMN) as $newsId)content_delete($pdo,'event_news',(string)$newsId);
        $pdo->prepare('DELETE FROM events WHERE id=?')->execute([$id]);content_delete($pdo,'events',(string)$id);$pdo->commit();
    }catch(Throwable $e){$pdo->rollBack();throw $e;}
}
header('Location: event-list.php');
