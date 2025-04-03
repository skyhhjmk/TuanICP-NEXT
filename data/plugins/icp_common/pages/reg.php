<?php

if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
define('REG_PLUGIN_ROOT', __DIR__);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $icp_number = $_POST['icp_number'] ?? null;
    $site_name = $_POST['site_name'] ?? null;
    $site_domain = $_POST['site_domain'] ?? null;
    $site_desc = $_POST['site_desc'] ?? null;
    $owner = $_POST['owner'] ?? null;
    $email = $_POST['email'] ?? null;
    $qq = $_POST['qq'] ?? null;
    $security_code = $_POST['security_code'] ?? null;
    if (!empty($icp_number) && !empty($site_name)
        && !empty($site_domain) && !empty($site_desc)
        && !empty($owner) && !empty($email)
        && !empty($qq) && !empty($security_code)) {
        $site_config = [
            'owner' => $owner,
            'email' => $email,
            'qq' => $qq,
            'security_code_hash' => password_hash($security_code, PASSWORD_DEFAULT),
        ];
        $serialize_site_config = serialize($site_config); // 序列化数据

        try {
            $dbc = initDatabase();
            $stmt = $dbc->prepare("INSERT INTO sites 
    (user_id,site_name,site_domain,site_icp_number,site_desc,site_config) 
VALUES (1,:site_name, :site_domain,:icp_number, :site_desc, :site_config)");
            $stmt->execute([
                ':icp_number' => $icp_number,
                ':site_name' => $site_name,
                ':site_domain' => $site_domain,
                ':site_desc' => $site_desc,
                'site_config' => $serialize_site_config,
            ]);
        } catch (PDOException $e) {
            $reg_error_msg = $e->getMessage();
            echo $reg_error_msg;
            exit;
        }

    }
    header('Location: /id?keyword=' . $icp_number);
    exit;
}

function reg_add_page_vars($page_vars)
{
    $user_icp_number = $_GET['icp_number'] ?? '';
    $addVars = [
        'user' => [
            'icp_number' => $user_icp_number,
            'current_time' => date('Y-m-d H:i:s'),
        ],
        'url' => [
            'reg' => get_Url('reg'),
        ],
    ];

    $page_vars = array_merge($page_vars, $addVars);

    return $page_vars;
}

add_filter('page_vars', 'reg_add_page_vars', 10, 1);
$twig = initTwig();
echo $twig->render('@index/reg.html.twig', get_Page_vars());