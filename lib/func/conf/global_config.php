<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
function get_global_site_config()
{
    $dbc = initDatabase();
    $sql = "SELECT v FROM `config` WHERE k = 'site_config'";

    try {
        // 准备SQL语句
        $stmt = $dbc->prepare($sql);
        // 执行查询
        $stmt->execute();
        // 获取结果
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // 检查结果是否有效
        if ($result && isset($result['v'])) {
            // 反序列化数据
            $siteConfig = unserialize($result['v']);

            // 检查反序列化是否成功
            if ($siteConfig !== false) {
                return $siteConfig; // 返回配置数组
            } else {
                // 反序列化失败，可能是因为数据不是有效的序列化字符串
                return null;
            }
        } else {
            // 如果没有找到配置数据，插入默认配置
            $defaultConfig = [
                'site_name' => 'Wind Tag',
                'site_theme' => 'default',
                'site_description' => 'Wind Tag是一个缝合怪系统，支持标签管理，访问追踪，功能标志等。'
            ];
            $serializedDefaultConfig = serialize($defaultConfig);
            $insertSql = "INSERT INTO `config` (k, v) VALUES ('site_config', :config)";
            $insertStmt = $dbc->prepare($insertSql);
            $insertStmt->bindParam(':config', $serializedDefaultConfig, PDO::PARAM_STR);
            $insertStmt->execute();
            return $defaultConfig; // 返回默认配置数组
        }
    } catch (PDOException $e) {
        // 在这里处理错误，例如记录日志或者抛出异常
        output_error("查询失败: ", $e->getMessage());
        return null;
    }
}