<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

function initTwig(): Environment
{
    define('TEMPLATE_NAME', get_Template_name());
    $loader = new FilesystemLoader(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME); // 设置模板位置
    $loader->addPath(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME, 'index');
    $loader->addPath(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME . '/admin', 'admin');
    $twig = new Environment($loader, [
        'cache' => APP_ROOT . '/cache', // 设置缓存目录
        'debug' => DEBUG, // 开启调试模式
        'auto_reload' => true, // 当模板文件发生变化时自动重新加载
        'strict_variables' => false, // 当变量不存在时抛出异常
    ]);
    // 添加菜单和子菜单
    add_menu('admin_sidebar', '概览', get_Url('admin'));
    add_menu('admin_sidebar', '插件', 'javascript:');
    add_submenu('admin_sidebar', 'javascript:', '全部插件', get_Url('admin/plugin'));
    return $twig;
}
