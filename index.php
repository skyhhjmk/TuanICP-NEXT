<?php


define('DEBUG', true);
// 定义路径常量
define('BOOT_LOADER_DIR', __DIR__ . '/boot_loader');
define('SYSTEM_DIR', __DIR__ . '/super');
define('DATA_ROOT', __DIR__ . '/data');
define('ROOT', __DIR__);
// 定义插件目录常量
define('TUANICP_PLUGIN_DIR', DATA_ROOT . '/plugins');
define('TUANICP_TEMPLATE_DIR', DATA_ROOT . '/templates');
// 定义cookie域
define('COOKIE_DOMAIN', $_SERVER['HTTP_HOST']);
// 定义一天中的秒数
define('DAY_IN_SECONDS', 86400);
// 检查是否刚更新完并且是否在10分钟内
function isRecentlyUpdated()
{
    $updatedFile = 'boot_loader/just_updated';
    if (file_exists($updatedFile)) {
        $updateTimestamp = file_get_contents($updatedFile);
        $updateTimestamp = intval($updateTimestamp); // 将字符串转换为整数
        $currentTime = time();
        if ($currentTime - $updateTimestamp <= 600) { // 10分钟内
            return true;
        } else {
            // 超过10分钟，删除更新标记文件
            unlink($updatedFile);
        }
    }
    return false;
}

// 更新retry文件
function updateRetryFile($slot)
{
    $retryFile = "boot_loader/retry_{$slot}";
    $retryCount = file_exists($retryFile) ? file_get_contents($retryFile) : 0;
    $retryCount++;
    file_put_contents($retryFile, strval($retryCount));
}

function getCurrentSlot()
{
    $slotFile = 'boot_loader/slot';
    $validSlots = ['A', 'B']; // 定义有效的槽位值

    if (!file_exists($slotFile)) {
        // 文件不存在，创建文件并写入'A'
        file_put_contents($slotFile, 'A');
        return 'A';
    }

    // 读取文件内容
    $currentSlot = file_get_contents($slotFile);

    // 检查文件内容是否为有效的槽位值
    if (!in_array($currentSlot, $validSlots)) {
        // 文件内容无效，删除文件并重新创建
        unlink($slotFile);
        file_put_contents($slotFile, 'A');
        return 'A';
    } else {
        return $currentSlot;
    }
}

// 切换槽位
/**
 * @return void
 */
function switchSlot(): void
{
    $currentSlot = getCurrentSlot();
    $newSlot = $currentSlot === 'A' ? 'B' : 'A';
    file_put_contents('boot_loader/slot', $newSlot);
}

// 初始化脚本
/**
 * @return void
 */
function initScript(): void
{
    if (file_exists('boot_loader/update_done')) {
        unlink('boot_loader/update_done');
        $timestamp = time(); // 获取当前时间戳
        file_put_contents('boot_loader/just_updated', $timestamp); // 将时间戳写入文件
        switchSlot();
    }
    // 检查当前槽位
    $currentSlot = getCurrentSlot();
    // 定义APP_ROOT
    define('APP_ROOT', SYSTEM_DIR . "/system_{$currentSlot}");
    include APP_ROOT . '/index.php';
}

// 调用初始化脚本
initScript();