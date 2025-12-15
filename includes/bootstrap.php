<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__));
}

if (!defined('DATA_DIR')) {
    define('DATA_DIR', ROOT_DIR . '/data');
}

if (!defined('UPLOADS_DIR')) {
    define('UPLOADS_DIR', ROOT_DIR . '/uploads');
}
?>
