<?php
/**
 * The URL for the server.
 *
 * This is the location of server.php. For example:
 *
 * $server_url = 'http://example.com/~user/server.php';
 *
 * This must be a full URL.
 */
$config = (include 'config.dev.php');
if (empty($config)) {
    $config = (include 'config.prod.php');
}
if (empty($config)) {
    die('Configuration file not found');
}
$server_url = $config['server_url'];

/**
 * Initialize an OpenID store
 *
 * @return object $store an instance of OpenID store (see the
 * documentation for how to create one)
 */
function getOpenIDStore()
{
    global $config;

    require_once 'Auth/OpenID/MySQLStore.php';
    require_once 'DB.php';

    $db = DB::connect($config['db']);

    if (PEAR::isError($db)) {
        return null;
    }

    $db->query("USE openid");
        
    $s = new Auth_OpenID_MySQLStore($db);

    $s->createTables();

    return $s;
}

function getSmarty() {
    require_once('Smarty.class.php');
    $libPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib';
    $s = new Smarty();
    $s->setTemplateDir($libPath . DIRECTORY_SEPARATOR . 'templates');
    $s->setCompileDir($libPath . DIRECTORY_SEPARATOR . 'templates_c');
    $s->setCacheDir($libPath . DIRECTORY_SEPARATOR . 'templates_cache');
    $s->setConfigDir($libPath . DIRECTORY_SEPARATOR . 'templates_conf');
    return $s;
}