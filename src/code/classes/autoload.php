<?php

// 클래스 오토로더
require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
spl_autoload_register(function ($class) {
    include __DIR__ . '/' . str_replace("\\", "/", $class) . '.php';
});

?>
