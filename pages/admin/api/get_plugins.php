<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
// 获取所有插件
    $all_plugins = get_all_plugins();

// 创建统一的JSON输出格式
    $output = [
        'code' => 0,
        'msg' => '',
        "count" => count($all_plugins),
        "data" => []
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
        $output['data'] = get($page, $limit);
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
    global $all_plugins;
    $filtered_plugins = array_filter($all_plugins, function ($plugin) use ($plugin_name) {
        return stripos($plugin['plugin_name'], $plugin_name) !== false;
    });

    $offset = ($page - 1) * $limit;
    return array_slice($filtered_plugins, $offset, $limit);
}

function get($page, $limit): array
{
    global $all_plugins;
    $offset = ($page - 1) * $limit;
    return array_slice($all_plugins, $offset, $limit);
}