<?php



if (file_exists('install.lock')){
    header('Location: /');
    exit();
}