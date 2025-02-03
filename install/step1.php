<?php
if (file_exists('install.lock')){
    header('Location: /');
    exit();
}
// 定义检查函数
function check_php_version($required_version)
{
    $current_version = PHP_VERSION;
    $status = version_compare($current_version, $required_version, '<') ? '失败' : '通过';
    $note = $status == '失败' ? "PHP 版本过低，请升级到 PHP $required_version 或更高版本。当前版本:$current_version" : "当前版本: $current_version";
    return compact('status', 'note');
}

function check_extensions($required_extensions)
{
    $status = [];
    $notes = [];
    foreach ($required_extensions as $extension) {
        $status[$extension] = extension_loaded($extension) ? '通过' : '失败';
        $notes[$extension] = $status[$extension] == '失败' ? "缺少必要的 PHP 扩展: $extension" : "已加载";
    }
    return compact('status', 'notes');
}

function check_directory_permissions($directory)
{
    $status = is_readable($directory) && is_writable($directory) ? '通过' : '失败';
    $note = $status == '失败' ? "应用目录 ($directory) 没有读写权限，请检查权限设置。" : "读写权限正常";
    return compact('status', 'note');
}

// 执行检查
$php_version_check = check_php_version('8.1');
$extensions_check = check_extensions([
    'pdo',
    'pdo_mysql',
    'curl',
    'openssl',
    'zip',
    'json'
]);
$directory_check = check_directory_permissions('..');

// 检查所有项是否通过
$all_passed = $php_version_check['status'] == '通过' && !in_array('失败', $extensions_check['status']) && $directory_check['status'] == '通过';

// 如果有任何检查失败，显示错误信息
if (!$all_passed) {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TuanICP-NEXT安装向导 - 环境检查</title>
    <style>
    
        .success {
            color: green;
        }

        .failure {
            color: red;
        }

        .container {
            width: 80%;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .button-container {
            text-align: right;
            margin-top: 20px;
        }

        .next-button, .force-button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        .next-button {
            background-color: #4CAF50;
            color: white;
        }

        .force-button {
            background-color: #f44336;
            color: white;
        }

        .checkbox-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>';
    echo "<div class='container'>";
    echo "<h1>TuanICP<span>NEXT</span>安装向导 - 环境检查</h1>";
    echo "<p class='failure'>环境检查未通过，请解决以下问题后再继续安装：</p>";
    echo "<table>";
    echo "<tr><th>检查项</th><th>状态</th><th>备注</th></tr>";
    echo "<tr><td>PHP 版本</td><td class='" . ($php_version_check['status'] == '通过' ? 'success' : 'failure') . "'>" . $php_version_check['status'] . "</td><td>" . $php_version_check['note'] . "</td></tr>";
    foreach ($extensions_check['status'] as $extension => $status) {
        echo "<tr><td>$extension 扩展</td><td class='" . ($status == '通过' ? 'success' : 'failure') . "'>$status</td><td>" . $extensions_check['notes'][$extension] . "</td></tr>";
    }
    echo "<tr><td>应用目录权限</td><td class='" . ($directory_check['status'] == '通过' ? 'success' : 'failure') . "'>" . $directory_check['status'] . "</td><td>" . $directory_check['note'] . "</td></tr>";
    echo "</table>";
    echo "<div class='checkbox-container'>";
    echo "<input type='checkbox' id='forceInstall'>";
    echo "<label for='forceInstall'>我已明白环境检查不通过，大概率会导致安装失败、运行不正常等，但出于某些目的我依然要继续安装</label>";
    echo "</div>";
    echo "<div class='button-container'>";
    echo "<button class='force-button' id='forceButton' style='display: none;' disabled onclick='location.href=\"step2.php\"'>强制继续</button>";
    echo "</div>";
    echo "<script>";
    echo "document.addEventListener('DOMContentLoaded', function() {";
    echo "var forceInstall = document.getElementById('forceInstall');";
    echo "var forceButton = document.getElementById('forceButton');";
    echo "forceInstall.addEventListener('change', function() {";
    echo "forceButton.disabled = !this.checked;";
    echo "forceButton.style.display = this.checked ? 'inline' : 'none';";
    echo "});";
    echo "});";
    echo "</script>";
    echo "</div>";
    exit;
}

// HTML 输出
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TuanICP-NEXT安装向导 - 环境检查</title>
    <style>
        @keyframes rainbow-text-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .rainbow-text {
            font-size: 48px;
            font-weight: bold;
            background: linear-gradient(270deg, red, orange, yellow, green, blue, indigo, violet);
            background-size: 600% 600%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent; /* Fallback for browsers that don't support gradients */
            animation: rainbow-text-animation 6s ease-in-out infinite;
        }
        /* 在这里添加CSS样式 */
        .success {
            color: green;
        }

        .failure {
            color: red;
        }

        .container {
            width: 80%;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .button-container {
            text-align: right;
            margin-top: 20px;
        }

        .next-button, .force-button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        .next-button {
            background-color: #4CAF50;
            color: white;
        }

        .force-button {
            background-color: #f44336;
            color: white;
        }

        .checkbox-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class='container'>
    <h1>TuanICP<span class="rainbow-text">NEXT</span>安装向导 - 环境检查</h1>
    <table>
        <tr>
            <th>检查项</th>
            <th>状态</th>
            <th>备注</th>
        </tr>
        <tr>
            <td>PHP 版本</td>
            <td class="<?= $php_version_check['status'] == '通过' ? 'success' : 'failure' ?>"><?= $php_version_check['status'] ?></td>
            <td><?= $php_version_check['note'] ?></td>
        </tr>
        <?php
        foreach ($extensions_check['status'] as $extension => $status): ?>
            <tr>
                <td><?= $extension ?> 扩展</td>
                <td class="<?= $status == '通过' ? 'success' : 'failure' ?>"><?= $status ?></td>
                <td><?= $extensions_check['notes'][$extension] ?></td>
            </tr>
        <?php
        endforeach; ?>
        <tr>
            <td>应用目录权限</td>
            <td class="<?= $directory_check['status'] == '通过' ? 'success' : 'failure' ?>"><?= $directory_check['status'] ?></td>
            <td><?= $directory_check['note'] ?></td>
        </tr>
    </table>
    <p>请将下方的伪静态内容配置到服务器（仅Nginx需要设置，Apache/OpenLiteSpeed/LiteSpeed无需配置，其他Web服务器请根据实际情况进行修改）</p>
    <textarea style="width: 100%; height: 150px;" readonly><?php include '../nginx.htaccess';?></textarea>
    <?php
    if ($all_passed): ?>
        <div class='button-container'>
            <button class='next-button' onclick='location.href="step2.php"'>下一步</button>
        </div>
    <?php
    else: ?>
        <div class='checkbox-container'>
            <input type='checkbox' id='forceInstall'>
            <label for='forceInstall'>我已明白环境检查不通过，大概率会导致安装失败、运行不正常等，但出于某些目的我依然要继续安装</label>
        </div>
        <div class='button-container'>
            <button class='force-button' id='forceButton' style='display: none;' disabled
                    onclick='location.href="step2.php"'>强制继续
            </button>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var forceInstall = document.getElementById('forceInstall');
                var forceButton = document.getElementById('forceButton');
                forceInstall.addEventListener('change', function () {
                    forceButton.disabled = !this.checked;
                    forceButton.style.display = this.checked ? 'inline' : 'none';
                });
            });
        </script>
    <?php
    endif; ?>
</div>
</body>
</html>