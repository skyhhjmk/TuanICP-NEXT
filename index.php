<?php
define('APP_ROOT', __DIR__);
// 定义插件目录常量
define('WT_PLUGIN_DIR', APP_ROOT . '/data/plugins');
require APP_ROOT . '/lib/globalExceptionHandler.php'; // 全局异常处理，需要在所有文件之前引入
include APP_ROOT . '/vendor/autoload.php'; // 加载第三方库
include APP_ROOT . '/lib/func/plugin.php';
include APP_ROOT . '/lib/db.php'; // 数据库连接
if (file_exists(APP_ROOT . '/install.lock')){
    load_plugins(); // 加载插件
}
include APP_ROOT . '/lib/error/error_func.php'; // 错误处理
include APP_ROOT . '/lib/func/conf_loader.php'; // 配置文件加载
include APP_ROOT . '/lib/router.php'; // 路由，负责匹配路由、返回对应页面
//$dbc = initDatabase();
//$config = get_global_site_config();
//var_dump($config);
do_action('entry_file');