<?php

//define('logged_in_pat', 'You are logged in as %s (URL: %s)');
define('logged_in_pat', '%s');

/**
 * HTTP response line contstants
 */
define('http_bad_request', 'HTTP/1.1 400 Bad Request');
define('http_found', 'HTTP/1.1 302 Found');
define('http_ok', 'HTTP/1.1 200 OK');
define('http_internal_error', 'HTTP/1.1 500 Internal Error');

/**
 * HTTP header constants
 */
define('header_connection_close', 'Connection: close');
define('header_content_text', 'Content-Type: text/plain; charset=us-ascii');

define('redirect_message',
       'Please wait; you are being redirected to <%s>');

function build_menu() {
    global $config;
    global $server_url;
    $nav = [];
    $user = getLoggedInProfile();
    if ($user) {
        $nav[] = [ 'title' => $user['email'], 'url' => '' ];
        $nav[] = [ 'key' => 'nav_profile', 'url' => $server_url . '/profile' ];
        if ($user['is_admin']) {
            $nav[] = [ 'key' => 'nav_admin', 'url' => $server_url . '/admin_users' ];
        }
        $nav[] = [ 'key' => 'nav_logout', 'url' => $server_url . '/logout' ];
    } else {
        $nav[] = [ 'key' => 'nav_login', 'url' => $server_url . '/login' ];
        if ($config['allow_signin']) {
            $nav[] = [ 'key' => 'nav_signin', 'url' => $server_url . '/signin' ];
        }
    }
    return $nav;
}

/**
 * Return a string containing an anchor tag containing the given URL
 *
 * The URL does not need to be quoted, but if text is passed in, then
 * it does.
 */
function link_render($url, $text=null, $cssclass=null) {
    $esc_url = htmlspecialchars($url, ENT_QUOTES);
    $text = ($text === null) ? $esc_url : $text;
    $cssclass = ($cssclass === null) ? '' : "class=\"$cssclass\"";
    return sprintf('<a %s href="%s">%s</a>', $cssclass, $esc_url, $text);
}

/**
 * Return an HTTP redirect response
 */
function redirect_render($redir_url)
{
    $headers = array(http_found,
                     header_content_text,
                     header_connection_close,
                     'Location: ' . $redir_url,
                     );
    $body = sprintf(redirect_message, $redir_url);
    return array($headers, $body);
}

function navigation_render($msg, $items)
{
    //$what = link_render(buildURL(), 'PHP OpenID Server');
    //if ($msg) {
        //$what .= ' &mdash; ' . $msg;
    //    $what = $msg;
    //}

    $s = $msg;

    if ($items) {
        //'<p>' . $what . '</p>';
        foreach ($items as $action => $text) {
            $url = buildURL($action);
            $s .= link_render($url, $text, 'ow_console_item');
        }
    } //else {
       // $s = '<p class="bottom">' . $what . '</p>';
   // }
    return $s;
    //return sprintf('<div class="navigation">%s</div>', $s);
}

/**
 * Render an HTML page
 */
function page_render($body, $user, $title, $h1=null, $login=false)
{
    $h1 = $h1 ? $h1 : $title;

    if ($user) {
        $msg = sprintf(logged_in_pat, link_render(idURL($user), $user, 'ow_console_item'),
                       link_render(idURL($user)));
        $nav = array('logout' => 'Log Out');

        $navigation = navigation_render($msg, $nav);
    } else {
        if (!$login) {
            $msg = '';
            $nav = array('login' => 'Sign In');
            $navigation = navigation_render($msg, $nav);
        } else {
            $navigation = '';
        }
    }

    $page_template = file_get_contents(realpath(dirname(__FILE__)) . '/render/template.html');
    $style = getStyle();
    $text = sprintf($page_template, $title, $style, $navigation, $h1, $body);
    // No special headers here
    $headers = array();
    return array($headers, $text);
}