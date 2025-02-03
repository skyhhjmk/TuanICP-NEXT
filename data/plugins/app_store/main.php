<?php
/*
* Name:        应用商店
* Description:        向后台添加一个应用商店，支持在线下载主题和插件
* Version:            1.0
* Author:             风屿Wind
*/

define('APP_STORE_DIR', __DIR__);
add_menu('admin_sidebar', '应用商店', get_Url('admin/app_store'));

function appstore_add_page_router($page_router)
{
    $page_router['admin']['app_store'] = APP_STORE_DIR . '/pages/app_store.php';

    return $page_router;
}
add_filter('page_router', 'appstore_add_page_router');

