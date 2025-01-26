<?php
// 存储钩子回调的数组
$plugin_callbacks = [];

$GLOBALS['tuanicp_actions'] = array();
$GLOBALS['tuanicp_filters'] = array();

/**
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

function apply_filters($tag, $value, ...$args)
{
    global $tuanicp_filters;
    if (isset($tuanicp_filters[$tag])) {
        foreach ($tuanicp_filters[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $func_args = array_merge(array(&$value), array_slice($args, 0, (int)$callback['accepted_args'] - 1));
                call_user_func_array($callback['function'], $func_args);
            }
        }
    }
    return $value;
}