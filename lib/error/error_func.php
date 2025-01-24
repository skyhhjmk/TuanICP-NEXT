<?php
require APP_ROOT . '/vendor/autoload.php';

function miss_env($env_name, $tip = '请检查 .env 文件')
{
    output_error("缺少环境变量或配置有误，出错的环境变量为:" . $env_name . "，可能的解决方法为：" . $tip);
}

function output_error($error_msg = '发生了一个未知的错误！', $error_detail = null, $error_code = '500')
{
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->safeLoad();
    $dotenv->required('DEBUG');
    $debug_status = $_ENV['DEBUG'] ?? false;
    if ($debug_status === 'true') {
        echo '错误代码：' . $error_code . '<br>';
        echo '错误信息：' . $error_msg . '<br>';
        if ($error_detail) {
            echo '错误详情：' . $error_detail . '<br>';
        }
    } else {
        echo $error_msg . '<br>' . '若要查看错误信息，请将 DEBUG 设置为 true（即开启调试模式）' . '<br>';
    }

}