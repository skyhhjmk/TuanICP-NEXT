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

$user_role = get_current_user_role();
if (!$user_role){
    header('Location: '. get_Url('admin/login'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $plugin_name = $_POST['plugin_name'] ?? null;
    $plugin_entry = $_POST['plugin_entry'] ?? null;
    if (is_null($action) && is_null($plugin_name)) {
        $response = [
            'status' => 'error',
            'plugin_name' => '',
            'action' => '',
            'message' => '传入参数不能为空'
        ];
        goto output;
    }
    if (is_null($action)) {
        $response = [
            'status' => 'error',
            'plugin_name' => $plugin_name,
            'action' => '',
            'message' => '方法不能为空'
        ];
        goto output;
    }
    if (is_null($plugin_name)) {
        $response = [
            'status' => 'error',
            'plugin_name' => '',
            'action' => $action,
            'message' => '插件名不能为空'
        ];
        goto output;
    }
    switch ($action) {
        case 'activate':
            $name = $plugin_name;
            if (is_null($plugin_entry)) {
                $response = [
                    'status' => 'error',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '插件入口文件参数不能为空'
                ];
                goto output;
            }
            $path = $plugin_entry;
            if(activate_plugin($name , $path)){
                // 初始化缓存池，如果缓存被禁用，则 $cachePool 为 null
                $cachePool = initCache();

                // 定义要清除的缓存项的键
                $cacheKey = 'active_plugins';

                // 检查缓存池是否不为 null
                if ($cachePool !== null) {
                    // 从缓存池中获取缓存项
                    $item =$cachePool->getItem($cacheKey);
                    // 清除缓存项
                    $cachePool->deleteItem($cacheKey);
                }
                $response = [
                    'status' => 'success',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '已激活'
                ];
            } else {
                // 初始化缓存池，如果缓存被禁用，则 $cachePool 为 null
                $cachePool = initCache();

                // 定义要清除的缓存项的键
                $cacheKey = 'active_plugins';

                // 检查缓存池是否不为 null
                if ($cachePool !== null) {
                    // 从缓存池中获取缓存项
                    $item =$cachePool->getItem($cacheKey);
                    // 清除缓存项
                    $cachePool->deleteItem($cacheKey);
                }
                $response = [
                    'status' => 'error',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '插件已经被激活或存在冲突，如果误判请再次尝试'
                ];
            }

            break;
        case 'deactivate':
            $name = $plugin_name;
            if (is_null($plugin_entry)) {
                $response = [
                    'status' => 'error',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '插件入口文件参数不能为空'
                ];
                goto output;
            }
            $path = $plugin_entry;
            if(deactivate_plugin($name , $path)){
                // 初始化缓存池，如果缓存被禁用，则 $cachePool 为 null
                $cachePool = initCache();

                // 定义要清除的缓存项的键
                $cacheKey = 'active_plugins';

                // 检查缓存池是否不为 null
                if ($cachePool !== null) {
                    // 从缓存池中获取缓存项
                    $item =$cachePool->getItem($cacheKey);
                    // 清除缓存项
                    $cachePool->deleteItem($cacheKey);
                }
                $response = [
                    'status' => 'success',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '已禁用'
                ];
            } else {
                // 初始化缓存池，如果缓存被禁用，则 $cachePool 为 null
                $cachePool = initCache();

                // 定义要清除的缓存项的键
                $cacheKey = 'active_plugins';

                // 检查缓存池是否不为 null
                if ($cachePool !== null) {
                    // 从缓存池中获取缓存项
                    $item =$cachePool->getItem($cacheKey);
                    // 清除缓存项
                    $cachePool->deleteItem($cacheKey);
                }
                $response = [
                    'status' => 'error',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '插件已经被禁用，如果误判请再次尝试'
                ];
            }
            break;
        case 'delete':

            $response = [
                'status' => 'success',
                'plugin_name' => $plugin_name,
                'action' => 'delete',
                'message' => '已删除'
            ];
            break;
        case 'check_update':
            $response = [
                'status' => 'none',
                'plugin_name' => $plugin_name,
                'action' => 'check_update',
                'message' => '不支持检查更新'
            ];
            break;
        default:
            $response = [
                'status' => 'error',
                'plugin_name' => '',
                'action' => 'unknown',
                'message' => '无法处理此方法'
            ];
            break;
    }
    output:
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Stop further execution after handling AJAX request
}