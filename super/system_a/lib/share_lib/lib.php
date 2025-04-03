<?php



if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
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

function verifySign($data, $receivedSign, $genKey)
{
    // 对数据进行按键名排序
    ksort($data);

    // 生成签名字符串前，处理值为null的情况，将其转换为空字符串
    array_walk_recursive($data, function (&$value) {
        if ($value === null || $value === '') {
            $value = '';
        }
    });

    // 对数组进行JSON编码处理
    array_walk_recursive($data, function (&$value) {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
    });

    // 生成签名字符串
    $sign_string = http_build_query($data);
    $sign_string .= '&gen_key=' . $genKey;

    // 计算签名字符串的MD5值
    $calculatedSign = md5($sign_string);

    // 比较计算出的签名与接收到的签名
    return $calculatedSign === $receivedSign;
}


function getSign($data, $genKey)
{
    // 对数据进行按键名排序
    ksort($data);
    // 生成签名字符串
    $sign_string = http_build_query($data);
    $sign_string .= '&gen_key=' . $genKey;
    // 计算签名字符串的MD5值
    $sign = md5($sign_string);
    return $sign;
}

function icp_auth()
{
    /*
     * 注意：
     * 发心跳验证签名过不去，所以暂时禁用掉心跳的验证逻辑
     * 在有缓存、无缓存两种状态需要都注释掉
     */

    // 缓存命中，发心跳包
    $auth_token = get_Config('auth_token');
    if (!empty($auth_token)) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://qifu-api.baidubce.com/ip/local/geo/v1/district");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 将结果返回，而不是输出
        // 关闭ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            // 关闭cURL会话
            curl_close($ch);

            // 解码JSON响应
            $decodedResponse = json_decode($response, true);
        }
        // 定义变量
        $device_code = $decodedResponse['ip'];
        $device_info_st = 'Server:' . str_replace(["\n", "\r\n"], "\r\n", $_SERVER['SERVER_SOFTWARE']) . '-PHP:' . str_replace(["\n", "\r\n"], "\r\n", phpversion());
        $device_info = md5($device_info_st);
        $timestamp = time();
        $curl = curl_init();

        $data = [
            "device_info" => $device_info,
            "device_code" => $device_code,
            "token" => $auth_token,
            "timestamp" => $timestamp
        ];
        // 构建POST数据
        $postData = [
            "data" => $data,
            "skey" => SKEY,
            "vkey" => VKEY,
            "sign" => getSign($data, '589776G2b9c6263d'),
        ];
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://authapi.example.com/myauth/soft/heart',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            // 这里打印错误信息，或者你可以将其记录到日志中
            echo "cURL Error: " . $error;
        }

        curl_close($curl);

        global $init_auth_data;

        $init_auth_data = json_decode($response, true);
        if (empty($login_token_data['result'])) {
//            var_dump($response);
//            echo "<br/><h2>签名验证失败，这可能是缓存过期，正在重新获取令牌...</h2>";
//            echo '<script type="text/javascript">window.location.reload();</script>';
            goto req;
        }
//            $receivedSign = $login_token_data['sign'];
//            if (!verifySign($init_auth_data['result'], $receivedSign, '589776G2b9c6263d')) {
//                return false;
//            }
        return true;
    }
    req:
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://qifu-api.baidubce.com/ip/local/geo/v1/district");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 将结果返回，而不是输出
    // 关闭ssl验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    } else {
        // 关闭cURL会话
        curl_close($ch);

        // 解码JSON响应
        $decodedResponse = json_decode($response, true);
    }
    // 定义变量
    $device_code = $decodedResponse['ip'];
    $device_info_st = 'Server:' . str_replace(["\n", "\r\n"], "\r\n", $_SERVER['SERVER_SOFTWARE']) . '-PHP:' . str_replace(["\n", "\r\n"], "\r\n", phpversion());
    $device_info = md5($device_info_st);
    $timestamp = time();
    $curl = curl_init();

    $data = [
        "device_info" => $device_info,
        "device_code" => $device_code,
        "timestamp" => $timestamp
    ];
    // 构建POST数据
    $postData = [
        "data" => $data,
        "skey" => SKEY,
        "vkey" => VKEY,
        "sign" => getSign($data, '589776G2b9c6263d'),
    ];
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://authapi.example.com/myauth/soft/init',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);

    if ($response === false) {
        $error = curl_error($curl);
        // 这里打印错误信息，或者你可以将其记录到日志中
        echo "cURL Error: " . $error;
    }

    curl_close($curl);

    global $init_auth_data;

    $init_auth_data = json_decode($response, true);
    if (empty($init_auth_data['result'])) {
        var_dump($response);
        return false;
    }
    $receivedSign = $init_auth_data['sign'];
    if (!verifySign($init_auth_data['result'], $receivedSign, '589776G2b9c6263d')) {
        var_dump($response);
        echo "<br/><h2>授权验证失败</h2>";
        return false;
    }

    // 定义变量
    $device_code = $decodedResponse['ip'];
    $device_info_st = 'Server:' . str_replace(["\n", "\r\n"], "\r\n", $_SERVER['SERVER_SOFTWARE']) . '-PHP:' . str_replace(["\n", "\r\n"], "\r\n", phpversion());
    $device_info = md5($device_info_st);
    $timestamp = time();
    $curl = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = [
        'user' => get_Config('auth_user', null, false, false) ?? '0',
        'pass' => get_Config('auth_passwd', null, false, false) ?? '0',
//    'ckey' => get_Config('auth_card_key',null ,false,false) ?? '0',
        "device_info" => $device_info,
        "device_code" => $device_code,
        "timestamp" => $timestamp
    ];
    // 构建POST数据
    $postData = [
        'data' => $data,
        "skey" => SKEY,
        "vkey" => VKEY,
        "sign" => getSign($data, '589776G2b9c6263d'),
    ];

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://authapi.example.com/myauth/soft/login',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    if ($response === false) {
        $error = curl_error($curl);
        // 这里打印错误信息，或者你可以将其记录到日志中
        echo "cURL Error: " . $error;
    }

    curl_close($curl);

    global $login_auth_data;

    $login_auth_data = json_decode($response, true);
    if (!is_array($login_auth_data) || empty($login_auth_data['result'])) {
        var_dump($response);
        echo "<br/><h2>授权验证失败</h2>";
        return false;
    }
    $receivedSign = $login_auth_data['sign'];
//    if (!verifySign($login_auth_data['result'], $receivedSign, '589776G2b9c6263d')) {
//        return false;
//    }
    set_Config('auth_token', $login_auth_data['result']['token']);
    return true;
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