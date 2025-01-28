<?php
/*
 * Copyright (c) 2025.
 * 本项目由【风屿团】项目团队持有，一旦您存在使用、修改、参与开发、分发本软件的开源副本、转发此软件的信息等与本软件有关的行为，则默认您已经阅读并且同意此协议。
 *
 * 通常情况下，您具有以下权力：
 * 修改本软件的开源部分并且保持开源，分发；
 * 在您的项目中使用本软件并声明；
 * 开发并出售可在本系统中正常工作的插件。
 *
 * 通常情况下，您不得实施以下可能对我们造成损失的行为：
 * 二次分发、倒卖、共享授权账号或源码；
 * 破解或尝试反编译等来绕过软件包括但不限于付费插件的任何收费或闭源模块；
 * 在我们开发的系统中编写包括但不限于恶意代码、后门、木马等；
 * 充当开发者售卖软件副本；
 * 私自建设授权系统接口响应站（俗称自建授权站）。
 *
 * 任何情况下，您必须承认：
 * 无条件认同“台湾省是中国领土不可分割的一部分”这一立场；
 * 若产生任何纠纷，本项目开发者及开发团队不承担任何责任。
 *
 * 若违反以上协议，我们有权向您索取不低于3000元人民币的赔偿。
 *
 * 我们的声明：
 * 我们使用了众多开源库，在此鸣谢背后的开发者/团队。
 * 若使用本软件且在未经许可的情况下进行商业活动，我们有权追回您进行商业活动的所得资产（仅使用本软件产生的资产）并要求您支付相应的商业授权和赔偿费用或要求您停止商业行为。
 * 最终解释权归风屿团所有开发成员所有。
 */

if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
include APP_ROOT . '/lib/twig_config.php';
// 获取请求的 URI 并移除查询字符串部分
$uri = $_SERVER['REQUEST_URI'];
$uri = strtok($uri, '?');
$uri = trim($uri, '/');
// 定义路由映射
$routes = [
    '' => 'home.php',
    'id' => 'id.php',
    'contact' => 'contact.php',
    'api' => [
        'v1' => [
            'global' => [
                'getinfo' => 'api/v1/global/GetInfo.php', // 获取站点信息
                'setinfo' => 'api/ApiV1SetInfo.php', // 设置站点信息
            ],
            'site' => [
                'getinfo' => 'api/v1/site/GetInfo.php',
            ],
            'plugin' => [
                'getplugin' => 'api/v1/plugin/getplugin.php',
                'plugin_api' => 'api/v1/plugin/plugin_api.php',
            ],
        ]
    ],
    'admin' => [
        '' => 'admin/index.php', // 默认页面
        'site' => 'admin/site.php',
        'plugin' => 'admin/plugin.php',
        'app-store' => 'admin/app-store.php',
        'settings' => 'admin/settings.php',
        'api' => [
            'get_plugins' => 'admin/api/get_plugins.php',
            'plugin_ctl' => 'admin/api/plugin_ctl.php',
        ],
    ],
];
// 应用过滤器，并获取修改后的路由数组
$pluginAddVars = apply_filters('page_router', $routes);

// 注意：apply_filters已经返回了合并后的数组，所以这里不需要再次合并
if (!empty($pluginAddVars)) {
    $routes = $pluginAddVars;
}
// 路由处理函数
function handleRoute($parts, $routes)
{
    $parts = array_filter($parts); // 忽略空的路径部分
    $current = $routes;
    $remaining = [];
    foreach ($parts as $key => $part) {
        if (isset($current[$part])) {
            $current = $current[$part];
        } else {
            // 检查是否有通配符路由
            if (isset($current['*'])) {
                $current = $current['*'];
                $remaining = array_slice($parts, $key);
                break;
            } else {
                return '404.php';
            }
        }
    }
    if (is_array($current) && isset($current[''])) {
        $defaultPage = $current[''];
        if (is_callable($defaultPage)) {
            return call_user_func($defaultPage, $remaining);
        }
        return $defaultPage;
    }
    if (is_callable($current)) {
        return call_user_func($current, $remaining);
    }
    if (is_string($current)) {
        if ($remaining) {
            return '404.php';
        }
        return $current;
    }
    return '404.php';
}


// 检查查询字符串中是否有 'router' 参数
if (isset($_GET['router'])) {
    // 使用查询字符串中的 'router' 参数
    $parts = explode('/', $_GET['router']);
} else {
    // 使用路径路由
    $parts = explode('/', $uri);
}

// 调用路由处理函数
$page = handleRoute($parts, $routes);

// 检查文件是否存在并包含
$file = APP_ROOT . '/pages/' . $page;
if (file_exists($file)) {
    include $file;
} else {
    header("HTTP/1.0 404 Not Found");
    include APP_ROOT . '/pages/404.php';
    exit;
}