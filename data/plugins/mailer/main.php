<?php
/*
* Name:        邮件扩展
* Description:        此插件支持使用SMTP发信，替代核心默认的mail()函数发信
* Version:            1.0
* Author:             风屿Wind
*/

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';


function ces_phpmailer_send($tag,$to, $subject, $message)
{
    // 引入 PHPMailer 类

    $mail = new PHPMailer(true);
    // 设置 PHPMailer 参数
    $mail->isSMTP(); // 使用 SMTP
    $mail->Host = 'smtp.qiye.aliyun.com';
    $mail->SMTPAuth = true;
    $mail->Username = '';
    $mail->Password = '';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('', '');
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->Body = $message;

    return $mail->send();
}


remove_filter('core_sendmail', 'default_core_sendmail');
add_filter('core_sendmail', 'ces_phpmailer_send', 10, 4);
