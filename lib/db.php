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
    define('DB_TYPE', $DB_TYPE);

    switch ($DB_TYPE) {
        case 'mysql':
            $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
            $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
            break;
        case 'sqlite':
            $dotenv->required(['DB_PATH']);
            $path = APP_ROOT . '/' . $_ENV['DB_PATH'];
            $dsn = "sqlite:$path";
            $user = null;
            $pass = null;
            break;
        case 'sqlsrv':
            $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
            $dsn = "sqlsrv:Server={$_ENV['DB_HOST']};Database={$_ENV['DB_NAME']}";
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
            break;
        case 'oci':
            $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
            $cs = "(DESCRIPTION =(ADDRESS = (PROTOCOL = TCP)(HOST = {$_ENV['DB_HOST']})(PORT = 1521))(CONNECT_DATA =(SERVER = DEDICATED)(SERVICE_NAME = {$_ENV['DB_NAME']})))";
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
            $dsn = "oci:dbname=" . $cs;
            break;
        default:
            output_error('不支持的数据库类型: ' . $DB_TYPE);
    }

    if (in_array($DB_TYPE, ['mysql', 'sqlite', 'sqlsrv', 'oci'])) {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            if ($DB_TYPE === 'oci' || $DB_TYPE === 'sqlsrv') {
// 使用 eftec\PdoOne 来连接 Oracle 或 SQL Server
                $dao = new PdoOne($DB_TYPE, $dsn, $user, $pass);
                $dao->connect();
                return $dao->pdo; // 返回 PDO 实例
            } else {
// 使用 PDO 来连接 MySQL 或 SQLite
                $pdo = new PDO($dsn, $user, $pass, $options);
                return $pdo; // 返回 PDO 实例
            }
        } catch (PDOException $e) {
            output_error("数据库连接失败: ", $e->getMessage());
        }
    } else {
        output_error('不支持的数据库类型: ' . $DB_TYPE);
    }
    return null;
}