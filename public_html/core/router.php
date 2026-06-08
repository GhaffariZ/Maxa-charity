<?php

require_once __DIR__.'/database.php';
require_once __DIR__.'/functions.php';

$db = db();

$route = $_GET['route'] ?? 'home';

$route = trim($route,'/');

$segments = explode('/',$route);

$slug = $segments[0];
$subslug = $segments[1] ?? null;


/*
PAGE ROUTES
example:
/home
/about
/contact
*/

$stmt = $db->prepare("SELECT * FROM pages WHERE slug = ?");
$stmt->execute([$slug]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if($page){

$template = __DIR__.'/../templates/'.$page['template'].'.php';

if(file_exists($template)){
require $template;
exit;
}

}


/*
NEWS ROUTE
example:
/news/my-post
*/

if($slug == "news" && $subslug){

$stmt = $db->prepare("SELECT * FROM news WHERE slug = ?");
$stmt->execute([$subslug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if($post){
require __DIR__.'/../templates/news_single.php';
exit;
}

}


/*
BRANCH ROUTE
*/

if($slug == "branch" && $subslug){

$stmt = $db->prepare("SELECT * FROM branches WHERE slug = ?");
$stmt->execute([$subslug]);
$branch = $stmt->fetch(PDO::FETCH_ASSOC);

if($branch){
require __DIR__.'/../templates/branch_single.php';
exit;
}

}


/*
404
*/

require __DIR__.'/../templates/404.php';
