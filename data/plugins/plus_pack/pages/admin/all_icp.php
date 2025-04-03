<?php


if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

$user_role = get_current_user_role();
if (!$user_role){
    header('Location: '. get_Url('admin/login'));
}

$twig = initTwig();
echo $twig->render('@admin/all_icp.html.twig', get_Page_vars());