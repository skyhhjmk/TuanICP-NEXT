<?php


// 模拟数据库中的插件数据
$plugins = [
    [
        'name' => '应用商店',
        'version' => '1.0.0',
        'description' => '向后台添加一个应用商店，支持在线下载主题和插件',
        'available' => true
    ],
    [
        'name' => 'Plugin Two',
        'version' => '2.3.1',
        'description' => 'This is the second plugin.',
        'available' => false
    ],
    [
        'name' => 'Plugin Three',
        'version' => '0.9.5',
        'description' => 'This is the third plugin.',
        'available' => true
    ],
];

// 设置响应头为JSON格式
header('Content-Type: application/json');

// 检查请求方法是否为GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 输出插件列表的JSON数据
    echo json_encode($plugins);
} else {
    // 如果请求方法不是GET，返回405 Method Not Allowed
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}

