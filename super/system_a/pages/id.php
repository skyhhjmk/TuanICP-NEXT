<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

if (empty($_GET['keyword'])) {
    header('Location: /');
    exit();
}

// 获取URL参数 keyword
$keyword = $_GET['keyword'] ?? '';
$urlPattern = $_GET['keyword'] ?? '';

$dbc = initDatabase();
// 查询备案信息
// 使用 OR 逻辑来查询备案号或域名
$sql = "SELECT site_id as site_id,site_name as site_name, site_domain as site_domain, 
       site_icp_number as site_icp_number,
       site_desc as site_desc, site_avatar_url as site_avatar_url, site_config as site_config,
       site_status as site_status, site_ext as site_ext, status as status, created_at as created_at
FROM sites WHERE site_icp_number = :keyword OR site_domain = :urlPattern";
$stmt = $dbc->prepare($sql);
$stmt->execute(['keyword' => $keyword, 'urlPattern' => $urlPattern]);
$icp_record = $stmt->fetch(PDO::FETCH_ASSOC);

// 如果没有找到记录，则弹窗提示并跳转
if (!$icp_record) {
    echo "<script>alert('没有找到对应的ICP备案信息。');</script>";
    echo "<script>window.location.href='/';</script>";
    exit;
}

global $icp_common_icp_record;

switch ($icp_record['status']) {
    case 'awaiting':
        $status_msg = "该备案信息正在审核中，请正确悬挂信息并且耐心等待。";

        $icp_common_icp_record = [
            'site_domain' => '******',
            'site_name' => '******',
            'site_desc' => '******',
            'site_avatar_url' => '******',
            'site_icp_number' => $icp_record['site_icp_number'],
            'created_at' => $icp_record['created_at'],
        ];
        break;
    case 'approved':
        $status_msg = "该备案信息已经通过审核，请正确悬挂信息。";
        $icp_common_icp_record = $icp_record;
        break;
    case 'rejected':
        $icp_common_icp_record = [
            'site_domain' => '******',
            'site_name' => '******',
            'site_desc' => '******',
            'site_avatar_url' => '******',
            'site_icp_number' => $icp_record['site_icp_number'],
            'created_at' => $icp_record['created_at'],
        ];
        $status_msg = "该备案信息已被驳回，请修正问题后重新提交审核。";
        break;
    default:
        $icp_common_icp_record = [
            'site_domain' => '******',
            'site_name' => '******',
            'site_desc' => '******',
            'site_avatar_url' => '******',
            'site_icp_number' => $icp_record['site_icp_number'],
            'created_at' => $icp_record['created_at'],
        ];
        $status_msg = "未知状态";
        break;
}

$icp_common_icp_record['status_msg'] = $status_msg;
//var_dump($icp_common_icp_record);

function id_add_page_vars($page_vars)
{
    global $icp_common_icp_record;
    $addVars = [
        'user' => [
            'site_domain' => $icp_common_icp_record['site_domain'],
            'site_name' => $icp_common_icp_record['site_name'],
            'site_desc' => $icp_common_icp_record['site_desc'],
            'site_avatar_url' => $icp_common_icp_record['site_avatar_url'],
            'site_icp_number' => $icp_common_icp_record['site_icp_number'],
            'created_at' => $icp_common_icp_record['created_at'],
            'site_status' => $icp_common_icp_record['status_msg'],
        ],
    ];
    $page_vars = array_merge($page_vars, $addVars);

    return $page_vars;
}
add_filter('page_vars', 'id_add_page_vars');

$twig = initTwig();
echo $twig->render('@index/id.html.twig', get_Page_vars());