<?php
/*
* Name:        应用商店
* Description:        向后台添加一个应用商店，支持在线下载主题和插件
* Version:            1.0
* Author:             风屿Wind
*/

define('APP_STORE_DIR', __DIR__);
include APP_STORE_DIR . '/inc/wind_share_lib/func.php';

add_menu('admin_sidebar', '设置', '#settings');
add_menu('admin_sidebar', '应用商店', '#app_store');
add_submenu('admin_sidebar', '#app_store', '应用商店',get_Url('admin/app_store'));
add_submenu('admin_sidebar', '#app_store', '授权信息配置',get_Url('admin/auth_config'));

function appstore_add_page_router($page_router)
{
    $page_router['admin']['app_store'] = APP_STORE_DIR . '/pages/app_store.php';
    $page_router['admin']['auth_config'] = APP_STORE_DIR . '/pages/auth_config.php';

    return $page_router;
}

add_filter('page_router', 'appstore_add_page_router');

