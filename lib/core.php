<?php
require_once 'func/function.php';
require_once 'func/action.php';
require_once 'func/plugin.php';
require_once 'func/sendmail.php';

if (file_exists(APP_ROOT . '/.env')) {
    load_plugins(); // 加载插件
}
