<?php
// 检查 PHP 版本
$required_php_version = '8.1';
if (version_compare(PHP_VERSION, $required_php_version, '<')) {
    echo "PHP 版本过低，请升级到 PHP $required_php_version 或更高版本。当前版本: " . PHP_VERSION . "当然，你也可以选择强制继续，因为分析得出的最低兼容版本是7.2（勉强运行）";
    exit;
}

// 检查所需插件
$required_extensions = ['pdo', 'pdo_mysql', 'curl'];
foreach ($required_extensions as $extension) {
    if (!extension_loaded($extension)) {
        echo "缺少必要的 PHP 扩展: $extension";
        exit;
    }
}

// 检查文件权限
$application_directory = '../..'; // 假设应用目录在上两级
if (!is_readable($application_directory) || !is_writable($application_directory)) {
    echo "应用目录 ($application_directory) 没有读写权限，请检查权限设置。";
    exit;
}
