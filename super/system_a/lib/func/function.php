<?php
/*
 * Copyright (c) 2025.
 * 本项目由【风屿团】项目团队持有，一旦您存在使用、修改、参与开发、分发本软件的开源副本、转发此软件的信息等与本软件有关的行为，则默认您已经阅读并且同意此协议。
 *
 * 通常情况下，您具有以下权力：
 * 修改本软件的开源部分并且保持开源，分发；
 * 在您的项目中使用本软件并声明；
 * 开发并出售可在本系统中正常工作的插件。
 *
 * 通常情况下，您不得实施以下可能对我们造成损失的行为：
 * 二次分发、倒卖、共享授权账号或源码；
 * 破解或尝试反编译等来绕过软件包括但不限于付费插件的任何收费或闭源模块；
 * 在我们开发的系统中编写包括但不限于恶意代码、后门、木马等；
 * 充当开发者售卖软件副本；
 * 私自建设授权系统接口响应站（俗称自建授权站）。
 *
 * 任何情况下，您必须承认：
 * 无条件认同“台湾省是中国领土不可分割的一部分”这一立场；
 * 若产生任何纠纷，本项目开发者及开发团队不承担任何责任。
 *
 * 若违反以上协议，我们有权向您索取不低于3000元人民币的赔偿。
 *
 * 我们的声明：
 * 我们使用了众多开源库，在此鸣谢背后的开发者/团队。
 * 若使用本软件且在未经许可的情况下进行商业活动，我们有权追回您进行商业活动的所得资产（仅使用本软件产生的资产）并要求您支付相应的商业授权和赔偿费用或要求您停止商业行为。
 * 最终解释权归风屿团所有开发成员所有。
 */

if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}



/**
 * 设置配置项的值
 * @param string $key 设置项
 * @param mixed $value 设置的值
 * @return bool 是否设置成功
 * @throws JsonException
 */
function set_Config(string $key, mixed $value): bool
{
    $dbc = initDatabase();
    $query = "INSERT INTO config (k, v) VALUES (:key, :value) ON DUPLICATE KEY UPDATE v = :value";
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':key', $key);
    $stmt->bindParam(':value', $value);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

/**
 * 获取启用的主题名称
 * 如果没有启用的主题，则默认使用tuan
 * @return mixed
 * @throws JsonException
 */
function get_Template_name(): mixed
{
    $template_name = get_Config('template_name', 'tuan', true);
    if ($template_name === null) {
        set_Config('template_name', 'tuan');
        return 'tuan';
    }
    return $template_name;
}


/**
 * 获取当前的URL
 * eg: https://example.com/xxx
 * @return string
 */
function getFullURL(): string
{
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];

    return $scheme . '://' . $host . $uri;
}


/**
 * 获取域名的URL
 * eg: https://example.com
 * @return string
 */
function getDomainURL(): string
{
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];

    return $scheme . '://' . $host;
}

/**
 * 获取一个页面的URL
 * 自动判断是否伪静态
 * @param $page
 * @param $params
 * @return string
 */
function get_Url($page, $params = null): string
{
    $dotenv = Dotenv\Dotenv::createImmutable(DATA_ROOT);
    $dotenv->load();
    $dotenv->required('REWRITE')->notEmpty();
    $Rewrite = $_ENV['REWRITE'] ?? false;
    if ($Rewrite) {
        if ($params !== null) {
            return '/' . $page . '?' . http_build_query($params);
        } else {
            return '/' . $page;
        }
    } else {
        if ($params !== null) {
            return '/index.php?router=' . $page . '&' . http_build_query($params);
        } else {
            return '/index.php?router=' . $page;
        }
    }
}

/**
 * 获取页面变量，用于渲染模板
 * @param array $additionalVars
 * @return array|null
 * @throws JsonException
 */
function get_Page_vars(array $additionalVars = []): ?array
{
    $page_vars = [
        'global' => [
            'site_name' => get_Config('site_name', '云团子', true),
            'site_url' => get_Config('site_url', 'https://icp.yuncheng.fun/', true),
            'site_avatar' => get_Config('site_avatar', 'https://www.yuncheng.fun/static/webAvatar/11727945933180571.png', true),
            'site_abbr' => get_Config('site_abbr', '团', true),
            'site_description' => get_Config('site_description', '哇，是谁家的小可爱？', true),
            'site_keywords' => get_Config('site_keywords', '团备, 团ICP备, 云团子ICP备案中心 ,云团子 ,杜匀程', true),
            'admin_nickname' => get_Config('admin_nickname', '云团子', true),
            'admin_email' => get_Config('admin_email', 'yun@yuncheng.fun', true),
            'admin_qq' => get_Config('admin_qq', '937319686', true),
            'footer_code' => get_Config('footer_code', '', true),
            'template_name' => get_Template_name(),
            'audit_duration' => get_Config('audit_duration', '3天', true),
            'feedback_link' => get_Config('feedback_link', 'https://qm.qq.com/q/kClRRuBmOQ', true),
            'background_image' => get_Config('background_image', 'https://cdn.koxiuqiu.cn/ccss/ecyrw/ecy%20(68).png', true),
            'update_log_page' => 'https://authpro.cutetuan.cn/#/Soft/getUpdateLog?skey=b696c5ed-7c0a-43c2-b223-9cdf6053f7ff',
        ],
        'template' => [
            'root' => '/data/templates/' . get_Template_name(),
        ],
        'url' => [
            'index' => '/',
            'id' => get_Url('id'),
        ],
        'admin' => [
            'index' => get_Url('admin'),
            'profile' => get_Url('admin/profile'),
            'login' => get_Url('admin/login'),
            'logout' => get_Url('admin/logout'),
            'plugin' => get_Url('admin/plugin'),
            'sidebar' => get_menus('admin_sidebar'),
            'app-store' => get_Url('admin/app-store'),
            'api' => [
                'get_plugins' => get_Url('admin/api/get_plugins'),
                'plugin_ctl' => get_Url('admin/api/plugin_ctl'),
            ],
        ],
        'menu' => [
            'footer' => get_menus_html('footer','footer'),
        ]
    ];

// 使用 apply_filters 触发钩子，并获取返回值
    $page_vars = apply_filters('page_vars', $page_vars,$page_vars);

    return $page_vars;
}

// 检查是否使用SSL
function is_ssl() {
    if (isset($_SERVER['HTTPS'])) {
        if ('on' == strtolower($_SERVER['HTTPS']) || '1' ==$_SERVER['HTTPS']) {
            return true;
        }
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' ==$_SERVER['SERVER_PORT'])) {
        return true;
    }
    return false;
}

function isAdmin()
{

}