<?php
// 检查 PHP 版本
$required_php_version = '8.1';
$php_version_status = version_compare(PHP_VERSION, $required_php_version, '<') ? '失败' : '通过';
$php_version_note = $php_version_status == '失败' ? "PHP 版本过低，请升级到 PHP $required_php_version 或更高版本。当前版本: " . PHP_VERSION : "当前版本: " . PHP_VERSION;

// 检查所需插件
$required_extensions = ['pdo', 'pdo_mysql', 'curl'];
$extensions_status = [];
$extensions_notes = [];
foreach ($required_extensions as $extension) {
    $extensions_status[$extension] = extension_loaded($extension) ? '通过' : '失败';
    $extensions_notes[$extension] = $extensions_status[$extension] == '失败' ? "缺少必要的 PHP 扩展: $extension" : "已加载";
}

// 检查文件权限
$application_directory = '../..'; // 假设应用目录在上两级
$directory_status = is_readable($application_directory) && is_writable($application_directory) ? '通过' : '失败';
$directory_note = $directory_status == '失败' ? "应用目录 ($application_directory) 没有读写权限，请检查权限设置。" : "读写权限正常";

// 检查所有项是否通过
$all_passed = $php_version_status == '通过' && !in_array('失败', $extensions_status) && $directory_status == '通过';

// 输出结果到表格
echo "<style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }
        .container {
            text-align: center;
            width: 80%;
        }
        h1 {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
            border: 2px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .success {
            color: green;
        }
        .failure {
            color: red;
        }
        .button-container {
            margin-top: 20px;
        }
        .button-container button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }
        .next-button {
            background-color: #007bff;
            color: white;
        }
        .next-button:hover {
            background-color: #0056b3;
        }
        .force-button {
            background-color: #dc3545;
            color: white;
            display: none;
        }
        .force-button:hover {
            background-color: #c82333;
        }
        .checkbox-container {
            margin-bottom: 10px;
        }
        .checkbox-container input[type='checkbox'] {
            margin-right: 10px;
        }
    </style>";

echo "<div class='container'>
        <h1>TuanICP安装向导 - 环境检查</h1>
        <table>
            <tr>
                <th>检查项</th>
                <th>状态</th>
                <th>备注</th>
            </tr>
            <tr>
                <td>PHP 版本</td>
                <td class='" . ($php_version_status == '通过' ? 'success' : 'failure') . "'>$php_version_status</td>
                <td>$php_version_note</td>
            </tr>";

foreach ($extensions_status as $extension => $status) {
    echo "<tr>
            <td>$extension 扩展</td>
            <td class='" . ($status == '通过' ? 'success' : 'failure') . "'>$status</td>
            <td>{$extensions_notes[$extension]}</td>
          </tr>";
}

echo "<tr>
        <td>应用目录权限</td>
        <td class='" . ($directory_status == '通过' ? 'success' : 'failure') . "'>$directory_status</td>
        <td>$directory_note</td>
      </tr>
      </table>";

if ($all_passed) {
    echo "<div class='button-container'>
            <button class='next-button' onclick='location.href=\"step2.php\";'>下一步</button>
          </div>";
} else {
    echo "<div class='checkbox-container'>
            <input type='checkbox' id='forceInstall'>
            <label for='forceInstall'>我已明白环境检查不通过，但出于某些目的我依然要继续安装</label>
          </div>
          <div class='button-container'>
            <button class='force-button' id='forceButton' disabled onclick='location.href=\"step2.php\";'>强制继续</button>
          </div>
          <script>
            document.getElementById('forceInstall').addEventListener('change', function() {
                document.getElementById('forceButton').disabled = !this.checked;
            });
          </script>";
}

// 如果有任何检查失败，退出脚本
if ($php_version_status == '失败' || in_array('失败', $extensions_status) || $directory_status == '失败') {
    exit;
}
?>
