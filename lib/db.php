<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

use eftec\PdoOne;

/**
 * @return PDO|null
 * @throws JsonException
 */
function initDatabase()
{
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();

    $dotenv->required('DB_TYPE')->notEmpty();
    $DB_TYPE = $_ENV['DB_TYPE'];

    switch ($DB_TYPE) {
        case 'mysql':
            $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
            $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
            break;
        default:
            output_error('不支持的数据库类型: ' . $DB_TYPE);
    }

    if ($DB_TYPE == 'mysql') {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
                $pdo = new PDO($dsn, $user, $pass, $options);
                return $pdo; // 返回 PDO 实例
        } catch (PDOException $e) {
            output_error("数据库连接失败: ", $e->getMessage());
        }
    } else {
        output_error('不支持的数据库类型: ' . $DB_TYPE);
    }
    return null;
}