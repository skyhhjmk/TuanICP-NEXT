<?php
require_once 'func/function.php';
require_once 'func/cron.php';
require_once 'func/action.php';
require_once 'func/plugin.php';
require_once 'func/menu.php';
require_once 'func/sendmail.php';
require_once 'func/settings_func.php';

// 添加后台管理页面的菜单和子菜单
add_menu('admin_sidebar', '概览', get_Url('admin'));

add_menu('admin_sidebar', '插件', 'javascript:');
add_submenu('admin_sidebar', 'javascript:', '全部插件', get_Url('admin/plugin'));

// 添加前台页脚菜单
add_menu('footer', '首页', get_Url(''));
add_menu('footer', '关于', get_Url('about'));

if (file_exists(DATA_ROOT . '/.env')) {
    load_plugins(); // 加载插件文件
    // 同时执行两个初始化钩子防止钩子名写错导致无法初始化
    do_action('load_plugin');
    do_action('load_plugins');
}
