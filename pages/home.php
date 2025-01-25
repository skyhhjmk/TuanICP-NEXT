<?php

if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
$twig = initTwig();
echo $twig->render('@index/home.html.twig', get_Page_vars());