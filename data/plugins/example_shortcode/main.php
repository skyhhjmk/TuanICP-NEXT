<?php
/*
* Name:        短代码示例
* Description:        此插件可以添加一个短代码
* Version:            1.0
* Author:             风屿Wind
*/
// 定义短代码处理器
function my_shortcode_handler($atts, $content = null, $tag, $ignore_html = false)
{
    // 提取属性并设置默认值
    $atts = shortcode_atts(array(
        'title' => 'Default Title',
        'class' => '',
    ), $atts, $tag);

    // 根据属性生成输出
    $output = '<div class="' . esc_attr($atts['class']) . '">';
    $output .= '<h2>' . esc_html($atts['title']) . '</h2>';
    if ($content) {
        $output .= do_shortcode($content); // 允许嵌套短代码
    }
    $output .= '</div>';

    return $output;
}

// 注册短代码
add_shortcode('my_shortcode', 'my_shortcode_handler');

// 示例：使用短代码
// [my_shortcode title="My Custom Title" class="my-custom-class"]This is the content[/my_shortcode]

// 为了演示，我们可以模拟一个简单的函数来处理短代码文本
function process_shortcode_text($text)
{
    return do_shortcode($text);
}

// 示例文本
$shortcode_text = '[my_shortcode title="My Custom Title" class="my-custom-class"]This is the content[/my_shortcode]';

// 处理短代码文本并输出结果
echo process_shortcode_text($shortcode_text);