<?php



function getSetting($setting_name = null,$default_value = null)
{
    if ($setting_name === null) {
        return null;
    } else {
        $setting_value = get_Config($setting_name,$default_value);
        return $setting_value;
    }
}