<?php

$path_extra = (dirname(dirname(__FILE__)));
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
$path .= PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc';
$path .= PATH_SEPARATOR . $path_extra . DIRECTORY_SEPARATOR . 'ow_libraries' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'smarty' .DIRECTORY_SEPARATOR . 'smarty' . D$

ini_set('include_path', $path);

$try_include = @include 'config.php';

if (!$try_include) {
    header("Location: setup.php");
}

header('Cache-Control: no-cache');
header('Pragma: no-cache');
header('Content-Type: application/json');

if (!function_exists('getOpenIDStore')) die();

require_once 'lib/session.php';
require_once 'lib/actions.php';
require_once 'lib/actions_admin.php';

init();

$method = $_SERVER['REQUEST_METHOD'];
if (strtoupper($method) != 'POST') die();

$username = $_POST['identity'];
$password = $_POST['password'];

list($errors, $user) = db_authUserByEmail($username, $password);

$message = $errors ? 'Authentication failed' : 'Authentication successfull';

$result = array(
        'result' => $user ? true : false,
        'message' => $message,
);

echo json_encode($result);
