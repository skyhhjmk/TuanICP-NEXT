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


/**
 * 检查指定的ICP备案号是否存在于数据库中
 *
 * @param string $icp_number 要检查的ICP备案号
 * @return bool 如果ICP备案号存在则返回true，否则返回false
 * @throws JsonException
 */
function check_icp_exists($icp_number)
{
    $pdo = initDatabase();

    // 准备SQL语句
    $sql = "SELECT site_icp_number FROM sites WHERE site_icp_number = :icp_number";

    // 准备预处理语句
    $stmt = $pdo->prepare($sql);

    // 绑定参数
    $stmt->bindParam(':icp_number', $icp_number, PDO::PARAM_STR);

    // 执行预处理语句
    $stmt->execute();

    // 检索结果
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // 检查结果是否存在
    return $result !== false;
}

/**
 * 验证 ICP 号码的有效性
 * @param $icp_number
 * @return bool
 * @throws JsonException
 */
function validate_icp_number($icp_number): bool
{
    if (is_plugin_active_by_name('经典ICP规则')) {
        // 检查 ICP 号码是否为8位且仅包含数字
        if (strlen($icp_number) === 8 && ctype_digit($icp_number)) {
            // 检查 ICP 号码是否存在于数据库中
            if (!check_icp_exists($icp_number)) {
                return true;
            }
        }
        return false;
    } else {
        // TODO: 实现其他验证逻辑
    }
    return false;
}

function verifySign($data,$receivedSign, $genKey) {
    // 对数据进行按键名排序
    ksort($data);
    // 生成签名字符串
    $sign_string = http_build_query($data);
    $sign_string .= '&gen_key=' .$genKey;

    // 计算签名字符串的MD5值
    $calculatedSign = md5($sign_string);

    // 比较计算出的签名与接收到的签名
    return $calculatedSign ===$receivedSign;
}

function getSign($data, $genKey) {
    // 对数据进行按键名排序
    ksort($data);
    // 生成签名字符串
    $sign_string = http_build_query($data);
    $sign_string.= '&gen_key='.$genKey;
    // 计算签名字符串的MD5值
    $sign = md5($sign_string);
    return $sign;
}

function icp_auth()
{
    return true;
}


function icp_auth_free()
{

}

/**
 * 获取配置项的值，如果配置项不存在，则返回默认值
 * @param string $key 设置项
 * @param null $default 默认值
 * @param bool $init
 * @param bool $useCache
 * @return mixed|null
 * @throws JsonException
 */
function get_Config(string $key, $default = null, bool $init = false, bool $useCache = true): mixed
{
    if ($useCache) {
        // 初始化缓存池，如果缓存被禁用，则 $cachePool 为 null
        $cachePool = initCache();

        // 定义缓存项的键
        $cacheKey = 'config_' . $key;

        // 如果缓存池不为 null，尝试从缓存中获取数据
        if ($cachePool !== null) {
            $item = $cachePool->getItem($cacheKey);
            if ($item->isHit()) {
                // 缓存命中，直接返回缓存中的数据
                return $item->get();
            }
        }

        // 初始化数据库连接
        $dbc = initDatabase();
        $query = "SELECT v FROM config WHERE k = :key";
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // 如果查询结果存在，保存到缓存中（如果缓存池不为 null）
            if ($cachePool !== null) {
                $item->set($result['v']);
                $cachePool->save($item);
            }
            return $result['v'];
        } else {
            if ($init) {
                // 如果需要初始化配置，则调用 set_Config 函数
                set_Config($key, $default);
                // 保存默认值到缓存中（如果缓存池不为 null）
                if ($cachePool !== null) {
                    $item->set($default);
                    $cachePool->save($item);
                }
            }
            return $default;
        }
    } else {
        // 初始化数据库连接
        $dbc = initDatabase();
        $query = "SELECT v FROM config WHERE k = :key";
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['v'];
        } else {
            if ($init) {
                // 如果需要初始化配置，则调用 set_Config 函数
                set_Config($key, $default);
            }
            return $default;
        }
    }


}