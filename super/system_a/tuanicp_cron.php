<?php



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

include APP_ROOT . '/lib/db.php'; // 数据库连接
include APP_ROOT . '/lib/cache.php'; // 缓存连接
include APP_ROOT . '/lib/core.php';

do_action('startup'); // 路由前执行，可以用来拦截访问次数过多等

// 执行CRON任务
execute_cron_jobs();

do_action('shutdown');