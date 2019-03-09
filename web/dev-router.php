<?php
if (php_sapi_name() == 'cli-server') {
    if (is_file(__DIR__ . '/' . $_SERVER['REQUEST_URI'])) {
       return false;
    }
}

require_once __DIR__ . '/index.php';
