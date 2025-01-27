<?php

define('INSTALL_ROOT', __DIR__);

if(file_exists(INSTALL_ROOT . '/../.env')){
    header('Location: /');
    exit;
}

include INSTALL_ROOT . '/../vendor/autoload.php'; // 加载第三方库

$install_part = $_GET['part'] ?? 'step1';

if ($install_part == 'step1') {
    include INSTALL_ROOT . '/inc/step1.php';
} elseif ($install_part == 'step2') {
    include INSTALL_ROOT . '/inc/step2.php';
}