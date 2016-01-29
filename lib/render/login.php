<?php

require_once "lib/session.php";
require_once "lib/render.php";

function login_render($errors=null, $input=null, $needed=null)
{
    $current_user = getLoggedInUser();
    if ($input === null) {
        $input = $current_user;
    }
    if ($needed) {

    }

    $esc_input = htmlspecialchars($input, ENT_QUOTES);
    $login_url = buildURL('login', true);
    //$body = sprintf(login_form_pat, idURL('USERNAME'), $login_url, $esc_input);
    /*
    if ($errors) {
        $body = loginError_render($errors) . $body;
    }
*/
    $t = getSmarty();
    $t->assign('login_url', $login_url);
    $t->assign('id_url', idURL('USERNAME'));
    $t->assign('password_reset_url', buildURL('password_reset'));
    $t->assign('email', $esc_input);
    $t->assign('errors', $errors);
    return [ array(), $t->fetch('login.tpl') ];

    return page_render($body, $current_user, 'Log In', null, true);
}

function loginError_render($errors)
{
    $text = '';
    foreach ($errors as $error) {
        $text .= sprintf("<li>%s</li>\n", $error);
    }
    return sprintf("<ul class=\"error\">\n%s</ul>\n", $text);
}
?>
