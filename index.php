<?php

$path_extra = (dirname(dirname(__FILE__)));
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
$path .= PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc';
$path .= PATH_SEPARATOR . $path_extra . DIRECTORY_SEPARATOR . 'ow_libraries' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'smarty' .DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR . 'libs';

ini_set('include_path', $path);

$try_include = @include 'config.php';

if (!$try_include) {
    header("Location: setup.php");
}

header('Cache-Control: no-cache');
header('Pragma: no-cache');

if (function_exists('getOpenIDStore')) {
    require_once 'lib/session.php';
    require_once 'lib/actions.php';
    require_once 'lib/actions_admin.php';

    init();

    $action = getAction();
    if (!function_exists($action)) {
        $action = 'action_default';
    }

    $resp = $action();

    writeResponse($resp);
} else {
?>
<html>
  <head>
    <title>PHP OpenID Server</title>
    <body>
      <h1>PHP OpenID Server</h1>
      <p>
        This server needs to be configured before it can be used. Edit
        <code>config.php</code> to reflect your server's setup, then
        load this page again.
      </p>
    </body>
  </head>
</html>
<?php
}
?>
