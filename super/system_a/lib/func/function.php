<?php


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
    // 当key存在时更新，不存在时插入
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
            'update_log_page' => 'https://authpro.example.com/#/Soft/getUpdateLog?skey=b696c5ed-7c0a-43c2-b223-9cdf6053f7ff',
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