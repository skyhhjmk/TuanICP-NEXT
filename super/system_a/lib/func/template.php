<?php



if (!defined('APP_ROOT')) {
    exit('No direct script access allowed');
}

function get_all_templates(): array
{
    // 初始化一个空数组来存储主题信息
    $all_templates = [];

    // 检查目录是否存在
    if (is_dir(TUANICP_TEMPLATE_DIR)) {
        // 尝试打开目录
        $dir = @opendir(TUANICP_TEMPLATE_DIR); // 使用 @ 来抑制错误
        if ($dir === false) {
            output_error("无法打开主题目录: ", TUANICP_TEMPLATE_DIR . PHP_EOL);
            return $all_templates; // 返回空数组
        }

        // 循环读取目录下的所有条目
        while (($subdir = readdir($dir)) !== false) {
            // 跳过'.'和'..'这两个特殊的目录
            if ($subdir != "." && $subdir != "..") {
                // 检查是否为目录
                $template_dir = TUANICP_TEMPLATE_DIR . '/' . $subdir;
                if (is_dir($template_dir)) {
                    // 构建主题信息文件路径
                    $template_info_file = $template_dir . '/main.php';

                    // 获取主题信息
                    $template_info = get_template_info($template_info_file);

                    if ($template_info) {
                        // 构建主题对象
                        $template = [
                            "template_name" => $template_info['name'] ?? '',
                            "template_info" => $template_info['description'] ?? '',
                            "template_version" => $template_info['version'] ?? '',
                            "template_author" => $template_info['author'] ?? '',
                            "template_entry" => $template_info_file,
                            "template_conflicts" => $template_info['conflicts'] ?? '',
                            "template_dependencies" => $template_info['dependencies'] ?? '',
                            "is_active" => is_template_active($template_info_file)
                        ];
                        // 将主题对象添加到数组中
                        $all_templates[] = $template;
                    } else {
                        output_error("无法获取主题信息: ", $template_info_file . PHP_EOL);
                    }
                }
            }
        }
        // 关闭目录
        closedir($dir);
    } else {
        output_error("主题目录不存在: ", TUANICP_TEMPLATE_DIR . PHP_EOL);
    }
//var_dump($all_templates);
    return $all_templates;
}
