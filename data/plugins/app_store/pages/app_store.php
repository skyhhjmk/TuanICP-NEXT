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

if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}

// 定义API URL
$apiUrl = 'http://icpn.com/app_store_api.php'; // 假设这是插件市场的API URL

// 获取远端API的插件数据
function fetchRemotePlugins($apiUrl) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $pluginsData = curl_exec($ch);
    curl_close($ch);
    return json_decode($pluginsData, true);
}

// 获取本地已安装的插件
function getLocalPlugins() {
    $localPlugins = [];
    $allPlugins = get_all_plugins(); // 使用 get_all_plugins 函数获取所有插件信息

    foreach ($allPlugins as$plugin) {
        $localPlugins[$plugin['plugin_name']] = [
            'file' => $plugin['plugin_entry'],
            'active' => $plugin['is_active']
        ];
    }
    return $localPlugins;
}

// 假设这是从远程API获取的插件数据
$remotePlugins = fetchRemotePlugins($apiUrl);
$localPlugins = getLocalPlugins();

// 遍历远端插件数据，判断是否已安装或激活
foreach ($remotePlugins as &$plugin) {
    $pluginName =$plugin['name'];
    if (isset($localPlugins[$pluginName])) {
        $plugin['status'] = 'installed';
        if ($localPlugins[$pluginName]['active']) {
            $plugin['status'] = 'activated';
        }
    } else {
        $plugin['status'] = 'not_installed';
    }

    // 检查插件是否可用
    if (!$plugin['available']) {
        $plugin['status'] = 'unavailable';
    }
}

// Twig模板引擎变量赋值
$twigVariables = [
    'plugins' => $remotePlugins
];

global $twigVariables;

function app_store_page_vars($routes) {
    global $twigVariables;
    $routes = array_merge($routes, $twigVariables); // 合并两个数组
    return $routes;
}

// 添加过滤器
add_filter('page_vars', 'app_store_page_vars');

$twig = initTwig();
echo $twig->render('@admin/app_store.html.twig', get_Page_vars());