<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/core/auth.php';

function require_role($level) {
    auth();

    if (!can($level)) {
        forbid();
    }
}
