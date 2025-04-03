<?php


if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

$user_role = get_current_user_role();
if (!$user_role){
    header('Location: '. get_Url('admin/login'));
}

// 定义API URL
$apiUrl = 'http://icpn.com/app_store_api.php'; // 假设这是插件市场的API URL

// 获取远端API的插件数据
function fetchRemotePlugins($apiUrl) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $pluginsData = curl_exec($ch);
    curl_close($ch);
    return json_decode($pluginsData, true);
}

// 获取本地已安装的插件
function getLocalPlugins() {
    $localPlugins = [];
    $allPlugins = get_all_plugins(); // 使用 get_all_plugins 函数获取所有插件信息

    foreach ($allPlugins as$plugin) {
        $localPlugins[$plugin['plugin_name']] = [
            'file' => $plugin['plugin_entry'],
            'active' => $plugin['is_active']
        ];
    }
    return $localPlugins;
}

// 假设这是从远程API获取的插件数据
$remotePlugins = fetchRemotePlugins($apiUrl);
$localPlugins = getLocalPlugins();

// 遍历远端插件数据，判断是否已安装或激活
foreach ($remotePlugins as &$plugin) {
    $pluginName =$plugin['name'];
    if (isset($localPlugins[$pluginName])) {
        $plugin['status'] = 'installed';
        if ($localPlugins[$pluginName]['active']) {
            $plugin['status'] = 'activated';
        }
    } else {
        $plugin['status'] = 'not_installed';
    }

    // 检查插件是否可用
    if (!$plugin['available']) {
        $plugin['status'] = 'unavailable';
    }
}

global $add_twigVariables;
// Twig模板引擎变量赋值
$add_twigVariables = [
    'plugins' => $remotePlugins
];



function app_store_page_vars($routes) {
    global $add_twigVariables;
    $routes = array_merge($routes, $add_twigVariables); // 合并两个数组
    return $routes;
}

// 添加过滤器
add_filter('page_vars', 'app_store_page_vars');

$twig = initTwig();
echo $twig->render('@admin/app_store.html.twig', get_Page_vars());