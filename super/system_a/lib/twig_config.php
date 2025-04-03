<?php


use Twig\Environment;
use Twig\Loader\FilesystemLoader;

function initTwig(): Environment
{
    define('TEMPLATE_NAME', get_Template_name());
    $loader = new FilesystemLoader(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME); // 设置模板位置
    $loader->addPath(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME, 'index');
    $loader->addPath(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME . '/admin', 'admin');
    $loader->addPath(TUANICP_PLUGIN_DIR . '/', 'plugin_root');
    $twig = new Environment($loader, [
        'cache' => DATA_ROOT . '/cache', // 设置缓存目录
        'debug' => DEBUG, // 开启调试模式
        'auto_reload' => true, // 当模板文件发生变化时自动重新加载
        'strict_variables' => false, // 当变量不存在时抛出异常
    ]);
    return $twig;
}
