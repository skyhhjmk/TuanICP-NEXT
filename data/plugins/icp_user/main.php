<?php
/*
 * Name:        基于用户的ICP规则
 * Description:        每个用户仅能拥有一个ICP备案号，多个站点备案将添加“-1”、“-2”等后缀
 * Version:            1.0
 * Author:             风屿Wind
 * Conflicts: 基于主域名的ICP规则, 基于子域名的ICP规则, 经典ICP规则
*/

if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
define('ICP_USER_DIR', __DIR__);

function icp_user_page_router($routes)
{

    $newRoutes = [
        'reg' => ICP_USER_DIR . '/pages/reg.php'
    ];

    $routes = array_merge($routes, $newRoutes); // 合并两个数组

    return $routes;
}

add_filter('page_router', 'icp_user_page_router');