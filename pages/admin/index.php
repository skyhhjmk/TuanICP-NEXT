<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
$twig = initTwig();
// 渲染404模板
echo $twig->render('@admin/index.html.twig', get_Page_vars());