<?php


if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

// Twig模板引擎变量赋值
$add_twigVariables = [
//    'plugins' => $remotePlugins
];



function help_me_document_page_vars($routes) {
    global $add_twigVariables;
    $routes = array_merge($routes, $add_twigVariables); // 合并两个数组
    return $routes;
}

// 添加过滤器
add_filter('page_vars', 'help_me_document_page_vars');

$twig = initTwig();
echo $twig->render('@admin/document.html.twig', get_Page_vars());