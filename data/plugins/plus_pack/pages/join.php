<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $icp_number = $_POST['icp_number'];
    // 验证 ICP 号码的有效性
    if (validate_icp_number($icp_number)) {
        // 重定向到成功页面或其他适当的页面
        header('Location: /reg?icp_number='.$icp_number);
        exit();
    } else {
        // 显示错误消息或进行其他适当的处理
        $error_message = '无效的 ICP 号码，或此号码已被使用';
    }
}

if (isset($error_message)) {
    echo '<script>alert("'.$error_message.'");</script>';
    header('Location: /join'); // 重定向清除POST参数
    exit;
}

$twig = initTwig();
echo $twig->render('@index/join.html.twig', get_Page_vars());