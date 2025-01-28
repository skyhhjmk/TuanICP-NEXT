<?php
/*
* Name:        邮件扩展
* Description:        此插件支持使用SMTP发信，替代核心默认的mail()函数发信
* Version:            1.0
* Author:             风屿Wind
*/

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';

function ces_phpmailer_send($args)
{
    // 引入 PHPMailer 类

    $mail = new PHPMailer;

    // 设置 PHPMailer 参数
    $mail->isSMTP(); // 使用 SMTP
    $mail->Host = 'smtp.qiye.aliyun.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'admin@notify.biliwind.com';
    $mail->Password = 'rmILuSMZ2jA966pm';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('admin@notify.biliwind.com', 'Mailer');
    $mail->addAddress($args['to'], 'Recipient Name');
    $mail->Subject = $args['subject'];
    $mail->Body = $args['message'];

    return $mail->send();
}

// 注册插件
function ces_register_plugin()
{
    add_filter('core_sendmail', 'ces_phpmailer_send', 10, 1);
}

// 注销插件
function ces_unregister_plugin()
{
    remove_filter('core_sendmail', 'ces_phpmailer_send', 10);
}

// 激活插件
add_action('load_plugin', 'ces_register_plugin');

// 卸载插件
add_action('unload_plugin', 'ces_register_plugin');
