<?php

function initTwig(): \Twig\Environment
{
    define('TEMPLATE_NAME', get_Template_name());
    $loader = new \Twig\Loader\FilesystemLoader(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME); // 设置模板位置
    $loader->addPath(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME, 'index');
    $loader->addPath(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME, 'admin');
    $twig = new \Twig\Environment($loader, [
        'cache' => APP_ROOT . '/cache', // 设置缓存目录
        'debug' => DEBUG, // 开启调试模式
        'auto_reload' => true, // 当模板文件发生变化时自动重新加载
        'strict_variables' => false, // 当变量不存在时抛出异常
    ]);
    return $twig;
}
