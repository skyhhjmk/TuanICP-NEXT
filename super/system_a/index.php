<?php


if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

define('SOFTWARE_SKEY', 'b696c5ed-7c0a-43c2-b223-9cdf6053f7ff');


if(!file_exists(DATA_ROOT . '/.env')){
    header('Location: /install/');
    exit;
}
session_start();
require APP_ROOT . '/lib/globalExceptionHandler.php'; // 全局异常处理，需要在所有文件之前引入
include APP_ROOT . '/lib/error/error_func.php'; // 错误处理
include APP_ROOT . '/vendor/autoload.php'; // 加载第三方库

$dotenv = Dotenv\Dotenv::createImmutable(DATA_ROOT);
$dotenv->load();
$dotenv->required(['COOKIE_KEY']);

define('COOKIE_KEY', $_ENV['COOKIE_KEY']);
define('VKEY', "def5d3aa-da27-4b32-b971-a6d60612c64e");
define('SKEY', "b696c5ed-7c0a-43c2-b223-9cdf6053f7ff");

include APP_ROOT . '/lib/db.php'; // 数据库连接
include APP_ROOT . '/lib/cache.php'; // 缓存连接
include APP_ROOT . '/lib/core.php';

do_action('startup'); // 路由前执行，可以用来拦截访问次数过多等
include APP_ROOT . '/lib/router.php'; // 路由，负责匹配路由、返回对应页面
do_action('shutdown');