<?php
/*
 * Copyright (c) 2025. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

// 检查是否有POST请求，并尝试连接数据库
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取表单数据
    $dbType =$_POST['dbtype'] ?? 'MySQL';
    $host =$_POST['host'] ?? '';
    $username =$_POST['username'] ?? '';
    $password =$_POST['password'] ?? '';
    $dbName =$_POST['dbname'] ?? '';
    $path =$_POST['path'] ?? ''; // 用于SQLite

    // 根据数据库类型连接数据库
    try {
        switch ($dbType) {
            case 'MySQL':
                $dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";
                $pdo = new PDO($dsn, $username,$password);
                // 连接成功，将信息写入.env文件
                file_put_contents('.env', "DB_HOST=$host\nDB_NAME=$dbName\nDB_USER=$username\nDB_PASS=$password\n", FILE_APPEND);
                echo "<p class='success'>数据库连接成功！信息已写入.env文件。</p>";
                break;
            // 其他数据库类型的连接逻辑可以在这里添加
            default:
                echo "<p class='failure'>不支持的数据库类型。</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='failure'>数据库连接失败: " . $e->getMessage() . "</p>";
    }
}

// HTML 输出
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>数据库配置</title>
    <style>
        /* 在这里添加CSS样式 */
    </style>
</head>
<body>
<div class='container'>
    <h1>数据库配置</h1>
    <form action="./step2.php" method="post">
        <label for="dbtype">数据库类型:</label>
        <select id="dbtype" name="dbtype" onchange="toggleOptions()">
            <option value="MySQL">MySQL</option>
            <option value="SQLite" disabled>SQLite</option>
            <option value="oci" disabled>oci</option>
            <option value="sqlserver" disabled>SQL Server</option>
        </select><br><br>

        <div id="mysqlOptions">
            <label for="host">主机地址:</label>
            <input type="text" id="host" name="host" required><br><br>
            <label for="username">用户名:</label>
            <input type="text" id="username" name="username" required><br><br>
            <label for="password">密码:</label>
            <input type="password" id="password" name="password" required><br><br>
            <label for="dbname">数据库名:</label>
            <input type="text" id="dbname" name="dbname" required><br><br>
        </div>

        <!-- 其他数据库类型的输入框可以在这里添加，并设置为隐藏 -->

        <input type="submit" value="确认">
    </form>
</div>
<script>
    function toggleOptions() {
        var dbType = document.getElementById('dbtype').value;
        var mysqlOptions = document.getElementById('mysqlOptions');
        // 根据选择的数据库类型显示或隐藏相应的输入框
        switch (dbType) {
            case 'MySQL':
                mysqlOptions.style.display = 'block';
                break;
            // 其他数据库类型的逻辑可以在这里添加
            default:
                mysqlOptions.style.display = 'none';
        }
    }
</script>
</body>
</html>
