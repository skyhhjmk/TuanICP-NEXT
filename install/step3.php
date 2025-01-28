<?php
if (file_exists('install.lock')){
    header('Location: /');
    exit();
}
if (file_exists('install_cache.lock')){
    header('Location: step4.php');
    exit();
}
use Stash\Driver\Apcu;
use Stash\Driver\Ephemeral;
use Stash\Driver\FileSystem;
use Stash\Driver\Memcache;
use Stash\Driver\Redis;

require_once __DIR__ . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取表单数据
    $cache_enabled = $_POST['CACHE_ENABLED'] ?? true;
    $cache_type = $_POST['cache_type'] ?? 'ephemeral';
    $redis_server = $_POST['redis_host'] ?? '127.0.0.1';
    $redis_port = $_POST['redis_port'] ?? 6379;
    $redis_password = $_POST['redis_password'] ?? null;
    $redis_db = $_POST['redis_db'] ?? 0;
    $memcache_server = $_POST['memcached_host'] ?? '127.0.0.1';
    $memcache_port = $_POST['memcached_port'] ?? 11211;

    // 创建一个Stash Pool
    $pool = null;
    $tmp_key = md5($_SERVER['DOCUMENT_ROOT']);
    file_put_contents('tmp_key', $tmp_key);
    $info_msg = "<p class='success'> " . $cache_type . "缓存配置成功，安装阶段结束，默认的用户名为admin，密码为admin，请登录后修改密码。</p>";

    try {
        $driver = null;

        switch ($cache_type) {
            case 'apcu':
                if (extension_loaded('apcu')) {
                    $driver = new Apcu();
                    $driver->setOptions(['ttl' => 3600, 'namespace' => md5(__FILE__)]);
                } else {
                    $info_msg = "<p class='failure'>APCu 扩展未加载。</p>";
                }
                break;
            case 'file':
                $path = '../cache';
                $driver = new FileSystem(['path' => $path]);
                break;
            case 'memcache':
            case 'memcached':
                if (extension_loaded('memcached') || extension_loaded('memcache')) {
                    $driver = new Memcache(['servers' => [['server' => $memcache_server, 'port' => $memcache_port]]]);
                } else {
                    $info_msg = "<p class='failure'>Memcache(d) 扩展未加载。</p>";
                }
                break;
            case 'redis':
                $redisOptions = [
                    'servers' => [[
                        'server' => $redis_server,
                        'port' => $redis_port,
                        'password' => $redis_password, // 如果密码为空，则不会设置密码字段
                        'database' => $redis_db, // 如果设置了数据库编号，则使用该数据库
                    ]]
                ];

                // 如果密码为空，则从配置中移除
                if (empty($password)) {
                    unset($redisOptions['servers'][0]['password']);
                }

                // 如果数据库编号未设置，则从配置中移除
                if ($redis_db === null) {
                    unset($redisOptions['servers'][0]['database']);
                }

                if (extension_loaded('redis')) {
                    $driver = new Redis($redisOptions);
                } else {
                    $info_msg = "<p class='failure'>Redis 扩展未加载。</p>";
                }
                break;

            case 'ephemeral':
            default:
                $driver = new Ephemeral();
                break;
        }

        // 创建缓存池
        $pool = new Stash\Pool($driver);
        // 尝试存储一个值来测试缓存是否工作
        $item = $pool->getItem('test');
        $item->set('Cache is working');
        $pool->save($item);

        // 检查缓存是否设置成功
        if ($item->get() != 'Cache is working') {
            throw new Exception('缓存测试失败');
        }
    } catch (Exception $e) {
        $info_msg = "<p class='failure'>" . $cache_type . "缓存配置失败: " . $e->getMessage() . "</p>";
    }

    // 确定缓存配置成功后，追加配置信息到.env文件
    if ($pool && $info_msg == "<p class='success'> " . $cache_type . "缓存配置成功</p>") {
        $envFilePath = __DIR__ . '/../.env'; // 假设.env文件位于当前脚本上一级目录

        // 构建要追加的配置信息
        $envConfig = "
# 缓存配置
CACHE_ENABLED=true
CACHE_TYPE=" . $cache_type . "# redis,memcached,file,apcu,ephemeral
REDIS_HOST=" . $redis_server . "
REDIS_PORT=" . $redis_port . "
REDIS_PASSWORD=" . ($redis_password ? $redis_password: '#REDIS_PASSWORD=123#连接Redis的密码') . "
REDIS_DB=" . $redis_db . "# 连接Redis的数据库
MEMCACHED_HOST=" . $memcache_server . "# memcached模式下生效
MEMCACHED_PORT=" . $memcache_port . "# memcached模式下生效
# 伪静态开关，配置伪静态后设置为true，否则设置为false
REWRITE=true";

        // 追加配置信息到.env文件
        try {
            // 打开文件，追加内容
            file_put_contents($envFilePath, $envConfig, FILE_APPEND | LOCK_EX);
            file_put_contents('install_cache.lock', '');
        } catch (Exception $e) {
            $info_msg = "<p class='failure'>追加配置到.env文件失败: " . $e->getMessage() . "</p>";
        }
    }
}


// HTML 输出
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TuanICP-NEXT安装向导 - 缓存配置</title>
    <style>
        @keyframes rainbow-text-animation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .rainbow-text {
            font-size: 48px;
            font-weight: bold;
            background: linear-gradient(270deg, red, orange, yellow, green, blue, indigo, violet);
            background-size: 600% 600%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent; /* Fallback for browsers that don't support gradients */
            animation: rainbow-text-animation 6s ease-in-out infinite;
        }

        .success {
            color: green;
        }

        .failure {
            color: red;
        }

        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        input[type="submit"] {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class='container'>
    <h1>TuanICP<span class="rainbow-text">NEXT</span>安装向导 - 缓存配置</h1>
    <form action="./step3.php" method="post">
        <label for="cache_type">缓存类型:</label>
        <select id="cache_type" name="cache_type" onchange="toggleOptions()">
            <option value="file">文件缓存</option>
            <option value="redis">Redis</option>
            <option value="memcached">Memcache(d)</option>
            <option value="apcu">APCu</option>
            <option value="ephemeral">Ephemeral</option>
        </select>

        <div id="redisOptions" style="display: none">
            <label for="redis_host">Redis 地址:</label>
            <input type="text" id="redis_host" name="redis_host" value="127.0.0.1">

            <label for="redis_port">Redis 端口:</label>
            <input type="text" id="redis_port" name="redis_port" value="6379">

            <label for="redis_password">Redis 密码:</label>
            <input type="password" id="redis_password" name="redis_password">

            <label for="redis_db">数据库 ID:</label>
            <input type="text" id="redis_db" name="redis_db" value="0">
        </div>

        <div id="memcachedOptions" style="display: none">
            <label for="memcached_host">Memcache(d) 地址:</label>
            <input type="text" id="memcached_host" name="memcached_host" value="127.0.0.1">

            <label for="memcached_port">Memcache(d) 端口:</label>
            <input type="text" id="memcached_port" name="memcached_port" value="11211">
        </div>

        <input type="submit" value="确认">
    </form>
    <?php
    if (isset($info_msg)) {
        echo $info_msg;
    }
    ?>
</div>
<script>
    function toggleOptions() {
        var cacheType = document.getElementById('cache_type').value;
        var redisOptions = document.getElementById('redisOptions');
        var memcachedOptions = document.getElementById('memcachedOptions');
        // 根据选择的数据库类型显示或隐藏相应的输入框
        switch (cacheType) {
            case 'memcached':
                memcachedOptions.style.display = 'block';
                redisOptions.style.display = 'none';
                break;
            case 'redis':
                redisOptions.style.display = 'block';
                memcachedOptions.style.display = 'none';
                break;
            // 其他数据库类型的逻辑可以在这里添加
            default:
                redisOptions.style.display = 'none';
                memcachedOptions.style.display = 'none';
        }
    }
</script>
</body>
</html>
