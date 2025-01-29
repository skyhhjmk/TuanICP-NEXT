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


function activate_plugin($plugin_name, $plugin_file)
{
    // 初始化缓存池，如果缓存被禁用，则 $cachePool 为 null
    $cachePool = initCache();

    // 定义要清除的缓存项的键
    $cacheKey = 'active_plugins';

    // 检查缓存池是否不为 null
    if ($cachePool !== null) {
        // 从缓存池中获取缓存项
        $item = $cachePool->getItem($cacheKey);
        // 清除缓存项
        $cachePool->deleteItem($cacheKey);
    }

    $pdo = initDatabase();
    // 获取当前所有启用的插件
    $activePlugins = get_active_plugins();

    // 检查是否有相同的插件名或入口文件
    foreach ($activePlugins as $plugin) {
        if ($plugin->name == $plugin_name || $plugin->file == $plugin_file) {
            // 如果存在相同的插件名或入口文件，返回失败
            return false;
        }
    }

    // 获取插件信息
    $plugin_info = get_plugin_info($plugin_file);
    if (isset($plugin_info['conflicts'])) {
        $conflicts = explode(',', $plugin_info['conflicts']);
        foreach ($conflicts as $conflict) {
            $conflict = trim($conflict);
            if (is_plugin_active($conflict)) {
                // 如果存在冲突的插件已激活，返回失败
                return false;
            }
        }
    }

    if (isset($plugin_info['dependencies'])) {
        $dependencies = explode(',', $plugin_info['dependencies']);
        foreach ($dependencies as $dependency) {
            $dependency = trim($dependency);
            if (!is_plugin_active($dependency)) {
                // 如果存在依赖的插件未激活，返回失败
                return false;
            }
        }
    }

    // 添加新插件到数组，存储为对象
    $activePlugins[] = (object)['name' => $plugin_name, 'file' => $plugin_file];

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

function deactivate_plugin($plugin_name, $plugin_file)
{
    // 初始化缓存池，如果缓存被禁用，则 $cachePool 为 null
    $cachePool = initCache();

    // 定义要清除的缓存项的键
    $cacheKey = 'active_plugins';

    // 检查缓存池是否不为 null
    if ($cachePool !== null) {
        // 从缓存池中获取缓存项
        $item = $cachePool->getItem($cacheKey);
        // 清除缓存项
        $cachePool->deleteItem($cacheKey);
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

function get_all_plugins(): array
{

    // 初始化一个空数组来存储插件信息
    $all_plugins = [];

    // 检查目录是否存在
    if (is_dir(TUANICP_PLUGIN_DIR)) {
        // 打开目录
        $dir = opendir(TUANICP_PLUGIN_DIR);
        // 循环读取目录下的所有条目
        while (($subdir = readdir($dir)) !== false) {
            // 跳过'.'和'..'这两个特殊的目录
            if ($subdir != "." && $subdir != "..") {
                // 检查是否为目录
                if (is_dir(TUANICP_PLUGIN_DIR . '/' . $subdir)) {
                    // 构建插件信息文件路径
                    $plugin_info_file = TUANICP_PLUGIN_DIR . '/' . $subdir . '/main.php';
//                    echo "Processing plugin info file: " . $plugin_info_file . PHP_EOL;

                    // 获取插件信息
                    $plugin_info = get_plugin_info($plugin_info_file);

                    if ($plugin_info) {
                        // 构建插件对象
                        $plugin = [
                            "plugin_name" => $plugin_info['name'] ?? '',
                            "plugin_info" => $plugin_info['description'] ?? '',
                            "plugin_version" => $plugin_info['version'] ?? '',
                            "plugin_author" => $plugin_info['author'] ?? '',
                            "plugin_entry" => $plugin_info_file, // 添加插件入口文件路径
                            "plugin_conflicts" => $plugin_info['conflicts'] ?? '',
                            "plugin_dependencies" => $plugin_info['dependencies'] ?? '',
                            "is_active" => is_plugin_active($plugin_info_file) // 添加激活状态
                        ];
                        // 将插件对象添加到数组中
                        $all_plugins[] = $plugin;
//                        echo "Added plugin: " . $plugin_info['Plugin Name'] . PHP_EOL;
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

    return $all_plugins;
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
