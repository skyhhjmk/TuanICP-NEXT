<?php

if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

use Stash\Driver\Apcu;
use Stash\Driver\Ephemeral;
use Stash\Driver\FileSystem;
use Stash\Driver\Memcache;
use Stash\Driver\Redis;
use Stash\Pool;

/**
 * @return Pool|null
 */
function initCache(): ?Pool
{
    $dotenv = Dotenv\Dotenv::createImmutable(DATA_ROOT);
    $dotenv->load();

    // 检查是否启用了缓存
    $CACHE_ENABLED = $_ENV['CACHE_ENABLED'] ?? true;
    if (!$CACHE_ENABLED) {
        // 如果缓存被禁用，则返回 null 或一个不执行任何操作的缓存池
        return null; // 或者 return new Ephemeral(); 如果你想返回一个不执行任何操作的缓存池
    }

    $dotenv->required('CACHE_TYPE')->notEmpty();
    $CACHE_TYPE = $_ENV['CACHE_TYPE'];

    $driver = null;

    switch ($CACHE_TYPE) {
        case 'apcu':
            if (extension_loaded('apcu')) {
                $driver = new Apcu();
                $driver->setOptions(['ttl' => 3600, 'namespace' => md5(__FILE__)]);
            } else {
                output_error('APCu 扩展未加载。');
            }
            break;
        case 'file':
            $path = APP_ROOT . '/cache';
            $driver = new FileSystem(['path' => $path]);
            break;
        case 'memcache':
        case 'memcached':
            if (extension_loaded('memcached') || extension_loaded('memcache')) {
                $dotenv->required(['MEMCACHED_SERVER', 'MEMCACHED_PORT']);
                $server = $_ENV['MEMCACHED_SERVER'] ?? '127.0.0.1';
                $port = $_ENV['MEMCACHED_PORT'] ?? 11211; // 默认端口 11211
                $driver = new Memcache(['servers' => [['server' => $server, 'port' => $port]]]);
            } else {
                output_error('Memcache(d) 扩展未加载。');
            }
            break;
        case 'redis':
            $dotenv->required(['REDIS_SERVER', 'REDIS_PORT', 'REDIS_PASSWORD', 'REDIS_DB']);
            $server = $_ENV['REDIS_SERVER'] ?? '127.0.0.1';
            $port = $_ENV['REDIS_PORT'] ?? 6379;
            $password = $_ENV['REDIS_PASSWORD'] ?? null;
            $databaseIndex = $_ENV['REDIS_DB'] ?? null; // 支持设置数据库编号

            $redisOptions = [
                'servers' => [[
                    'server' => $server,
                    'port' => $port,
                    'password' => $password, // 如果密码为空，则不会设置密码字段
                    'database' => $databaseIndex, // 如果设置了数据库编号，则使用该数据库
                ]]
            ];

            // 如果密码为空，则从配置中移除
            if (empty($password)) {
                unset($redisOptions['servers'][0]['password']);
            }

            // 如果数据库编号未设置，则从配置中移除
            if ($databaseIndex === null) {
                unset($redisOptions['servers'][0]['database']);
            }

            $driver = new Redis($redisOptions);
            break;

        case 'ephemeral':
        default:
            $driver = new Ephemeral();
            break;
    }

    if ($driver !== null) {
        // 设置全局过期时间，例如 3600 秒（1 小时）
        $pool = new Pool($driver);
        return $pool;
    } else {
        output_error('缓存驱动初始化失败。');
    }

    return null;
}

?>
