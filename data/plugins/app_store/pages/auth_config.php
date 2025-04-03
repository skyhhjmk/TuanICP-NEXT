<?php



if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

$user_role = get_current_user_role();
if (!$user_role){
    header('Location: '. get_Url('admin/login'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth_user = $_POST['auth_user'];
    $auth_passwd = $_POST['auth_passwd'];
    $auth_card_key = $_POST['auth_card_key'];

    set_Config('auth_card_key', $auth_card_key);
    set_Config('auth_user', $auth_user);
    set_Config('auth_passwd', $auth_passwd);

    header('Location: '. get_Url('admin/auth_config'));
}

$twig = initTwig();
echo $twig->render('@admin/auth_config.html.twig', get_Page_vars());