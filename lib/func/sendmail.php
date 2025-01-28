<?php
// core_sendmail.php

function core_sendmail($to, $subject, $message)
{
    // 使用 apply_filters 来发送邮件，允许插件覆盖默认行为
    return apply_filters('core_sendmail', false, $to, $subject, $message);
}

// 默认的邮件发送方式（使用 PHP 的 mail 函数）
function default_core_sendmail($override, $to, $subject, $message)
{
    if ($override === false) {
        $headers = "From: webmaster@example.com\r\n";
        return mail($to, $subject, $message, $headers);
    }
    return $override; // 如果插件覆盖了发送行为，则返回插件的返回值
}

// 注册默认的邮件发送方式
add_filter('core_sendmail', 'default_core_sendmail', 10, 4);
//var_dump($GLOBALS['tuanicp_filters'], $GLOBALS['tuanicp_actions']);
add_action('send_mail', 'core_sendmail', 10, 3);