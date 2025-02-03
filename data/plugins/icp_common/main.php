<?php
/*
 * Name:        经典ICP规则
 * Description:        每个域名（或子域名）都有一个唯一的ICP备案号
 * Version:            1.0
 * Author:             风屿Wind
 * Conflicts: 基于主域名的ICP规则, 基于子域名的ICP规则, 基于用户的ICP规则
*/

if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
define('ICP_COMMON_DIR', __DIR__);

function icp_common_page_router($routes) {
    $newRoutes = [
        'reg' => ICP_COMMON_DIR . '/pages/reg.php',
    ];

    $routes = array_merge($routes, $newRoutes); // 合并两个数组

    return $routes;
}

// 添加过滤器
add_filter('page_router', 'icp_common_page_router');