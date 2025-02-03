<?php

define('INSTALL_ROOT', __DIR__);

if(file_exists(INSTALL_ROOT . '/../super/system_a/.env')){
    header('Location: /');
    exit;
}

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
    <h1>TuanICP<span class="rainbow-text">NEXT</span>安装向导 - 开始安装</h1>

    <div class='button-container'>
        <button class='next-button' onclick='location.href="step1.php"'>开始安装</button>
    </div>
</div>

</body>
</html>
