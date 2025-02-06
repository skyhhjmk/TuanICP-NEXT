<?php
$GLOBALS['tuanicp_actions'] = [];
$GLOBALS['tuanicp_filters'] = [];

/**
 * 添加一个动作回调函数
 *
 * @param string $tag 动作的标签
 * @param callable $function_to_add 要添加的回调函数
 * @param int $priority 回调函数的优先级，默认为 10
 * @param int $accepted_args 回调函数接受的参数数量，默认为 1
 * @throws InvalidArgumentException 如果回调函数不可调用
 */
function add_action(string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1): void
{
    if (!is_callable($function_to_add)) {
        throw new InvalidArgumentException("The function '{$function_to_add}' is not callable");
    }

    $GLOBALS['tuanicp_actions'][$tag][$priority][] = [
        'function' => $function_to_add,
        'accepted_args' => $accepted_args,
    ];
}

/**
 * 执行指定标签的动作
 *
 * @param string $tag 动作的标签
 * @param mixed ...$args 传递给动作回调函数的参数
 */
function do_action(string $tag, ...$args): void
{
    if (isset($GLOBALS['tuanicp_actions'][$tag])) {
        // 对回调函数按照优先级进行排序
        ksort($GLOBALS['tuanicp_actions'][$tag]);
        foreach ($GLOBALS['tuanicp_actions'][$tag] as $callbacks) {
            foreach ($callbacks as$callback) {
                $func_args = array_slice($args, 0, $callback['accepted_args']);
                call_user_func_array($callback['function'], $func_args);
            }
        }
    }
}

/**
 * 添加一个过滤器回调函数
 *
 * @param string $tag 过滤器的标签
 * @param callable $function_to_add 要添加的回调函数
 * @param int $priority 回调函数的优先级，默认为 10
 * @param int $accepted_args 回调函数接受的参数数量，默认为 1
 * @throws InvalidArgumentException 如果回调函数不可调用
 */
function add_filter(string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1): void
{
    if (!is_callable($function_to_add)) {
        throw new InvalidArgumentException("The function '{$function_to_add}' is not callable");
    }

    $GLOBALS['tuanicp_filters'][$tag][$priority][] = [
        'function' => $function_to_add,
        'accepted_args' => $accepted_args,
    ];
}

/**
 * 应用指定标签的过滤器
 *
 * @param string $tag 过滤器的标签
 * @param mixed $value 要过滤的值
 * @param mixed ...$args 传递给过滤器回调函数的额外参数
 * @return mixed 经过所有过滤器处理后的最终值
 */
function apply_filters(string $tag, $value, ...$args): mixed
{
    if (isset($GLOBALS['tuanicp_filters'][$tag])) {
        // 对回调函数按照优先级进行排序
        ksort($GLOBALS['tuanicp_filters'][$tag]);
        foreach ($GLOBALS['tuanicp_filters'][$tag] as $callbacks) {
            foreach ($callbacks as$callback) {
                $func_args = [&$value];
                if ($callback['accepted_args'] > 1) {
                    $func_args = array_merge($func_args, array_slice($args, 0, $callback['accepted_args'] - 1));
                }
                $value = call_user_func_array($callback['function'], $func_args);
            }
        }
    }
    return $value;
}

/**
 * 移除指定标签和优先级的动作回调函数
 *
 * @param string $tag 动作的标签
 * @param callable $function_to_remove 要移除的回调函数
 * @param int $priority 回调函数的优先级，默认为 10
 * @return bool 如果成功移除返回 true，否则返回 false
 */
function remove_action(string $tag, callable $function_to_remove, int $priority = 10): bool
{
    if (isset($GLOBALS['tuanicp_actions'][$tag][$priority])) {
        foreach ($GLOBALS['tuanicp_actions'][$tag][$priority] as $index => $callback) {
            if ($callback['function'] === $function_to_remove) {
                unset($GLOBALS['tuanicp_actions'][$tag][$priority][$index]);
                return true;
            }
        }
    }

    return false;
}

/**
 * 移除指定标签和优先级的过滤器回调函数
 *
 * @param string $tag 过滤器的标签
 * @param callable $function_to_remove 要移除的回调函数
 * @param int $priority 回调函数的优先级，默认为 10
 * @return bool 如果成功移除返回 true，否则返回 false
 */
function remove_filter(string $tag, callable $function_to_remove, int $priority = 10): bool
{
    if (isset($GLOBALS['tuanicp_filters'][$tag][$priority])) {
        foreach ($GLOBALS['tuanicp_filters'][$tag][$priority] as $index => $callback) {
            if ($callback['function'] === $function_to_remove) {
                unset($GLOBALS['tuanicp_filters'][$tag][$priority][$index]);
                return true;
            }
        }
    }

    return false;
}

/**
 * 检查指定标签是否有动作回调函数
 *
 * @param string $tag 动作的标签
 * @param bool $with_priority 是否返回动作的优先级
 * @return bool|array 如果有动作回调函数返回 true 或优先级数组，否则返回 false
 */
function has_action(string $tag, bool $with_priority = false): bool|array
{
    if (isset($GLOBALS['tuanicp_actions'][$tag])) {
        return $with_priority ? array_keys($GLOBALS['tuanicp_actions'][$tag]) : true;
    }

    return false;
}

/**
 * 检查指定标签是否有过滤器回调函数
 *
 * @param string $tag 过滤器的标签
 * @param bool $with_priority 是否返回过滤器的优先级
 * @return bool|array 如果有过滤器回调函数返回 true 或优先级数组，否则返回 false
 */
function has_filter(string $tag, bool $with_priority = false): bool|array
{
    if (isset($GLOBALS['tuanicp_filters'][$tag])) {
        return $with_priority ? array_keys($GLOBALS['tuanicp_filters'][$tag]) : true;
    }

    return false;
}

// 以下是短代码部分，暂时在测试中
$GLOBALS['shortcode_tags'] = [];

/**
 * 注册一个短代码处理函数
 *
 * @param string $tag 短代码标签
 * @param callable $function_to_add 处理短代码的回调函数
 * @throws InvalidArgumentException 如果回调函数不可调用
 */
function add_shortcode(string $tag, callable$function_to_add): void
{
    if (!is_callable($function_to_add)) {
        throw new InvalidArgumentException("The function '{$function_to_add}' is not callable");
    }

    $GLOBALS['shortcode_tags'][$tag] = $function_to_add;
}

/**
 * 移除已注册的短代码处理函数
 *
 * @param string $tag 短代码标签
 */
function remove_shortcode(string $tag): void
{
    unset($GLOBALS['shortcode_tags'][$tag]);
}

/**
 * 执行短代码处理
 *
 * @param string $content 包含短代码的内容
 * @return string 替换短代码后的内容
 */
function do_shortcode(string $content): string
{
    if (empty($GLOBALS['shortcode_tags']) || !is_string($content)) {
        return $content;
    }

    $pattern = get_shortcode_regex();
    return preg_replace_callback("/$pattern/s", 'do_shortcode_tag',$content);
}

/**
 * 获取短代码的正则表达式
 *
 * @return string 短代码的正则表达式
 */
function get_shortcode_regex(): string
{
    $tagnames = array_keys($GLOBALS['shortcode_tags']);
    $tagregexp = join('|', array_map('preg_quote',$tagnames));

    return '\\[(\\[?)(' . $tagregexp . ')(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\[?)';
}

/**
 * 处理单个短代码标签
 *
 * @param array $m 匹配的短代码数组
 * @return string 处理后的短代码内容
 */
function do_shortcode_tag(array $m): string
{
    // 允许短代码嵌套
    if (isset($m[1]) && '/' ===$m[1]) {
        return '';
    }

    if (isset($m[6])) {
        return $m[6];
    }

    $tag =$m[2];
    $attr = shortcode_parse_atts($m[3]);

    if (isset($GLOBALS['shortcode_tags'][$tag])) {
        return call_user_func($GLOBALS['shortcode_tags'][$tag], $attr,$m[5], $tag);
    }

    return $m[0];
}

/**
 * 解析短代码属性
 *
 * @param string $text 短代码属性字符串
 * @return array 解析后的属性数组
 */
function shortcode_parse_atts(string $text): array
{
    $atts = [];
    $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ",$text);
    if (preg_match_all($pattern,$text, $match, PREG_SET_ORDER)) {
        foreach ($match as$m) {
            if (!empty($m[1])) {
                $atts[strtolower($m[1])] = stripcslashes($m[2]);
            } elseif (!empty($m[3])) {
                $atts[strtolower($m[3])] = stripcslashes($m[4]);
            } elseif (!empty($m[5])) {
                $atts[strtolower($m[5])] = stripcslashes($m[6]);
            } elseif (isset($m[7]) && strlen($m[7])) {
                $atts[] = stripcslashes($m[7]);
            } elseif (isset($m[8])) {
                $atts[] = stripcslashes($m[8]);
            }
        }
    } else {
        $atts = ltrim($text);
    }
    return $atts;
}