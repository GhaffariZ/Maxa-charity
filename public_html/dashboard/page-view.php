<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../config/database.php";

$slug = $_GET['slug'] ?? null;

if(!$slug){
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.html';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug=? AND status='published' LIMIT 1");
$stmt->execute([$slug]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$page){
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.html';
    exit;
}

$components = json_decode($page['components'], true);

if(!is_array($components)){
    $components = [];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($page['title']); ?></title>
</head>
<body>

<?php

foreach($components as $component){

    $componentPath = __DIR__ . "/components/".$component."/component.php";

    if(file_exists($componentPath)){

        // خواندن کد کامپوننت
        $code = file_get_contents($componentPath);

        // تبدیل {{image1}} {{image2}} ...
        $code = preg_replace_callback('/{{image(\d+)}}/', function($m) use ($component){
            return "/dashboard/components/".$component."/images/".$m[1].".png";
        }, $code);

        echo $code;

    }else{
        echo "<!-- Component not found: ".$component." -->";
    }

}

?>

</body>
</html>