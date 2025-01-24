<?php
if (!defined('APP_ROOT')) {
    exit('Direct access is not allowed.');
}
/**
 * @param $key
 * @return mixed|null
 * @throws JsonException
 */
function get_Config($key): mixed
{
    $dbc = initDatabase();
    $query = "SELECT v FROM config WHERE k = :key";
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':key', $key);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        return $result['v'];
    } else {
        return null;
    }
}

/**
 * @param $key
 * @param $value
 * @return bool
 * @throws JsonException
 */
function set_Config($key, $value): bool
{
    $dbc = initDatabase();
    $query = "INSERT INTO config (k, v) VALUES (:key, :value) ON DUPLICATE KEY UPDATE v = :value";
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':key', $key);
    $stmt->bindParam(':value', $value);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

/**
 * @return mixed|string
 * @throws JsonException
 */
function get_Site_name()
{
    $site_name = get_Config('site_name');
    if ($site_name === null){
        set_Config('site_name','云团子');
        return '云团子';
    }
    return $site_name;
}

/**
 * @return mixed
 * @throws JsonException
 */
function get_Template_name(): mixed
{
    $template_name = get_Config('template_name');
    if ($template_name === null){
        set_Config('template_name','tuan');
        return 'tuan';
    }
    return $template_name;
}