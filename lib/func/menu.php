<?php
// 全局数组用于存储多个菜单项
$menu_items = [];

/**
 * @param string $menu_id 菜单ID
 * @param string $menu_name 菜单名称
 * @param string $menu_slug 菜单URL
 * @param array $menu_data 菜单额外数据
 * @return void
 */
function add_menu(string $menu_id, string $menu_name, string $menu_slug, array $menu_data = []): void
{
    global $menu_items;
    $menu_items[$menu_id][] = [
        'name' => $menu_name,
        'slug' => $menu_slug,
        'children' => [],
        'data' => $menu_data
    ];
}

function add_submenu($menu_id, $menu_slug, $submenu_name, $submenu_slug, $submenu_data = []): void
{
    global $menu_items;
    foreach ($menu_items[$menu_id] as &$menu) {
        if ($menu['slug'] === $menu_slug) {
            $menu['children'][] = [
                'name' => $submenu_name,
                'slug' => $submenu_slug,
                'data' => $submenu_data
            ];
        }
    }
}

function get_menus($menu_id = null)
{
    global $menu_items;
    if ($menu_id !== null) {
        return $menu_items[$menu_id] ?? null;
    }
    return $menu_items;
}
