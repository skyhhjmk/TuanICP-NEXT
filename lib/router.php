<?php
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