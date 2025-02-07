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
    if (!icp_auth()){
        exit('您已经安装了Plus扩展包，但是授权失败！Plus扩展包是一个收费功能，请购买授权并且配置授权用户信息，否则请删除Plus扩展包以正常使用。');
    }
    define('PLUS_PACK_DIR', __DIR__);

    if (file_exists(TUANICP_PLUGIN_DIR . '/app_store/inc/wind_share_lib/func.php')) {
        include TUANICP_PLUGIN_DIR . '/app_store/inc/wind_share_lib/func.php';
    } else {
        return;
    }
    add_submenu('admin_sidebar', 'javascript:', '站点设置', get_Url('admin/settings'));
    add_submenu('admin_sidebar', 'javascript:', '用户管理', get_Url('admin/users'));
    add_menu('admin_sidebar', '主题',get_Url('admin/settings'));
    add_menu('admin_sidebar', '备案管理','javascript:');
    add_submenu('admin_sidebar', 'javascript:', '全部备案', get_Url('admin/all_icp'));
    add_submenu('admin_sidebar', 'javascript:', '审核管理', get_Url('admin/audit'));


}

add_action('load_plugin', 'initPlusPack');

// 注册一个自定义过滤器 'page_router' 用于添加新的路由
function register_plus_pack_routes($routes)
{
    // 添加新的路由条目
    $new_routes = [
        'join' => PLUS_PACK_DIR . '/pages/join.php',
    ];

    $routes = array_merge($routes, $new_routes);

    $routes['admin']['users'] = PLUS_PACK_DIR . '/pages/admin/users.php';
    $routes['admin']['stats'] = PLUS_PACK_DIR . '/pages/admin/stats.php';
    $routes['admin']['settings'] = PLUS_PACK_DIR . '/pages/admin/settings.php';
    $routes['admin']['all_icp'] = PLUS_PACK_DIR . '/pages/admin/all_icp.php';
    $routes['admin']['audit'] = PLUS_PACK_DIR. '/pages/admin/audit.php';
    $routes['admin']['audit_log'] = PLUS_PACK_DIR. '/pages/admin/audit_log.php';
    $routes['admin']['auto_audit'] = PLUS_PACK_DIR. '/pages/admin/auto_audit.php';
    $routes['admin']['themes'] = PLUS_PACK_DIR. '/pages/admin/themes.php';
    $routes['admin']['logs'] = PLUS_PACK_DIR. '/pages/admin/logs.php';

    return $routes;
}

// 使用 'page_router' 过滤器钩子添加自定义路由
add_filter('page_router', 'register_plus_pack_routes');

add_menu('footer', '加入我们',get_Url('join'));