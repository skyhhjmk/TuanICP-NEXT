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
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
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
            } else {
                output_error('APCu 扩展未加载。');
            }
            break;
        case 'file':
            $path = APP_ROOT . '/cache';
            $driver = new FileSystem(array('path' => $path));
            break;
        case 'memcache':
            $dotenv->required(['MEMCACHE_SERVER']);
            $server = $_ENV['MEMCACHE_SERVER'];
            $driver = new Memcache(array('servers' => array($server)));
            break;
        case 'redis':
            $dotenv->required(['REDIS_SERVER', 'REDIS_PORT']);
            $server = $_ENV['REDIS_SERVER'];
            $port = $_ENV['REDIS_PORT'];
            $password = $_ENV['REDIS_PASSWORD'] ?? '';
            $redisOptions = array(
                'servers' => array(array(
                    'server' => $server,
                    'port' => $port,
                    'password' => $password
                ))
            );
            if (!empty($password)) {
                $redisOptions['servers'][0]['password'] = $password;
            }
            $driver = new Redis($redisOptions);
            break;
        case 'ephemeral':
            $driver = new Ephemeral();
            break;
        default:
            output_error('不支持的缓存驱动: ' . $CACHE_TYPE);
    }

    if ($driver !== null) {
        // 设置全局过期时间，例如 3600 秒（1 小时）
        $pool = new Pool($driver, ['ttl' => 3600]);
        return $pool;
    } else {
        output_error('缓存驱动初始化失败。');
    }

    return null;
}

?>
