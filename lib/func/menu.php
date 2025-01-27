<?php
// 全局数组用于存储多个菜单项
$menu_items = array();

// 添加菜单项的函数
function add_menu($menu_name,$menu_slug, $menu_data = array()) {
global $menu_items;
$menu_items[$menu_slug] = array(
'name' => $menu_name,
'slug' => $menu_slug,
'children' => array(),
'data' => $menu_data // 允许每个菜单定义额外的数据
);
}

// 添加子菜单项的函数
function add_submenu($menu_slug,$submenu_name, $submenu_slug,$submenu_data = array()) {
global $menu_items;
if (isset($menu_items[$menu_slug])) {
$menu_items[$menu_slug]['children'][] = array(
'name' => $submenu_name,
'slug' => $submenu_slug,
'data' => $submenu_data // 允许每个子菜单定义额外的数据
);
}
}

// 获取菜单的函数，用于传递给Twig
function get_menus() {
global $menu_items;
return $menu_items;
}
