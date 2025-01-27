<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

/**
 * @param $key
 * @return mixed|null
 * @throws JsonException
 */
function get_Config($key, $default = null, $init = false): mixed
{
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
}

/**
 * @param $key
 * @param $value
 * @return bool
 * @throws JsonException
 */
function set_Config($key, $value): bool
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
function esc_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * 转义HTML属性值中的字符串
 * 通常用于HTML标签的属性值
 * @param string $text 要转义的字符串
 * @return string 转义后的字符串
 */
function esc_attr($text) {
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
    $menu = get_Config('menu',$serInit, true);
    $unserializedMenu = unserialize($menu);

    // 允许插件通过过滤器修改菜单样式
    $filteredMenu = apply_filters('get_menu_style',$unserializedMenu, $style);

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
function convertMenuToHtml(array $menu, string$style): string
{
    // 根据样式和菜单数据生成HTML
    $html = '<ul class="' . esc_attr($style) . '-menu">';
    foreach ($menu as$menuItem) {
        $html .= '<li>' . esc_html($menuItem['title']) . '</li>';
    }
    $html .= '</ul>';
    return $html;
}


function getFullURL()
{
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];

    return $scheme . '://' . $host . $uri;
}


function getDomainURL()
{
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];

    return $scheme . '://' . $host;
}

function get_Url($page, $params = null)
{
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();
    $dotenv->required('REWRITE');
    $Rewrite = $_ENV['REWRITE'] ?? false;
    if ($Rewrite === true) {
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
            'login' => get_Url('admin/login'),
            'logout' => get_Url('admin/logout'),
            'plugin' => get_Url('admin/plugin'),
        ],
        'menus' => get_menus(),
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