<?php
/*
* Name:        帮助文档
* Description:        向后台添加一个帮助文档，提供系统的使用说明。
* Version:            1.0
* Author:             风屿Wind
*/

define('DOCUMENT_DIR', __DIR__);
add_menu('admin_sidebar', '帮助文档', get_Url('admin/document'));

function help_me_document_add_page_router($page_router)
{
    $page_router['admin']['document'] = DOCUMENT_DIR . '/pages/document.php';

    return $page_router;
}
add_filter('page_router', 'help_me_document_add_page_router');

