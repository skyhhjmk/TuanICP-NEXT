<?php

define('INSTALL_ROOT', __DIR__);

if(file_exists(INSTALL_ROOT . '/../.env')){
    header('Location: /');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>安装</title>
    <style>
    </style>
</head>
<body>
<h1>安装</h1>
<p>欢迎使用 TuanICP 安装向导</p>
<button>
    <a href="./step1.php">开始安装</a>
</button>
</body>
</html>
