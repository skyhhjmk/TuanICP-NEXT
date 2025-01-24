<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
function login($username, $password)
{
    $dbc = initDatabase();
    $query = "SELECT password FROM users WHERE username = :username";
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result){
        if (password_verify($password, $result['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true;
            header('Location: index.php');
            exit();
        } else {
            header('Location: login.php?error=1');
            exit();
        }
    }
}