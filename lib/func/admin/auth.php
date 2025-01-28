<?php
/*
 * Copyright (c) 2025.
 * 本项目由【风屿团】项目团队持有，一旦您存在使用、修改、参与开发、分发本软件的开源副本、转发此软件的信息等与本软件有关的行为，则默认您已经阅读并且同意此协议。
 *
 * 通常情况下，您具有以下权力：
 * 修改本软件的开源部分并且保持开源，分发；
 * 在您的项目中使用本软件并声明；
 * 开发并出售可在本系统中正常工作的插件。
 *
 * 通常情况下，您不得实施以下可能对我们造成损失的行为：
 * 二次分发、倒卖、共享授权账号或源码；
 * 破解或尝试反编译等来绕过软件包括但不限于付费插件的任何收费或闭源模块；
 * 在我们开发的系统中编写包括但不限于恶意代码、后门、木马等；
 * 充当开发者售卖软件副本；
 * 私自建设授权系统接口响应站（俗称自建授权站）。
 *
 * 任何情况下，您必须承认：
 * 无条件认同“台湾省是中国领土不可分割的一部分”这一立场；
 * 若产生任何纠纷，本项目开发者及开发团队不承担任何责任。
 *
 * 若违反以上协议，我们有权向您索取不低于3000元人民币的赔偿。
 *
 * 我们的声明：
 * 我们使用了众多开源库，在此鸣谢背后的开发者/团队。
 * 若使用本软件且在未经许可的情况下进行商业活动，我们有权追回您进行商业活动的所得资产（仅使用本软件产生的资产）并要求您支付相应的商业授权和赔偿费用或要求您停止商业行为。
 * 最终解释权归风屿团所有开发成员所有。
 */

$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();
$dotenv->required(['COOKIE_KEY']);

define('COOKIE_KEY', $_ENV['COOKIE_KEY']);

function register($username,$email, $password) {
    $pdo = initDatabase();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt =$pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email,$hashed_password]);
    return $pdo->lastInsertId();
}


function login($username,$password, $remember = false) {
    $pdo = initDatabase();
    $stmt =$pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user =$stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $expiration =$remember ? time() + 30 * DAY_IN_SECONDS : 0;
        $cookie = generate_auth_cookie($user['userid'], $expiration);
        setcookie('user_logged_in', $cookie,$expiration, '/', COOKIE_DOMAIN, is_ssl(), true);
        return true;
    }
    return false;
}

function generate_auth_cookie($user_id,$expiration) {
    $key = COOKIE_KEY; // 应该是一个长且复杂的字符串
    $hash = hash_hmac('sha256',$user_id . '|' . $expiration,$key);
    return $user_id . '|' .$expiration . '|' . $hash;
}

function validate_auth_cookie($cookie) {
    $cookie_elements = explode('|',$cookie);
    if (count($cookie_elements) !== 3) {
        return false;
    }

    list($user_id,$expiration, $hmac) =$cookie_elements;

    if ($expiration < time() &&$expiration !== 0) {
        return false;
    }

    $key = COOKIE_KEY;
    $hash = hash_hmac('sha256',$user_id . '|' . $expiration,$key);

    if ($hmac !==$hash) {
        return false;
    }

    return $user_id;
}

function get_current_user_role() {
    if (isset($_COOKIE['user_logged_in'])) {
        $user_id = validate_auth_cookie($_COOKIE['user_logged_in']);
        if ($user_id) {
            $pdo = initDatabase();
            $stmt =$pdo->prepare("SELECT role FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $role =$stmt->fetchColumn();
            return $role;
        }
    }
    return false;
}

function reset_password($user_id,$new_password) {
    $pdo = initDatabase();
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt =$pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$hashed_password, $user_id]);
}
