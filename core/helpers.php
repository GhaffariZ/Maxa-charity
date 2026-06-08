<?php

function db() {
    require __DIR__ . '/database.php';
    return $pdo;
}
