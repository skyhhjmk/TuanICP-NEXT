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

define('DEBUG', true);

// 定义一天中的秒数
define('DAY_IN_SECONDS', 86400);

// 定义cookie域
define('COOKIE_DOMAIN', $_SERVER['HTTP_HOST']);
// 定义路径常量
define('BOOT_LOADER_DIR', __DIR__ . '/boot_loader');
define('SYSTEM_DIR', __DIR__ . '/super');
define('DATA_ROOT', __DIR__ . '/data');
define('IS_CRON', true);
// 定义插件目录常量
define('TUANICP_PLUGIN_DIR', DATA_ROOT . '/plugins');
define('TUANICP_TEMPLATE_DIR', DATA_ROOT . '/templates');

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
function switchSlot()
{
    $currentSlot = getCurrentSlot();
    $newSlot = $currentSlot === 'A' ? 'B' : 'A';
    file_put_contents('boot_loader/slot', $newSlot);
}

// 初始化脚本
function initScript()
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
    include APP_ROOT . '/tuanicp_cron.php';
}

// 调用初始化脚本
initScript();