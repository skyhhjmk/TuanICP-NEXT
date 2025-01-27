<?php
// 全局数组用于存储多个菜单项
$menu_items = array();

function add_menu($menu_id, $menu_name, $menu_slug, $menu_data = array())
{
    global $menu_items;
    $menu_items[$menu_id][] = array(
        'name' => $menu_name,
        'slug' => $menu_slug,
        'children' => array(),
        'data' => $menu_data
    );
}

function add_submenu($menu_id, $menu_slug, $submenu_name, $submenu_slug, $submenu_data = array())
{
    global $menu_items;
    foreach ($menu_items[$menu_id] as &$menu) {
        if ($menu['slug'] === $menu_slug) {
            $menu['children'][] = array(
                'name' => $submenu_name,
                'slug' => $submenu_slug,
                'data' => $submenu_data
            );
        }
    }
}

function get_menus($menu_id = null)
{
    global $menu_items;
    if ($menu_id !== null) {
        return isset($menu_items[$menu_id]) ? $menu_items[$menu_id] : null;
    }
    return $menu_items;
}
