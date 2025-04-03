<?php



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['dbHost'];
    $dbPort = $_POST['dbPort'];
    $dbUser = $_POST['dbUser'];
    $dbPassword = $_POST['dbPassword'];
    $dbName = $_POST['dbName'];

    // 测试数据库连接
    $conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName, $dbPort);
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => '数据库连接错误']));
    }

    // 导出指定的数据库表
    $tables = ['sites', 'site_meta', 'users', 'user_meta'];
    $backupFile = 'database_backup.sql';
    $backupHandle = fopen($backupFile, 'w');

    foreach ($tables as $table) {
        $result = $conn->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch_row();
        fwrite($backupHandle, $row[1] . ";\n\n");

        $result = $conn->query("SELECT * FROM `$table`");
        $numFields = $result->field_count;
        $fields = $result->fetch_fields();

        $fieldNames = array_map(function ($field) {
            return "`" . $field->name . "`";
        }, $fields);

        fwrite($backupHandle, "INSERT INTO `$table` (" . implode(", ", $fieldNames) . ") VALUES\n");

        while ($row = $result->fetch_row()) {
            $row = array_map(function ($item) use ($conn) {
                return is_null($item) ? "NULL" : "'" . $conn->real_escape_string($item) . "'";
            }, $row);

            fwrite($backupHandle, "(" . implode(", ", $row) . "),\n");
        }

        // Remove the last comma and add a semicolon
        fseek($backupHandle, -2, SEEK_END);
        fwrite($backupHandle, ";\n\n");
    }

    fclose($backupHandle);

    // 删除数据库中的所有表、索引、触发器等
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $conn->query("DROP TABLE IF EXISTS `" . $row[0] . "`");
    }

    // 删除./../data/.env文件
    unlink(__DIR__ . '/../data/.env');
    unlink(__DIR__ . '/../install/install_cache.lock');
    unlink(__DIR__. '/../install/tmp_key');

    // 返回备份文件
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backupFile));
    readfile($backupFile);
    exit;
}
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>TuanICP-NEXT - 恢复模式</title>
    <style>
        body {
            background-color: #000000; /* 将背景颜色改为黑色 */
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            color: #FF0000; /* 将所有文字颜色改为红色 */
        }

        .error-container {
            background-color: #8B0000; /* 将中间放置内容的div背景颜色改为暗红色 */
            color: #FFFFFF; /* 设置文本颜色为白色 */
            padding: 20px;
            border-radius: 10px;
            margin-top: 100px;
        }

        button {
            background-color: #FF4500; /* 橙红色背景 */
            color: white; /* 白色文字 */
            padding: 10px 20px; /* 内边距 */
            border: none; /* 无边框 */
            border-radius: 5px; /* 圆角 */
            cursor: pointer; /* 鼠标悬停时显示为指针 */
            font-size: 16px; /* 字体大小 */
            transition: background-color 0.3s; /* 背景颜色过渡效果 */
        }

        button:hover {
            background-color: #FF6347; /* 悬停时的背景颜色 */
        }

    </style>
</head>
<body>
<div class="error-container">
    <h1>TuanICP-NEXT - 恢复模式</h1>
    <p>很抱歉，系统出现了严重的错误，当你看到此消息，代表系统已经尝试过回滚版本等手段，结果均为无法继续运行。</p>
    <p>如果您是访客，请联系站点管理员并告知“站点进入了恢复模式”。</p>
    <p>如果您是管理员，请在源码交流群或者开源仓库中反馈此问题，并告知近期操作。</p>
    <p>我们将尽快解决此问题，并为您提供解决方案。</p>
    <p>感谢您的理解和支持！</p>
    <p style="font-size: 20px">
        当然，还有一个极度不推荐的解决方法，重新进行全新安装，这样通常可以解决一些问题，但您会丢失数据。</p>
</div>
<button id="showFormButton">显示数据库恢复表单</button>
<div id="databaseFormContainer" style="display:none;">
    <form id="databaseForm">
        <label for="dbHost">数据库地址:</label>
        <input type="text" id="dbHost" name="dbHost" required><br><br>
        <label for="dbPort">端口:</label>
        <input type="text" id="dbPort" name="dbPort" required><br><br>
        <label for="dbUser">用户名:</label>
        <input type="text" id="dbUser" name="dbUser" required><br><br>
        <label for="dbPassword">密码:</label>
        <input type="password" id="dbPassword" name="dbPassword" required><br><br>
        <label for="dbName">数据库名:</label>
        <input type="text" id="dbName" name="dbName" required><br><br>
        <button type="submit">提交</button>
    </form>
</div>
<script>
    document.getElementById('showFormButton').addEventListener('click', function () {
        document.getElementById('databaseFormContainer').style.display = 'block';
    });

    document.getElementById('databaseForm').addEventListener('submit', function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.blob())
            .then(blob => {
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'database_backup.sql';
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('数据库连接错误或导出失败，请检查输入的数据库参数。');
            });
    });
</script>

</body>
