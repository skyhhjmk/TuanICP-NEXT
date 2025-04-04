<?php


/*
* Name:        插件名称
* Description:        插件描述
* Version:            插件版本
* Author:             插件作者
*/
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

function get_plugin_info($plugin_file)
{
    // 确保文件存在
    if (!file_exists($plugin_file)) {
        return false;
    }

    // 读取文件内容
    $plugin_data = file($plugin_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $plugin_info = array();
    $in_header = false; // 初始化 $in_header 变量
    $header_ended = false; // 标记头部注释是否已结束

    // 遍历文件的每一行，匹配头部注释
    foreach ($plugin_data as $line) {
//        echo "Processing line: " . $line . PHP_EOL;
        if (!$header_ended && strpos($line, '/*') !== false) {
            // 头部注释开始
            $in_header = true;
//            echo "Header comment started." . PHP_EOL;
        } elseif ($in_header && strpos($line, '*/') !== false) {
            // 头部注释结束
            $in_header = false;
            $header_ended = true;
//            echo "Header comment ended." . PHP_EOL;
        } elseif ($in_header && preg_match('/^\s*\*\s*(.*)$/', $line, $matches)) {
            // 匹配注释行，允许注释行前有任意数量的空格
            $line = trim($matches[1]);
//            echo "Matched comment line: " . $line . PHP_EOL;
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // 规范化键名，去除多余空格并转换为小写
                $normalized_key = strtolower(str_replace(' ', '', $key));

                // 将信息存储到数组中
                $plugin_info[$normalized_key] = $value;
            }
        }

        // 如果头部注释已经结束，退出循环
        if ($header_ended) {
            break;
        }
    }
    return $plugin_info;
}

function get_all_plugins(): array
{
    // 初始化一个空数组来存储插件信息
    $all_plugins = [];

    // 检查目录是否存在
    if (is_dir(TUANICP_PLUGIN_DIR)) {
        // 尝试打开目录
        $dir = @opendir(TUANICP_PLUGIN_DIR); // 使用 @ 来抑制错误
        if ($dir === false) {
            output_error("无法打开插件目录: ", TUANICP_PLUGIN_DIR . PHP_EOL);
            return $all_plugins; // 返回空数组
        }

        // 循环读取目录下的所有条目
        while (($subdir = readdir($dir)) !== false) {
            // 跳过'.'和'..'这两个特殊的目录
            if ($subdir != "." && $subdir != "..") {
                // 检查是否为目录
                $plugin_dir = TUANICP_PLUGIN_DIR . '/' . $subdir;
                if (is_dir($plugin_dir)) {
                    // 构建插件信息文件路径
                    $plugin_info_file = $plugin_dir . '/main.php';

                    // 获取插件信息
                    $plugin_info = get_plugin_info($plugin_info_file);

                    if ($plugin_info) {
                        // 构建插件对象
                        $plugin = [
                            "plugin_name" => $plugin_info['name'] ?? '',
                            "plugin_info" => $plugin_info['description'] ?? '',
                            "plugin_version" => $plugin_info['version'] ?? '',
                            "plugin_author" => $plugin_info['author'] ?? '',
                            "plugin_entry" => $plugin_info_file,
                            "plugin_conflicts" => $plugin_info['conflicts'] ?? '',
                            "plugin_dependencies" => $plugin_info['dependencies'] ?? '',
                            "is_active" => is_plugin_active($plugin_info_file)
                        ];
                        // 将插件对象添加到数组中
                        $all_plugins[] = $plugin;
                    } else {
                        output_error("无法获取插件信息: ", $plugin_info_file . PHP_EOL);
                    }
                }
            }
        }
        // 关闭目录
        closedir($dir);
    } else {
        output_error("插件目录不存在: ", TUANICP_PLUGIN_DIR . PHP_EOL);
    }
//var_dump($all_plugins);
    return $all_plugins;
}


function is_plugin_active($plugin_file): bool
{
    // 获取当前所有启用的插件
    $activePlugins = get_active_plugins();

    // 遍历插件数组，检查是否存在指定的插件入口文件
    foreach ($activePlugins as $plugin) {
        if ($plugin->file == $plugin_file) {
            // 如果找到匹配的插件入口文件，返回 true 表示插件已激活
            return true;
        }
    }

    // 如果没有找到匹配的插件入口文件，返回 false 表示插件未激活
    return false;
}

function get_active_plugins(): array
{
    // 初始化缓存池，如果缓存被禁用，则 $cachePool 为 null
    $cachePool = initCache();

    // 定义缓存项的键
    $cacheKey = 'active_plugins';

    // 如果缓存池不为 null，尝试从缓存中获取数据
    if ($cachePool !== null) {
        $item = $cachePool->getItem($cacheKey);
        if ($item->isHit()) {
            // 如果缓存命中，直接返回缓存的数据
            return $item->get();
        }
    }

    // 初始化数据库连接
    $pdo = initDatabase();
    $sql = "SELECT `v` FROM config WHERE `k` = 'active_plugins'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result || empty($result['v'])) {
        // 如果查询结果为空或值为空，返回空数组
        return array();
    }

    // 反序列化插件信息
    $activePlugins = @unserialize($result['v']);

    if (!is_array($activePlugins)) {
        // 如果反序列化失败或结果不是数组，返回空数组
        return array();
    }

    // 如果缓存池不为 null，将结果保存到缓存中，并设置缓存过期时间（例如 3600 秒）
    if ($cachePool !== null) {
        $item->set($activePlugins);
        $cachePool->save($item);
    }

    return $activePlugins;
}


function is_plugin_active_by_name($plugin_name) {
    $activePlugins = get_active_plugins();
    foreach ($activePlugins as$plugin) {
        if ($plugin->name ==$plugin_name) {
            return true;
        }
    }
    return false;
}

function activate_plugin($plugin_name,$plugin_file) {
    // 初始化缓存池
    $cachePool = initCache();

    // 定义缓存项的键
    $cacheKey = 'active_plugins';

    // 如果缓存池不为 null，尝试清除缓存项
    if ($cachePool !== null) {
        // 创建一个缓存项
        $item =$cachePool->getItem($cacheKey);

        // 清除缓存项
        $cachePool->clear($cacheKey);
    }

    $pdo = initDatabase();
    // 获取当前所有启用的插件
    $activePlugins = get_active_plugins();

    // 检查是否有相同的插件名或入口文件
    foreach ($activePlugins as$plugin) {
        if ($plugin->name ==$plugin_name || $plugin->file ==$plugin_file) {
            // 如果存在相同的插件名或入口文件，返回失败
            return false;
        }
    }

    // 获取插件信息
    $plugin_info = get_plugin_info($plugin_file);

    if (isset($plugin_info['dependencies'])) {
        $dependencies = array_map('trim', explode(',',$plugin_info['dependencies']));
        foreach ($dependencies as$dependency) {
            if (!is_plugin_active_by_name($dependency)) {
                // 如果依赖的插件未激活，返回失败
                return false;
            }
        }
    }

    // 检查插件冲突
    if (isset($plugin_info['conflicts'])) {
        $conflicts = array_map('trim', explode(',',$plugin_info['conflicts']));
        foreach ($conflicts as$conflict) {
            if (is_plugin_active_by_name($conflict)) {
                // 如果存在冲突的插件已激活，返回失败
                return false;
            }
        }
    }

    // 添加新插件到数组，存储为对象
    $activePlugins[] = (object)['name' =>$plugin_name, 'file' => $plugin_file];

    // 序列化插件信息数组
    $serialized_plugin_info = serialize($activePlugins);

    // 准备SQL语句
    $sql = "INSERT INTO config (`k`, `v`) VALUES ('active_plugins', :v) ON DUPLICATE KEY UPDATE `v` = :v";
    $stmt =$pdo->prepare($sql);

    // 绑定参数
    $stmt->bindParam(':v',$serialized_plugin_info);

    // 执行语句
    $stmt->execute();

    // 返回成功
    return true;
}


function deactivate_plugin($plugin_name, $plugin_file)
{
    // 初始化缓存池
    $cachePool = initCache();

// 定义缓存项的键
    $cacheKey = 'active_plugins';

// 如果缓存池不为 null，尝试清除缓存项
    if ($cachePool !== null) {
        // 创建一个缓存项
        $item = $cachePool->getItem($cacheKey);

        // 清除缓存项
        $cachePool->clear($cacheKey);
    }

    $pdo = initDatabase();
    // 获取当前所有启用的插件
    $activePlugins = get_active_plugins();

    // 查找并移除指定的插件
    $found = false;
    foreach ($activePlugins as $key => $plugin) {
        if ($plugin->name == $plugin_name && $plugin->file == $plugin_file) {
            // 找到插件，从数组中移除
            unset($activePlugins[$key]);
            $found = true;
            break;
        }
    }

    if (!$found) {
        // 如果没有找到指定的插件，返回失败
        return false;
    }

    // 重新索引数组
    $activePlugins = array_values($activePlugins);

    // 检查是否有其他插件依赖于被停用的插件
    foreach ($activePlugins as $plugin) {
        $plugin_info = get_plugin_info($plugin->file);
        if (isset($plugin_info['dependencies'])) {
            foreach ($plugin_info['dependencies'] as $dependency) {
                if ($dependency == $plugin_name) {
                    // 如果找到依赖于被停用插件的插件，递归停用该插件
                    deactivate_plugin($plugin->name, $plugin->file);
                }
            }
        }
    }

    // 序列化插件信息数组
    $serialized_plugin_info = serialize($activePlugins);

    // 准备SQL语句
    $sql = "INSERT INTO config (`k`, `v`) VALUES ('active_plugins', :v) ON DUPLICATE KEY UPDATE `v` = :v";
    $stmt = $pdo->prepare($sql);

    // 绑定参数
    $stmt->bindParam(':v', $serialized_plugin_info);

    // 执行语句
    $stmt->execute();

    // 返回成功
    return true;
}

function load_plugins(): void
{
    $active_plugins = get_active_plugins();

    foreach ($active_plugins as $plugin) {
        // 获取插件的文件路径
        $plugin_file = $plugin->file;

        // 确保 $plugin_file 是一个有效的字符串路径
        if (is_string($plugin_file) && file_exists($plugin_file)) {

            // 获取插件信息
            $pluginInfo = get_plugin_info($plugin_file);

            // 检查插件是否包含最基本的注释信息
            if (!isset($pluginInfo['name']) || !isset($pluginInfo['description']) || !isset($pluginInfo['version']) || !isset($pluginInfo['author'])) {
                continue; // 跳过本次循环，进行下一轮循环，即跳过该插件的加载
            }

            // 如果没有 conflicts，定义为 ''
            if (!isset($pluginInfo['conflicts'])) {
                $pluginInfo['conflicts'] = '';
            }

            // 如果没有 dependencies，定义为 ''
            if (!isset($pluginInfo['dependencies'])) {
                $pluginInfo['dependencies'] = '';
            }
            try {
                // 尝试加载插件文件
                include_once $plugin_file;
            } catch (Exception $e) {
                // 捕获并处理加载插件时的异常
                output_error("Error loading plugin {$plugin->name}: ", $e->getMessage() . PHP_EOL);
            }
        }
    }
}
