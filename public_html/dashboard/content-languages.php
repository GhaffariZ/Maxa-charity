<?php
declare(strict_types=1);
require_once __DIR__.'/_guard.php';
require_once __DIR__.'/../core/content-locales.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    if($_SERVER['REQUEST_METHOD']!=='GET'){http_response_code(405);throw new RuntimeException('درخواست مجاز نیست.');}
    $type=(string)($_GET['type']??'');$id=(string)($_GET['id']??'');
    if(strlen($id)>190)throw new InvalidArgumentException('شناسه نامعتبر است.');
    content_access($pdo,$type,$id);content_install($pdo);
    echo json_encode(['state'=>content_state($pdo,$type,$id)],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
}catch(Throwable $e){if(http_response_code()<400)http_response_code(403);echo json_encode(['message'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
