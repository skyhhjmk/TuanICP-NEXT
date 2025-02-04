<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
// 获取URL参数 keyword
$keyword = $_GET['keyword'] ?? '';
if (!$keyword) {
    header('Location: /');
    exit();
}
$dbc = initDatabase();
// 查询备案信息
// 使用 OR 逻辑来查询备案号或域名
$sql = "SELECT site_id, user_id, site_name, site_domain, site_icp_number, site_desc, site_avatar_url, site_config, site_status, site_ext, status, created_at FROM sites WHERE site_icp_number = :keyword OR site_domain LIKE :urlPattern";
$stmt = $dbc->prepare($sql);
$urlPattern = "%{$keyword}%"; // 用于模糊匹配URL
$stmt->execute(['keyword' => $keyword, 'urlPattern' => $urlPattern]);
$icp_record = $stmt->fetch(PDO::FETCH_ASSOC);

// 如果没有找到记录，则弹窗提示并跳转
if (!$icp_record) {
    echo "<script>alert('没有找到对应的ICP备案信息。');</script>";
    echo "<script>window.location.href='/';</script>";
    exit;
}

// 检查备案状态是否为“审核通过”
if ($icp_record['STATUS'] !== '审核通过') {
    // 如果状态不是“审核通过”，则弹窗提示用户
    echo "<script type='text/javascript'>";
    echo "alert('该备案信息未通过审核');";
    echo "window.location.href = '/xg?keyword=" . urlencode($keyword) . "';";
    echo "</script>";
    exit; // 终止脚本执行
}

$icp_common_icp_record = $icp_record;
global $icp_common_icp_record;
function id_add_page_vars($page_vars)
{
    global $icp_common_icp_record;
    $addVars = [
        'user' => [
            'site_domain' => $icp_common_icp_record['site_domain'],
            'site_name' => $icp_common_icp_record['site_name'],
            'site_desc' => $icp_common_icp_record['site_desc'],
            'site_avatar_url' => $icp_common_icp_record['site_avatar_url'],
            'site_config' => $icp_common_icp_record['site_config'],
            'site_status' => $icp_common_icp_record['site_status'],
            'site_ext' => $icp_common_icp_record['site_ext'],
            'site_id' => $icp_common_icp_record['site_id'],
            'site_icp_number' => $icp_common_icp_record['site_icp_number'],
            'created_at' => $icp_common_icp_record['created_at'],
        ],
        'user_icp_number' => $icp_common_icp_record['site_icp_number'],
        'user_site_domain' => $icp_common_icp_record['site_domain'],
        'user_website_name' => $icp_common_icp_record['website_name'],
        'owner' => $icp_common_icp_record['owner'],
        'status' => $icp_common_icp_record['STATUS'],
        'user_site_desc' => $icp_common_icp_record['site_desc'],
        'update_time' => $icp_common_icp_record['update_time'],
    ];

    $page_vars = array_merge($page_vars, $addVars);

    return $page_vars;
}
add_filter('page_router', 'icp_user_page_router');

$twig = initTwig();
echo $twig->render('@index/id.html.twig', get_Page_vars());