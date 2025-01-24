<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
$twig = initTwig();
// 渲染404模板
echo $twig->render('404.html.twig', [
// 这里可以传递变量给模板
]);