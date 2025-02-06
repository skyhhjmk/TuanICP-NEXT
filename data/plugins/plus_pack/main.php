<?php
/*
 * Name:               Plus扩展包
 * Description:        此插件将为TuanICP提供额外的高级功能，绝对物超所值
 * Version:            1.0
 * Author:             风屿Wind
 * Dependencies:       应用商店
*/
function initPlusPack()
{
    define('PLUS_PACK_DIR', __DIR__);

    if (file_exists(TUANICP_PLUGIN_DIR . '/app_store/inc/wind_share_lib/func.php')) {
        include TUANICP_PLUGIN_DIR . '/app_store/inc/wind_share_lib/func.php';
    } else {
        return;
    }
    add_submenu('admin_sidebar', 'javascript:', '站点设置', get_Url('admin/settings'));
    add_submenu('admin_sidebar', 'javascript:', '用户管理', get_Url('admin/users'));
    add_menu('admin_sidebar', '主题',get_Url('admin/settings'));


}

add_action('load_plugin', 'initPlusPack');

// 注册一个自定义过滤器 'page_router' 用于添加新的路由
function register_plus_pack_routes($routes)
{
    // 添加新的路由条目
    $new_routes = [
        'join' => PLUS_PACK_DIR . '/pages/join.php',
        'admin' => [
            'users' => PLUS_PACK_DIR . 'pages/admin/users.php',
            'stats' => PLUS_PACK_DIR . 'pages/admin/stats.php',
            'settings' => PLUS_PACK_DIR . 'pages/admin/settings.php',
            'all_icp' => PLUS_PACK_DIR . 'pages/admin/all_icp.php',
            'audit' => PLUS_PACK_DIR . 'pages/admin/audit.php',
            'audit_log' => PLUS_PACK_DIR . 'pages/admin/audit_log.php',
            'auto_audit' => PLUS_PACK_DIR . 'pages/admin/auto_audit.php',
            'plugins' => PLUS_PACK_DIR . 'pages/admin/plugins.php',
            'themes' => PLUS_PACK_DIR . 'pages/admin/themes.php',
            'logs' => PLUS_PACK_DIR . 'pages/admin/logs.php',
        ],
    ];

    // 合并新的路由到原始路由数组中
    return array_replace_recursive($routes, $new_routes);
}

// 使用 'page_router' 过滤器钩子添加自定义路由
add_filter('page_router', 'register_plus_pack_routes');