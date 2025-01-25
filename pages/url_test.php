<?php
// 准备JSON响应
$response = [
    'code' => 0,
    'msg' => 'success',
];

// 设置响应头
header('Content-Type: application/json; charset=utf-8');

// 输出JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);