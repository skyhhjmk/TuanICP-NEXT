<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];
    $remember = isset($_POST['remember']);
    if (login($input_username, $input_password, $remember)) {
        $data = [
            'code' => 0,
            'message' => '登录成功',
            'status' => 'success'
        ];
        echo json_encode($data);
        exit;
    } else {
        $data = [
            'code' => 1,
            'message' => '登录失败',
            'status' => 'failed'
        ];
        echo json_encode($data);
        exit;
    }
}

$twig = initTwig();

echo $twig->render('@admin/login.html.twig', get_Page_vars());