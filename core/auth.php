<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function auth() {
    if (!isset($_SESSION['user'])) {
        header("Location: /admin/login.php");
        exit;
    }
}

function user() {
    return $_SESSION['user'] ?? null;
}

function can($minLevel) {
    return isset($_SESSION['user']['role_level']) &&
           $_SESSION['user']['role_level'] >= $minLevel;
}

function forbid() {
    http_response_code(403);
    echo "403 | Access Denied";
    exit;
}
