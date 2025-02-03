<?php
if (file_exists('install.lock')){
    header('Location: /');
    exit();
}
// 检查是否有POST请求，并尝试连接数据库
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
// 获取表单数据
    $dbType = $_POST['dbtype'] ?? 'MySQL';
    $host = $_POST['host'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $dbName = $_POST['dbname'] ?? '';
    $path = $_POST['path'] ?? ''; // 用于SQLite

    // 根据数据库类型连接数据库
    try {
        switch ($dbType) {
            case 'MySQL':
                $dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";
                $pdo = new PDO($dsn, $username, $password);
                // 设置错误模式为异常
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // 连接成功，将信息写入.env文件
                if (file_exists('../super/system_a/.env')) {
                    $info_msg = "<p class='failure'>.env文件已存在。</p>";
                    break;
                }
                file_put_contents('../super/system_a/.env', "DB_TYPE=mysql\nDB_HOST={$host}\nDB_NAME={$dbName}\nDB_USER={$username}\nDB_PASS={$password}\n", FILE_APPEND);
                $info_msg = "<p class='success'>数据库连接成功！信息已写入.env文件。</p>";
                $info_msg .= "<p>正在创建数据库表...</p>";
                // 获取数据库中所有表的列表
                $query = "SHOW TABLES";
                $stmt = $pdo->query($query);
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

                // 检查数据库是否为空
                if (empty($tables)) {
                    // 数据库为空，读取mysql.sql文件并执行命令
                    $sqlCommands = file_get_contents('sql/mysql.sql'); // 假设mysql.sql文件在当前目录

                    // 分割SQL命令
                    $sqlStatements = explode(';', $sqlCommands);

                    // 执行每个SQL命令
                    foreach ($sqlStatements as $sql) {
                        if (trim($sql) != '') {
                            $pdo->exec($sql);
                        }
                    }

                    $info_msg .= "<p class='success'>已创建数据表表和默认数据。</p>";
                    $info_msg .= "<p class='success'>3秒后自动继续安装，若没有跳转，请点击-><a href='./step3.php'>继续安装</a><-</p>";
                    header("refresh:3;url=./step3.php");
                } else {
                    $info_msg .= "<p class='failure'>安装程序终止，因为数据库不为空，存在以下表：</p>";
                    $info_msg .= "<ul>";

                    foreach ($tables as $table) {
                        $info_msg .= "<li>{$table}</li>";
                    }

                    $info_msg .= "</ul>";
                    unlink('../super/system_a/.env');
                }
                break;
            default:
                $info_msg = "<p class='failure'>不支持的数据库类型。</p>";
        }
    } catch (PDOException $e) {
        $info_msg = "<p class='failure'>数据库连接失败，请检查用户名或密码后重试: " . $e->getMessage() . "</p>";
    }
}

// HTML 输出
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TuanICP-NEXT安装向导 - 数据库配置</title>
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
        .success {
            color: green;
        }

        .failure {
            color: red;
        }

        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        input[type="submit"] {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class='container'>
    <h1>TuanICP<span class="rainbow-text">NEXT</span>安装向导 - 数据库配置</h1>
    <form action="./step2.php" method="post">
        <label for="dbtype">数据库类型:</label>
        <select id="dbtype" name="dbtype" onchange="toggleOptions()">
            <option value="MySQL">MySQL（完美兼容性）</option>
            <option value="SQLite" disabled>SQLite（一般兼容性）</option>
            <option value="oci" disabled>Oracle（未知兼容性）</option>
            <option value="sqlserver" disabled>SQL Server（未知兼容性）</option>
        </select>

        <div id="dbOptions">
            <label for="host">主机地址:</label>
            <input type="text" id="host" name="host" required>

            <label for="username">用户名:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">密码:</label>
            <input type="password" id="password" name="password" required>

            <label for="dbname">数据库名:</label>
            <input type="text" id="dbname" name="dbname" required>
        </div>

        <input type="submit" value="确认">
    </form>
    <?php
    if (isset($info_msg)) {
        echo $info_msg;
    }
    ?>
</div>
<script>
    function toggleOptions() {
        var dbType = document.getElementById('dbtype').value;
        var dbOptions = document.getElementById('dbOptions');
        // 根据选择的数据库类型显示或隐藏相应的输入框
        switch (dbType) {
            case 'SQLite':
            case 'oci':
            case 'sqlserver':
            case 'MySQL':
                dbOptions.style.display = 'block';
                break;
            // 其他数据库类型的逻辑可以在这里添加
            default:
                dbOptions.style.display = 'none';
        }
    }
</script>
</body>
</html>
