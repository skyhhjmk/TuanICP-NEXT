<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
include APP_ROOT . '/lib/twig_config.php';
// 获取请求的 URI 并移除查询字符串部分
$uri = $_SERVER['REQUEST_URI'];
$uri = strtok($uri, '?');
$uri = trim($uri, '/');
// 解析路由参数
$parts = explode('/', $uri);
// 定义路由映射
$routes = [
    '' => 'home.php',
    'about' => 'about.php',
    'contact' => 'contact.php',
    'plan' => 'plan.php',
    'this' => 'this.php',
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
            'tag' => [
                'getinfo' => 'api/v1/tag/GetInfo.php',
                'new' => 'api/v1/tag/new.php',
                'tag_api' => 'api/v1/tag/tag_api.php'
            ]
        ]
    ],
    'admin' => [
        '' => 'admin/index.php', // 默认页面已定义
        'site' => 'admin/site.php',
        'plugin' => 'admin/plugin.php',
        'tag' => 'admin/tag.php',
        'new-tag' => 'admin/new-tag.php',
        'tag-store' => 'admin/tag-store.php',
        'settings' => 'admin/settings.php'
    ],
];
$pluginAddVars = do_action('page_router');
if (!empty($pluginAddVars)) {
    // 如果插件返回了值，则合并到$pageVars数组中
    $routes = array_merge($routes, $pluginAddVars);
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


// 调试信息
//echo "APP_ROOT: " . APP_ROOT . "<br>";
//echo "URI: " . $uri . "<br>";
//echo "Parts: ";
//print_r($parts);
//echo "<br>";
$page = handleRoute($parts, $routes);
//echo "Page: " . $page . "<br>";
$file = APP_ROOT . '/pages/' . $page;
//echo "File: " . $file . "<br>";

// 检查文件是否存在
if (file_exists($file)) {
    include $file;
} else {
    header("HTTP/1.0 404 Not Found");
    include APP_ROOT . '/pages/404.php';
    exit;
}

