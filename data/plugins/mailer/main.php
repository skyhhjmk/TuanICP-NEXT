<?php
// custom_email_sender.php

function custom_email_sender($override,$to, $subject,$message) {
    // 引入 PHPMailer 类
    require_once 'path/to/PHPMailerAutoload.php';

    $mail = new PHPMailer;

    // 设置 PHPMailer 参数
    // ...

    $mail->setFrom('from@example.com', 'Mailer');
    $mail->addAddress($to);
    $mail->Subject =$subject;
    $mail->Body    =$message;

    // 发送邮件并返回结果
    return $mail->send();
}

// 注册插件
function activate_custom_email_sender() {
    add_filter('sendmail', 'custom_email_sender', 20, 4);
}

// 注销插件
function deactivate_custom_email_sender() {
    remove_filter('sendmail', 'custom_email_sender', 20);
}

// 激活插件
activate_custom_email_sender();

// 如果需要卸载插件，可以调用
// deactivate_custom_email_sender();
