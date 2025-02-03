<?php
// core_sendmail.php

function core_sendmail($to, $subject, $message)
{
    // 使用 apply_filters 来发送邮件，允许插件覆盖默认行为
    return apply_filters('core_sendmail', false, $to, $subject, $message);
}

// 默认的邮件发送方式（使用 PHP 的 mail 函数）
function default_core_sendmail($to, $subject, $message)
{
        $headers = "From: webmaster@example.com\r\n";
        return mail($to, $subject, $message, $headers);

}

// 注册默认的邮件发送方式
add_filter('core_sendmail', 'default_core_sendmail', 10, 3);

add_action('send_mail', 'core_sendmail', 10, 3);