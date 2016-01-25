<?php

require_once "lib/common.php";
require_once "lib/session.php";
require_once "lib/render.php";

require_once "lib/dbservice.php";

require_once "lib/render/login.php";
require_once "lib/render/idpage.php";
require_once "lib/render/idpXrds.php";
require_once "lib/render/userXrds.php";

require_once "Auth/OpenID.php";

/**
 * Handle a standard OpenID server request
 */
function action_default()
{
    header('X-XRDS-Location: '.buildURL('idpXrds'));

    $server = getServer();
    $method = $_SERVER['REQUEST_METHOD'];
    $request = null;
    if ($method == 'GET') {
        $request = $_GET;
    } else {
        $request = $_POST;
    }

    $request = $server->decodeRequest();

    if (!$request) {
        $t = getSmarty();
        $t->assign('right_menu', build_menu());
        return [ array(), $t->fetch('index.tpl') ];
        //return about_render();
    }

    setRequestInfo($request);

    if (in_array($request->mode,
                 array('checkid_immediate', 'checkid_setup'))) {

        if ($request->idSelect()) {
            // Perform IDP-driven identifier selection
            if ($request->mode == 'checkid_immediate') {
                $response = $request->answer(false);
            } else {
                if (!getLoggedInUser()) {
                    return login_render();
                }
                return trust_render($request);
            }
        } else if ((!$request->identity) &&
                   (!$request->idSelect())) {
            // No identifier used or desired; display a page saying
            // so.
            return noIdentifier_render();
        } else if ($request->immediate) {
            $response = $request->answer(false, buildURL());
        } else {
            if (!getLoggedInUser()) {
                return login_render();
            }
            return trust_render($request);
        }
    } else {
        $response = $server->handleRequest($request);
    }

    $webresponse = $server->encodeResponse($response);

    if ($webresponse->code != AUTH_OPENID_HTTP_OK) {
        header(sprintf("HTTP/1.1 %d ", $webresponse->code),
               true, $webresponse->code);
    }

    foreach ($webresponse->headers as $k => $v) {
        header("$k: $v");
    }

    header(header_connection_close);
    print $webresponse->body;
    exit(0);
}

/**
 * Log out the currently logged in user
 */
function action_logout()
{
    setLoggedInUser(null);
    setRequestInfo(null);
    return authCancel(null);
}

/**
 * Check the input values for a login request
 */
function login_checkInput($input)
{
    $user = false;
    $errors = array();

    if (empty($input['openid_url'])) {
        $errors[] = 'Enter an OpenID URL to continue';
    }
    if (empty($input['openid_pass'])) {
        $errors[] = 'Enter password to continue';
    }

    $user = db_authUserByEmail($input['openid_url'], $input['openid_pass']);

    return array($errors, $user);
}

/**
 * Log in a user and potentially continue the requested identity approval
 */
function action_login()
{
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
    case 'GET':
        return login_render();
    case 'POST':
        $info = getRequestInfo();
        $fields = $_POST;
        if (isset($fields['cancel'])) {
            return authCancel($info);
        }

        list ($errors, $user) = login_checkInput($fields);
        if (count($errors) || !$user) {
            $needed = $info ? $info->identity : false;
            return login_render($errors, @$fields['openid_url'], $needed);
        } else {
            flash('message', 'You are now logged in.');
            setLoggedInUser($user);
            return doAuth($info);
        }
    default:
        return login_render(array('Unsupported HTTP method: $method'));
    }
}

/**
 * Ask the user whether he wants to trust this site
 */
function action_trust()
{
    $info = getRequestInfo();
    $trusted = isset($_POST['trust']);
    //return doAuth($info, $trusted, true, @$_POST['idSelect']);
    return doAuth($info, $trusted, true, getLoggedInUser());
}

function action_idpage()
{
    $identity = $_GET['user'];
    return idpage_render($identity);
}

function action_idpXrds()
{
    return idpXrds_render();
}

function action_userXrds()
{
    $identity = $_GET['user'];
    return userXrds_render($identity);
}

function action_profile() {
    $user = getLoggedInProfile();
    $errors = [];

    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
        case 'GET';
            $t = getSmarty();
            $t->assign('user', $user);
            $t->assign('errors', $errors);
            $t->assign('right_menu', build_menu());
            return [ array(), $t->fetch('profile.tpl') ];
            break;
        case 'POST':
            $uuid = $user['uuid'];
            $uuid_confirm = @$_POST['uuid'];
            $email = trim(@$_POST['email']);
            $password = @$_POST['password'];
            $password_confirm = @$_POST['password_confirm'];

            if (empty($uuid_confirm)) {
                $errors[] = 'Request not valid';
            }
            if ($uuid != $uuid_confirm) {
                $errors[] = 'Request not valid';
            }
            if (empty($email)) {
                //$errors[] = 'Email required';
            }
            if ($password != $password_confirm) {
                $errors[] = 'Password and confirmation do not match';
            }
            if ($user === false) {
                $errors[] = 'Internal error, please contact system administrator';
            }
            if ($user === null) {
                $errors[] = 'User not found';
            }

            //$user['email'] = $email;

            if (count($errors) > 0) {
                $t = getSmarty();
                $t->assign('user', $user);
                $t->assign('errors', $errors);
                $t->assign('right_menu', build_menu());
                return [array(), $t->fetch('profile.tpl')];
            }

            $user['password'] = $password;
            $res = db_saveUser($user);
            flash('message', 'Your profile was successfully updated');
            return redirect_render('../');
            break;
        default:
            return '';
    }
}

function action_forgotpassword() {
    // todo
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
        case 'GET';
            $t = getSmarty();
            return [ array(), $t->fetch('forgot_password.tpl') ];
            break;
        case 'POST':

        default:
            return '';
    }
}