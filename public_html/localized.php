<?php
declare(strict_types=1);

define('MACSA_PUBLIC_PATH', $_SERVER['REQUEST_URI'] ?? '/');
require_once __DIR__.'/core/site-locale.php';
$path = parse_url(MACSA_PUBLIC_PATH, PHP_URL_PATH) ?: '/';
if (!preg_match('~^/(en|ar)(?:/(.*))?$~D', $path, $match)) { http_response_code(404); exit; }
$route = trim($match[2] ?? '', '/');
$decoded = rawurldecode($route);
if (str_contains($decoded, '..') || str_contains($decoded, '\\') || str_contains($decoded, "\0")) { http_response_code(404); exit; }
// Keep legacy parsers and redirects working; MACSA_PUBLIC_PATH remains canonical.
$_SERVER['REQUEST_URI'] = '/'.$route.(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']!=='' ? '?'.$_SERVER['QUERY_STRING'] : '');
if ($route === '') $route = 'home';
$files = ['branches','contactus','news','network','personal-resume-detail','personal-resume-list','branch-intro','macsa-story','macsapedia','macsapedia-section','courses','course-details','course-checkout','course-learn','course-my','event','events','event-news','stand-order','under-construction'];
$base = preg_replace('~\.php$~', '', $route);
if (in_array($base,$files,true)) { require __DIR__.'/'.$base.'.php'; exit; }
if ($route==='contact-us') { require __DIR__.'/contactus.php'; exit; }
if (preg_match('~^courses/(checkout|my|learn/([0-9]+)|([0-9]+))$~',$route,$course)) {
    if (!empty($course[2])) { $_GET['id']=$course[2]; $file='course-learn'; }
    elseif (!empty($course[3])) { $_GET['id']=$course[3]; $file='course-details'; }
    else $file='course-'.$course[1];
    require __DIR__.'/'.$file.'.php'; exit;
}
if (preg_match('~^macsapedia/([a-zA-Z0-9_-]+)$~D',$route,$section)) { $_GET['section']=$section[1]; require __DIR__.'/macsapedia-section.php'; exit; }
if (preg_match('~^([0-9]+)(?:/[^/]+)?$~D',$decoded)) { require __DIR__.'/dashboard/news-view.php'; exit; }
if (preg_match('~^(?:dashboard|benefactor-dashboard|api|core|uploads|assets|docs)(?:/|$)~i',$decoded)) { site_error_page(); exit; }
if (str_starts_with($route,'page/')) $route=substr($route,5);
$segments=explode('/',$route,2);
$_GET['slug']=$segments[0];
$_GET['sub']=$segments[1] ?? '';
require __DIR__.'/dashboard/page-view.php';
