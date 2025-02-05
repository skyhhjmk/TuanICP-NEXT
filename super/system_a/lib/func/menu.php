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

function get_menus_html($menu_id = null, $menu_style = null)
{
    global $menu_items;
    $html = '';

    if ($menu_id !== null) {
        $menus = get_menus($menu_id);
        if ($menus !== null) {
            switch ($menu_style) {
                case 'footer':
                    foreach ($menus as $menu) {
                        $html .= '<a href="' . htmlspecialchars($menu['slug']) . '">' . htmlspecialchars($menu['name']) . '</a> ';
                        if (!empty($menu['children'])) {
                            foreach ($menu['children'] as $child) {
                                $html .= '<a href="' . htmlspecialchars($child['slug']) . '">' . htmlspecialchars($child['name']) . '</a> ';
                            }
                        }
                    }
                    break;
                default:
                    foreach ($menus as $menu) {
                        $html .= '<a href="' . htmlspecialchars($menu['slug']) . '">' . htmlspecialchars($menu['name']) . '</a> ';
                    }
                    break;
            }
        }
    } else {
        $menus = get_menus();
        switch ($menu_style) {
            case 'footer':
                foreach ($menus as $menu) {
                    foreach ($menu as $item) {
                        $html .= '<a href="' . htmlspecialchars($item['slug']) . '">' . htmlspecialchars($item['name']) . '</a> ';
                        if (!empty($item['children'])) {
                            foreach ($item['children'] as $child) {
                                $html .= '<a href="' . htmlspecialchars($child['slug']) . '">' . htmlspecialchars($child['name']) . '</a> ';
                            }
                        }
                    }
                }
                break;
            default:
                foreach ($menus as $menu) {
                    foreach ($menu as $item) {
                        $html .= '<a href="' . htmlspecialchars($item['slug']) . '">' . htmlspecialchars($item['name']) . '</a> ';
                    }
                }
                break;
        }
    }

    return $html;
}