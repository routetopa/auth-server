<?php

require_once "config.php";
require_once "lib/render.php";
require_once "Auth/OpenID/Server.php";

/**
 * Set up the session
 */
function init()
{
    session_name('openid_server');
    session_start();
}

/**
 * Get the style markup
 */
function getStyle()
{
    $parent = rtrim(dirname(getServerURL()), '/');
    $url = htmlspecialchars($parent . '/openid-server.css', ENT_QUOTES);
    return sprintf('<link rel="stylesheet" type="text/css" href="%s" />', $url);
}

/**
 * Get the URL of the current script
 */
function getServerURL()
{
    $path = $_SERVER['SCRIPT_NAME'];
    $host = $_SERVER['HTTP_HOST'];
    $port = $_SERVER['SERVER_PORT'];
    $s = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '';
    if (($s && $port == "443") || (!$s && $port == "80")) {
        $p = '';
    } else {
        $p = ':' . $port;
    }

    return "http$s://$host$p$path";
}

/**
 * Build a URL to a server action
 */
function buildURL($action=null, $escaped=true)
{
    $url = getServerURL();
    if ($action) {
        $url .= '/' . $action;
    }
    return $escaped ? htmlspecialchars($url, ENT_QUOTES) : $url;
}

function getPathComponents($index=null)
{
    $path_info = @$_SERVER['PATH_INFO'];
    $path_components = ($path_info) ? array_values(array_filter(explode( substr($path_info, 0, 1), $path_info ))) : [];
    $path_components = count($path_components) ? $path_components : [''];
    return (null===$index) ? $path_components : (array_key_exists($index, $path_components) ? $path_components[$index] : null);
}

/**
 * Extract the current action from the request
 */
function getAction()
{
    $path_components = getPathComponents();
    $action = $path_components[0];
    $function_name = 'action_' . $action;
    return $function_name;
}

/**
 * Write the response to the request
 */
function writeResponse($resp)
{
    list ($headers, $body) = $resp;
    array_walk($headers, 'header');
    header(header_connection_close);
    print $body;
}

/**
 * Instantiate a new OpenID server object
 */
function getServer()
{
    static $server = null;
    if (!isset($server)) {
        $server = new Auth_OpenID_Server(getOpenIDStore(),
                                         buildURL());
    }
    return $server;
}

/**
 * Return a hashed form of the user's password
 */
function hashPassword($password)
{
    return bin2hex(Auth_OpenID_SHA1($password));
}

/**
 * Get the openid_url out of the cookie
 *
 * @return mixed $openid_url The URL that was stored in the cookie or
 * false if there is none present or if the cookie is bad.
 */
function getLoggedInUser()
{
    return isset($_SESSION['openid_url'])
        ? $_SESSION['openid_url']
        : false;
}

function getLoggedInProfile()
{
    return isset($_SESSION['openid_url']) && isset($_SESSION['openid_user'])
        ? unserialize($_SESSION['openid_user'])
        : false;
}

/**
 * Set the openid_url in the cookie
 *
 * @param mixed $identity_url The URL to set. If set to null, the
 * value will be unset.
 */
function setLoggedInUser($user=null)
{
    if (!isset($user)) {
        unset($_SESSION['openid_url']);
        unset($_SESSION['openid_user']);
    } else {
        $_SESSION['openid_url'] = $user['uuid'];
        $_SESSION['openid_user'] = serialize($user);
    }
}

function getRequestInfo()
{
    return isset($_SESSION['request'])
        ? unserialize($_SESSION['request'])
        : false;
}

function setRequestInfo($info=null)
{
    if (!isset($info)) {
        unset($_SESSION['request']);
    } else {
        $_SESSION['request'] = serialize($info);
    }
}


function getSreg($identity)
{
    // from config.php
    global $openid_sreg;

    if (!is_array($openid_sreg)) {
        return null;
    }

    return $openid_sreg[$identity];

}

function idURL($identity)
{
    return buildURL('idpage') . "?user=" . $identity;
}

function idFromURL($url)
{
    if (strpos($url, 'idpage') === false) {
        return null;
    }

    $parsed = parse_url($url);

    $q = $parsed['query'];

    $parts = array();
    parse_str($q, $parts);

    return @$parts['user'];
}

/**
 * Function to create and display error and success messages
 * @access public
 * @param string session name
 * @param string message
 * @param string display class
 * @return string message
 */
function flash( $name = '', $message = '', $class = 'success fadeout-message' )
{
    //We can only do something if the name isn't empty
    if( !empty( $name ) )
    {
        //No message, create it
        if( !empty( $message ) && empty( $_SESSION[$name] ) )
        {
            if( !empty( $_SESSION[$name] ) )
            {
                unset( $_SESSION[$name] );
            }
            if( !empty( $_SESSION[$name.'_class'] ) )
            {
                unset( $_SESSION[$name.'_class'] );
            }

            $_SESSION[$name] = $message;
            $_SESSION[$name.'_class'] = $class;
        }
        //Message exists, display it
        elseif( !empty( $_SESSION[$name] ) && empty( $message ) )
        {
            $class = !empty( $_SESSION[$name.'_class'] ) ? $_SESSION[$name.'_class'] : 'success';
            echo '<div class="'.$class.'" id="msg-flash">'.$_SESSION[$name].'</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name.'_class']);
        }
    }
}