<?php
declare(strict_types=1);
require_once __DIR__.'/core/content-locales.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
try {
    $db=content_pdo();
    $active=$db->query("SHOW COLUMNS FROM employee_profiles LIKE 'is_active'")->fetch()?'e.is_active=1 AND ':'';
    $scope=content_where($db,'employee_profiles','e.id');
    $employees=$db->query("SELECT e.id,e.fullname,e.profile_pic,e.branch,e.branch_id,e.role,e.job_category,b.slug AS branch_slug
        FROM employee_profiles e LEFT JOIN branches b ON b.id=e.branch_id WHERE $active $scope ORDER BY e.id DESC")->fetchAll();
    echo json_encode(content_rows($db,'employee_profiles',$employees),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch(Throwable $error) {
    error_log('Public partners: '.$error->getMessage());
    http_response_code(500);
    echo json_encode(['error'=>'Unable to load partners.']);
}
