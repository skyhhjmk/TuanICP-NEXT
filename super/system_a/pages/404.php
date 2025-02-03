<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
$twig = initTwig();

echo $twig->render('@index/404.html.twig', get_Page_vars());