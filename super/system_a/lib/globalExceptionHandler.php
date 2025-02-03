<?php

function globalExceptionHandler($exception)
{

// 初始化缓存池
    $cachePool = initCache();

// 清空整个缓存池
    $cachePool->clear();

    // 获取异常的详细信息
    $message = $exception->getMessage();
    $code = $exception->getCode();
    $file = $exception->getFile();
    $line = $exception->getLine();
    $trace = $exception->getTraceAsString();

    // 记录错误信息到日志
    error_log("Uncaught Exception: '{$message}' in {$file} on line {$line}");
    error_log("Exception Trace: {$trace}");

// 判断是否在更新完成后的10分钟内
    if (isRecentlyUpdated()) {
        // 获取当前槽位
        $currentSlot = getCurrentSlot();
        // 更新retry文件
        updateRetryFile($currentSlot);

        // 检查retry文件并决定是否切换槽位
        $retryFile = APP_ROOT . "/../../boot_loader/retry_{$currentSlot}";
        if (file_exists($retryFile)) {
            $retryCount = file_get_contents($retryFile);
            $retryCount = intval($retryCount);
            if ($retryCount >= 3) {
                // 切换槽位
                switchSlot();
                // 删除retry文件
                unlink($retryFile);
            }
        }
    }

    $solutions = [
        'file not found' => '请检查文件路径是否正确。',
        'database error' => '请检查数据库连接设置。',
        'file not writable' => '文件不可写，请检查文件权限。',
        'undefined variable' => '某个变量为空，检查输入参数。',
        'undefined function' => '尝试调用了一个不存在的函数，请检查函数名称。',
        'must be of type array, string given' => '需要提供一个数组，但是提供了字符串。',
        'undefined property' => '尝试访问一个不存在的属性，请检查属性名称。',
        'Unable to find template' => '请检查模板主题是否正确。',
        'database connection' => '请检查数据库连接设置。',
        'permission denied' => '请检查文件或目录权限。',
        'environment variables' => '请检查环境变量设置。',
        'empty variable' => '某个变量为空，检查输入参数。',
        'syntax error' => '代码中存在语法错误，请检查代码。',
        'method not found' => '调用了一个不存在的方法，请检查方法名称。',
        'class not found' => '尝试使用一个未定义的类，请检查类名是否正确。',
        'undefined index' => '尝试访问数组中不存在的键，请检查键名。',
        'out of memory' => '内存不足，请检查脚本内存使用情况或增加服务器内存。',
        'timeout error' => '请求超时，请检查网络连接或增加脚本执行时间。',
        'invalid argument' => '传递给函数的参数无效，请检查参数类型和值。',
        'invalid configuration' => '配置文件错误，请检查配置项是否正确。',
        'connection refused' => '连接被拒绝，请检查服务是否启动或端口是否正确。',
        'invalid operation' => '尝试执行无效的操作，请检查操作逻辑。',
        'duplicate entry' => '数据库中存在重复的条目，请检查数据唯一性。',
        'resource not available' => '资源不可用，请检查所需资源是否已经加载或存在。',
        'authentication failed' => '认证失败，请检查用户名和密码。',
        'file format error' => '文件格式错误，请检查文件是否正确。',
        'invalid request' => '无效的请求，请检查请求参数和格式。',
        'quota exceeded' => '配额超出，请检查服务使用情况或联系服务提供商。',
    ];


    // 根据错误消息提供可能的解决方案
    $solution = "未知错误，请汇报显示的错误信息。";
    foreach ($solutions as $keyword => $advice) {
        if (strpos(strtolower($message), $keyword) !== false) {
            $solution = $advice;
            break;
        }
    }

    // 开始输出HTML
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>哎呀，出错了！</title>
        <style>
            body {
                font-family: 'Arial', sans-serif;
                background-color: #f2f2f2;
                color: #333;
                text-align: center;
                padding-top: 100px;
            }

            .container {
                background-color: #fff;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
                overflow: hidden; /* 防止内容溢出容器 */
            }

            .error-title {
                font-size: 24px;
                color: #d9534f;
                margin-bottom: 20px;
            }

            .error-message {
                font-size: 18px;
                margin-bottom: 20px;
                word-wrap: break-word; /* 允许长单词换行 */
            }

            .solution {
                font-size: 16px;
                color: #5bc0de;
                margin-bottom: 20px;
            }

            .debug-info {
                max-height: 200px; /* 设置最大高度 */
                overflow-y: auto; /* 添加垂直滚动条 */
                margin-bottom: 20px;
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                padding: 10px;
                word-wrap: break-word; /* 允许长单词换行 */
            }

            .back-home-button {
                display: inline-block;
                padding: 10px 20px;
                margin-top: 20px;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                transition: background-color 0.3s ease;
            }

            .back-home-button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="error-title">哎呀，出错了！</div>
        <div class="error-message">我们遇到了一些问题，请稍后再试。</div>
        <?php
        if (DEBUG === true): ?>
            <div class="solution">可能的原因: <?php
                echo $solution; ?></div>
            <div class="debug-info" style="display: block;">
            <pre>未捕获的异常[code:<?php
                echo $code; ?>]: '<?php
                echo $message; ?>' <?php
                echo PHP_EOL; ?>在 <?php
                echo $file; ?> 的第 <?php
                echo $line; ?> 行。
堆栈跟踪: <?php
                echo $trace; ?></pre>
            </div>
        <?php
        endif; ?>
        <a href="/" class="back-home-button">返回首页</a>
    </div>
    </body>
    </html>
    <?php
    // 结束输出HTML
}

// 设置全局异常处理函数
set_exception_handler('globalExceptionHandler');
?>