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

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

function initTwig(): Environment
{
    define('TEMPLATE_NAME', get_Template_name());
    $loader = new FilesystemLoader(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME); // 设置模板位置
    $loader->addPath(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME, 'index');
    $loader->addPath(TUANICP_TEMPLATE_DIR . '/' . TEMPLATE_NAME . '/admin', 'admin');
    $loader->addPath(TUANICP_PLUGIN_DIR . '/', 'plugin_root');
    $twig = new Environment($loader, [
        'cache' => APP_ROOT . '/cache', // 设置缓存目录
        'debug' => DEBUG, // 开启调试模式
        'auto_reload' => true, // 当模板文件发生变化时自动重新加载
        'strict_variables' => false, // 当变量不存在时抛出异常
    ]);
    return $twig;
}
