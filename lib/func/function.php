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
 * 获取配置项的值，如果配置项不存在，则返回默认值
 * @param string $key 设置项
 * @param null $default 默认值
 * @param bool $init
 * @param bool $useCache
 * @return mixed|null
 * @throws JsonException
 */
function get_Config(string $key, $default = null, bool $init = false, bool $useCache = true): mixed
{
    if ($useCache) {
        // 初始化缓存池，如果缓存被禁用，则 $cachePool 为 null
        $cachePool = initCache();

        // 定义缓存项的键
        $cacheKey = 'config_' . $key;

        // 如果缓存池不为 null，尝试从缓存中获取数据
        if ($cachePool !== null) {
            $item = $cachePool->getItem($cacheKey);
            if ($item->isHit()) {
                // 缓存命中，直接返回缓存中的数据
                return $item->get();
            }
        }

        // 初始化数据库连接
        $dbc = initDatabase();
        $query = "SELECT v FROM config WHERE k = :key";
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // 如果查询结果存在，保存到缓存中（如果缓存池不为 null）
            if ($cachePool !== null) {
                $item->set($result['v']);
                $cachePool->save($item);
            }
            return $result['v'];
        } else {
            if ($init) {
                // 如果需要初始化配置，则调用 set_Config 函数
                set_Config($key, $default);
                // 保存默认值到缓存中（如果缓存池不为 null）
                if ($cachePool !== null) {
                    $item->set($default);
                    $cachePool->save($item);
                }
            }
            return $default;
        }
    } else {
        // 初始化数据库连接
        $dbc = initDatabase();
        $query = "SELECT v FROM config WHERE k = :key";
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['v'];
        } else {
            if ($init) {
                // 如果需要初始化配置，则调用 set_Config 函数
                set_Config($key, $default);
            }
            return $default;
        }
    }


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
 * 转义HTML内容中的字符串
 * 通常用于直接插入HTML标签之间的文本
 * @param string $text 要转义的字符串
 * @return string 转义后的字符串
 */
function esc_html($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * 转义HTML属性值中的字符串
 * 通常用于HTML标签的属性值
 * @param string $text 要转义的字符串
 * @return string 转义后的字符串
 */
function esc_attr($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * 获取页脚菜单
 * @param string $style 样式
 * @return string HTML菜单
 * @throws JsonException
 */
function get_Menu(string $style = 'bottom'): string
{
    $init = [
        '首页' => [
            'title' => '首页',
            'url' => get_Url('index'),
        ],
    ];
    $serInit = serialize($init);
    $menu = get_Config('menu', $serInit, true);
    $unserializedMenu = unserialize($menu);

    // 允许插件通过过滤器修改菜单样式
    $filteredMenu = apply_filters('get_menu_style', $unserializedMenu, $style);

    // 将菜单转换为HTML字符串
    $htmlMenu = convertMenuToHtml($filteredMenu, $style);

    return $htmlMenu;
}

/**
 * 将菜单数据转换为HTML字符串
 * @param array $menu 菜单数据
 * @param string $style 菜单样式
 * @return string HTML菜单
 */
function convertMenuToHtml(array $menu, string $style): string
{
    // 根据样式和菜单数据生成HTML
    $html = '<ul class="' . esc_attr($style) . '-menu">';
    foreach ($menu as $menuItem) {
        $html .= '<li>' . esc_html($menuItem['title']) . '</li>';
    }
    $html .= '</ul>';
    return $html;
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
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
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
            ]
        ],
    ];
// 合并额外的内容到$page_vars数组中
    $page_vars = array_merge($page_vars, $additionalVars);
// 定义一个默认的返回值
    $default_page_vars = array();

// 使用 apply_filters 触发钩子，并获取返回值
    $pluginAddPageVars = apply_filters('add_page_vars', $default_page_vars);

// 如果插件返回了值，则合并到 $page_vars 数组中
    if (!empty($pluginAddPageVars) && is_array($pluginAddPageVars)) {
        $page_vars = array_merge($page_vars, $pluginAddPageVars);
    }
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