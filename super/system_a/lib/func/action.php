<?php
$GLOBALS['tuanicp_actions'] = array();
$GLOBALS['tuanicp_filters'] = array();

function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1)
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

function do_action($tag, ...$args)
{
    global $tuanicp_actions;
    if (isset($tuanicp_actions[$tag])) {
        foreach ($tuanicp_actions[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $func_args = array_slice($args, 0, $callback['accepted_args']);
                call_user_func_array($callback['function'], $func_args);
            }
        }
    }
}

function add_filter($tag,$function_to_add, $priority = 10,$accepted_args = 1)
{
    global $tuanicp_filters;
    if (is_callable($function_to_add)) {
        $tuanicp_filters[$tag][$priority][] = array(
            'function' => $function_to_add,
            'accepted_args' => $accepted_args
        );
    } else {
        trigger_error("The function '{$function_to_add}' is not callable", E_USER_WARNING);
    }
}

function apply_filters($tag, $value, ...$args)
{
    global $tuanicp_filters;
    if (isset($tuanicp_filters[$tag])) {
        ksort($tuanicp_filters[$tag]);
        foreach ($tuanicp_filters[$tag] as $callbacks) {
            foreach ($callbacks as $callback) {
                $func_args = array(&$value);
                if ($callback['accepted_args'] > 1) {
                    $func_args = array_merge($func_args, array_slice($args, 0, $callback['accepted_args'] - 1));
                }
                $value = call_user_func_array($callback['function'], $func_args);
            }
        }
    }
    return $value;
}

function remove_action($tag, $function_to_remove, $priority = 10)
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

function remove_filter($tag, $function_to_remove, $priority = 10)
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

function do_shortcode($tag, $atts = array(), $content = null, $ignore_html = false)
{
    return apply_filters("shortcode_{$tag}", $content, $atts, $ignore_html);
}

function add_shortcode($tag, $func)
{
    add_filter("shortcode_{$tag}", $func, 10, 4);
}
