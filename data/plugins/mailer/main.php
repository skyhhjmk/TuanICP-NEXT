<?php
// custom_email_sender.php

require_once 'send_mail.php';

function ces_phpmailer_send($args) {
    // 引入 PHPMailer 类
    require_once 'path/to/PHPMailerAutoload.php';

    $mail = new PHPMailer;

    // 设置 PHPMailer 参数
    $mail->isSMTP(); // 使用 SMTP
    $mail->Host = 'smtp.example.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your_smtp_username';
    $mail->Password = 'your_smtp_password';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('from@example.com', 'Mailer');
    $mail->addAddress($args['to'], 'Recipient Name');
    $mail->Subject =$args['subject'];
    $mail->Body    =$args['message'];

    return $mail->send();
}

// 注册插件
function ces_register_plugin() {
    add_filter('send_mail', 'ces_phpmailer_send', 10, 1);
}

// 注销插件
function ces_unregister_plugin() {
    remove_filter('send_mail', 'ces_phpmailer_send', 10);
}

// 激活插件
ces_register_plugin();

// 如果需要卸载插件，可以调用
// ces_unregister_plugin();
