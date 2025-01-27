<?php
define('APP_ROOT', __DIR__);
define('DEBUG', true);
// 定义插件目录常量
define('TUANICP_PLUGIN_DIR', APP_ROOT . '/data/plugins');
define('TUANICP_TEMPLATE_DIR', APP_ROOT . '/data/templates');
if(!file_exists(APP_ROOT . '/.env')){
    header('Location: /install/');
    exit;
}
require APP_ROOT . '/lib/globalExceptionHandler.php'; // 全局异常处理，需要在所有文件之前引入
include APP_ROOT . '/lib/error/error_func.php'; // 错误处理
include APP_ROOT . '/vendor/autoload.php'; // 加载第三方库
include APP_ROOT . '/lib/db.php'; // 数据库连接
include APP_ROOT . '/lib/cache.php'; // 缓存连接
include APP_ROOT . '/lib/core.php';

include APP_ROOT . '/lib/router.php'; // 路由，负责匹配路由、返回对应页面
//$dbc = initDatabase();
//$config = get_global_site_config();
//var_dump($config);
//do_action('send_mail','1827348941@qq.com', '测试邮件', '测试邮件内容');
do_action('shutdown');