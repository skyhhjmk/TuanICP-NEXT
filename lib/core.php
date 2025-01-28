<?php
require_once 'func/function.php';
require_once 'func/action.php';
require_once 'func/plugin.php';
require_once 'func/menu.php';
require_once 'func/sendmail.php';

// 添加菜单和子菜单
add_menu('admin_sidebar', '概览', get_Url('admin'));

add_menu('admin_sidebar', '插件', 'javascript:');
add_submenu('admin_sidebar', 'javascript:', '全部插件', get_Url('admin/plugin'));

if (file_exists(APP_ROOT . '/.env')) {
    load_plugins(); // 加载插件
}
