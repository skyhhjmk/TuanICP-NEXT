<?php


if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

function register($username,$email, $password) {
    $pdo = initDatabase();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt =$pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email,$hashed_password]);
    return $pdo->lastInsertId();
}


function login($username,$password, $remember = false) {
    $pdo = initDatabase();
    $stmt =$pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
    $stmt->execute([$username]);
    $user =$stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $expiration =$remember ? time() + 30 * DAY_IN_SECONDS : 0;
        $cookie = generate_auth_cookie($user['user_id'], $expiration);
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

    if ($expiration < time() && $expiration != 0) {
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
