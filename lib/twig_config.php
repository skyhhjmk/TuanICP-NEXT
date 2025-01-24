<?php

function initTwig(): \Twig\Environment
{
    define('TEMPLATE_NAME',get_Template_name());
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();
    $dotenv->required('DEBUG');
    $DEBUG = $_ENV['DEBUG'] ?? false;
    $loader = new \Twig\Loader\FilesystemLoader(APP_ROOT . '/templates/' . TEMPLATE_NAME, '.html.twig'); // 设置模板位置
    $twig = new \Twig\Environment($loader, [
        'cache' => APP_ROOT . '/cache', // 设置缓存目录
        'debug' => $DEBUG, // 开启调试模式
        'auto_reload' => true, // 当模板文件发生变化时自动重新加载
        'strict_variables' => false, // 当变量不存在时抛出异常
    ]);
    return $twig;
}
