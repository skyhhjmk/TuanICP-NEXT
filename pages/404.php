<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
$twig = initTwig();
// 渲染404模板
echo $twig->render('@index/404.html.twig', get_Page_vars());