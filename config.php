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
$server_url = "http://spod.routetopa.eu/openid";

$server_url = 'http://localhost/openid/index.php';

/**
 * Initialize an OpenID store
 *
 * @return object $store an instance of OpenID store (see the
 * documentation for how to create one)
 */
function getOpenIDStore()
{
    require_once 'Auth/OpenID/MySQLStore.php';
    require_once 'DB.php';

    $dsn = array(
         'phptype'  => 'mysql',
         'username' => 'openid',
         'password' => 'sp0dopen1d82645',
         'hostspec' => 'localhost'
         );

    $dsn = array(
        'phptype'  => 'mysql',
        'username' => 'root',
        'password' => '',
        'hostspec' => 'localhost'
    );

    $db = DB::connect($dsn);

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