<?php
/*
* Name:        Plus扩展包
* Description:        此插件将为TuanICP提供额外的高级功能，绝对物超所值
* Version:            1.0
* Author:             风屿Wind
*/
// 注册一个自定义过滤器 'page_router' 用于添加新的路由
function register_plus_pack_routes($routes)
{
    // 添加新的路由条目
    $new_routes = [
        'about' => 'about.php', // 新的关于页面
        'admin' => [
            'users' => 'pages/admin/users.php', // 新的用户管理页面
            'stats' => 'pages/admin/stats.php', // 新的统计页面
            'settings' => 'pages/admin/settings.php',
            'all_icp' => 'pages/admin/all_icp.php',
            'audit' => 'pages/admin/audit.php',
            'audit_log' => 'pages/admin/audit_log.php',
            'auto_audit' => 'pages/admin/auto_audit.php',
        ],
    ];

    // 合并新的路由到原始路由数组中
    return array_replace_recursive($routes, $new_routes);
}

// 使用 'page_router' 过滤器钩子添加自定义路由
add_filter('page_router', 'register_plus_pack_routes');