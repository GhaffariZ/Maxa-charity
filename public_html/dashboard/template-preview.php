<?php
$data = json_decode(file_get_contents("php://input"), true);

foreach ($data['components'] as $c) {
    $file = __DIR__ . "/components/$c/component.php";
    if (file_exists($file)) {
        include $file;
    }
}
