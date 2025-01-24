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
 * @return Pool
 */
function initCache()
{
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();

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
            $password = $_ENV['REDIS_PASSWORD'] ?? ''; // 获取环境变量中的密码，如果没有设置则为空字符串
            $redisOptions = array(
                'servers' => array(array(
                    'server' => $server,
                    'port' => $port,
                    'password' => $password // 添加密码选项
                ))
            );
            // 如果密码不为空，则设置密码
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
        $pool = new Pool($driver);
        return $pool;
    } else {
        output_error('缓存驱动初始化失败。');
    }

    return null;
}

