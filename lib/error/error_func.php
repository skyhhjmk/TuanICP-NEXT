<?php
require APP_ROOT . '/vendor/autoload.php';

function miss_env($env_name, $tip = '请检查 .env 文件')
{
    output_error("缺少环境变量或配置有误，出错的环境变量为:" . $env_name . "，可能的解决方法为：" . $tip);
}

function output_error($error_msg = '发生了一个未知的错误！', $error_detail = null, $error_code = '500')
{


}