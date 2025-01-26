<?php
// 存储钩子回调的数组
$plugin_callbacks = [];

$GLOBALS['tuanicp_actions'] = array();
$GLOBALS['tuanicp_filters'] = array();

/**
 * 添加一个动作钩子
 * @param $tag //钩子名
 * @param $function_to_add //要添加的回调函数
 * @param int $priority //优先级
 * @param int $accepted_args //do_action时所能接收的参数个数
 * @return void
 */
function add_action($tag, $function_to_add, int $priority = 10, int $accepted_args = 1): void
{
    global $tuanicp_actions;
    if (is_callable($function_to_add)) {
        $tuanicp_actions[$tag][$priority][] = array(
            'function' => $function_to_add,
            'accepted_args' => $accepted_args
        );
    } else {
        trigger_error("The function is not callable", E_USER_WARNING);
    }
}

/**
 * 执行一个动作钩子
 * @param $tag
 * @param ...$args
 * @return void
 */
function do_action($tag, ...$args): void
{
    global $tuanicp_actions;
    if (isset($tuanicp_actions[$tag])) {
        foreach ($tuanicp_actions[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $func_args = array_slice($args, 0, (int)$callback['accepted_args']);
                call_user_func_array($callback['function'], $func_args);
            }
        }
    }
}

function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
{
    global $tuanicp_filters;
    if (is_callable($function_to_add)) {
        $tuanicp_filters[$tag][$priority][] = array(
            'function' => $function_to_add,
            'accepted_args' => $accepted_args
        );
    } else {
        trigger_error("The function is not callable", E_USER_WARNING);
    }
}

/**
 * @param $tag
 * @param $value
 * @param ...$args
 * @return mixed
 */
function apply_filters($tag, $value, ...$args): mixed
{
    global $tuanicp_filters;
    if (isset($tuanicp_filters[$tag])) {
        foreach ($tuanicp_filters[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                // 确保接受的参数数量是正确的
                $accepted_args = (int)$callback['accepted_args'];
                // 如果接受的参数数量大于1，才需要处理额外的参数
                if ($accepted_args > 1) {
                    $func_args = array_merge(array(&$value), array_slice($args, 0, $accepted_args - 1));
                } else {
                    // 如果只接受一个参数，直接传递$value
                    $func_args = [&$value];
                }
                call_user_func_array($callback['function'], $func_args);
            }
        }
    }
    return $value;
}


/**
 * 移除一个动作钩子
 * @param $tag
 * @param $function_to_remove
 * @param int $priority
 * @return bool
 */
function remove_action($tag, $function_to_remove, $priority = 10): bool
{
    global $tuanicp_actions;
    if (!isset($tuanicp_actions[$tag][$priority])) {
        return false;
    }

    foreach ($tuanicp_actions[$tag][$priority] as $index => $callback) {
        if ($callback['function'] === $function_to_remove) {
            unset($tuanicp_actions[$tag][$priority][$index]);
            return true;
        }
    }
    return false;
}

/**
 * 移除一个过滤器钩子
 * @param $tag
 * @param $function_to_remove
 * @param int $priority
 * @return bool
 */
function remove_filter($tag, $function_to_remove, $priority = 10): bool
{
    global $tuanicp_filters;
    if (!isset($tuanicp_filters[$tag][$priority])) {
        return false;
    }

    foreach ($tuanicp_filters[$tag][$priority] as $index => $callback) {
        if ($callback['function'] === $function_to_remove) {
            unset($tuanicp_filters[$tag][$priority][$index]);
            return true;
        }
    }
    return false;
}

/**
 * 检查是否有任何动作挂载到指定的钩子
 * @param $tag
 * @param bool $with_priority
 * @return bool|array
 */
function has_action($tag, $with_priority = false)
{
    global $tuanicp_actions;
    if (empty($tuanicp_actions[$tag])) {
        return false;
    }

    if ($with_priority) {
        return array_keys($tuanicp_actions[$tag]);
    }

    return true;
}

/**
 * 检查是否有任何过滤器挂载到指定的钩子
 * @param $tag
 * @param bool $with_priority
 * @return bool|array
 */
function has_filter($tag, $with_priority = false)
{
    global $tuanicp_filters;
    if (empty($tuanicp_filters[$tag])) {
        return false;
    }

    if ($with_priority) {
        return array_keys($tuanicp_filters[$tag]);
    }

    return true;
}

/**
 * 执行一个短代码
 * @param $tag
 * @param array $atts
 * @param null $content
 * @param bool $ignore_html
 * @return string
 */
function do_shortcode($tag, $atts = array(), $content = null, $ignore_html = false)
{
    // 这里只是一个示例，实际实现需要根据您的需求来编写
    return apply_filters("shortcode_{$tag}", '', $atts, $content, $ignore_html);
}

/**
 * 添加一个短代码
 * @param $tag
 * @param $func
 */
function add_shortcode($tag, $func)
{
    add_filter("shortcode_{$tag}", $func, 10, 3);
}
