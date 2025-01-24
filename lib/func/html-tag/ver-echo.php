<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
use MatthiasMullie\Minify\JS;

function output_minified_js($jsFilePath = JS_ROOT . '/helloworld.js', $output_path = JS_ROOT . '/helloworld.min.js'): string
{
// 读取原始JavaScript文件内容
    $jsContent = file_get_contents($jsFilePath);
// 使用JShrink压缩JavaScript代码
    $minifier = new JS($jsContent);
    $compressedJs = $minifier->minify();
// 计算压缩后JavaScript代码的SHA-256哈希值
    $hash = base64_encode(hash('sha256', $compressedJs, true));
// 输出压缩后的JavaScript文件
    file_put_contents($output_path, $compressedJs);

// 输出带有integrity属性的<script>标签
// 此处不进行转义
    return '<script src="' . $output_path . '" integrity="sha256-' . $hash . '" crossorigin="anonymous"></script>';
}