<?php


if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

$user_role = get_current_user_role();
if (!$user_role){
    header('Location: '. get_Url('admin/login'));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
// 获取所有插件
    global $get_all_plugins;
    $get_all_plugins = get_all_plugins();

// 创建统一的JSON输出格式
    $output = [
        'code' => 0,
        'msg' => '',
//        "count" => count($get_all_plugins),
    ];

// 输出数组内容为JSON格式
    header('Content-Type: application/json');

// 获取分页参数
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    $plugin_name = $_GET['plugin_name'] ?? '';

    if (!empty($plugin_name)) {
        // 执行搜索函数
        $output['data'] = search($plugin_name, $page, $limit);
        $output['count'] = count($output['data']);
    } else {
        // 执行分页函数
        $output['data'] = get($get_all_plugins,$page, $limit);
    }
    echo json_encode($output);
    exit;
} else {
    header('Content-Type: application/json');
    echo json_encode(['code' => 1, 'msg' => 'Method Not Allowed']);
    exit;
}

function search($plugin_name, $page, $limit): array
{
    global $get_all_plugins;
    $filtered_plugins = array_filter($get_all_plugins, function ($plugin) use ($plugin_name) {
        return stripos($plugin['plugin_name'], $plugin_name) !== false;
    });

    $offset = ($page - 1) * $limit;
    return array_slice($filtered_plugins, $offset, $limit);
}

function get($get_all_plugins,$page, $limit): array
{
//    var_dump($get_all_plugins);
    $offset = ($page - 1) * $limit;
    return array_slice($get_all_plugins, $offset, $limit);
}