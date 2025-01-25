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
 * @return mixed
 * @throws JsonException
 */
function get_Template_name(): mixed
{
    $template_name = get_Config('template_name');
    if ($template_name === null) {
        set_Config('template_name', 'tuan');
        return 'tuan';
    }
    return $template_name;
}

function get_Menu($style = 'bottom')
{
    $init = '';
    $menu = get_Config('menu', '', true);
    $data = unserialize($menu);
}

function get_Page_vars($additionalVars = [])
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
        ]
    ];
// 合并额外的内容到$page_vars数组中
    $page_vars = array_merge($page_vars, $additionalVars);
// 触发钩子，并获取返回值
    $pluginAddPageVars = do_action('add_page_vars');
    if (!empty($pluginAddPageVars)) {
        // 如果插件返回了值，则合并到$page_vars数组中
        $page_vars = array_merge($page_vars, $pluginAddPageVars);
    }
    return $page_vars;
}