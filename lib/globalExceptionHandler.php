<?php
function globalExceptionHandler($exception) {
    // 获取异常的详细信息
    $message =$exception->getMessage();
    $code =$exception->getCode();
    $file =$exception->getFile();
    $line =$exception->getLine();
    $trace =$exception->getTraceAsString();

    // 记录错误信息到日志
    error_log("Uncaught Exception: '{$message}' in {$file} on line {$line}");
    error_log("Exception Trace: {$trace}");

    try {
        $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
        $dotenv->load();

        $dotenv->required('DEBUG')->notEmpty();
        $DEBUG =$_ENV['DEBUG'];
    } catch (Exception $e) {
        // 如果加载.env文件失败，则使用默认的配置
        $DEBUG = 'false';
    }

    $solutions = [
        'file not found' => '请检查文件路径是否正确。',
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
    foreach ($solutions as$keyword => $advice) {
        if (strpos(strtolower($message),$keyword) !== false) {
            $solution =$advice;
            break;
        }
    }

    // 根据DEBUG配置显示错误信息
    if ($DEBUG === 'false') {
        echo "发生了一个特殊的错误，请向站点管理员汇报，谢谢。";
    } elseif ($DEBUG === 'true') {
        echo "<b style='color: red'>";
        echo "发生了一个特殊的错误，请向站点管理员汇报，谢谢。请根据下方可能的原因先行排查是否是您的配置错误。<br>";
        echo "<span style='color: blue'>可能的原因: {$solution}</span><br>";
        echo "<span style='color: blue'>如果您是开发者正在修改代码，请检查您的代码是否正确。特别注意在 {$file} 的第 {$line} 行。</span><br>";
        echo "未捕获的异常[code:{$code}]: '{$message}' 在 {$file} 的第 {$line} 行。<br>";
        echo "堆栈跟踪: {$trace}";
        echo "</b>";
    } else {
        echo "发生了一个特殊的错误，请向站点管理员汇报，谢谢。此外，站点的配置有误！";
    }
}

// 设置全局异常处理函数
set_exception_handler('globalExceptionHandler');