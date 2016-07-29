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
    $return_url = @$_GET['return_url'];
    setLoggedInUser(null);
    setRequestInfo(null);
    $authCancelRes = authCancel(null);

    return $return_url ? redirect_render($return_url) : $authCancelRes;
}

/**
 * Check the input values for a login request
 */
function login_checkInput($input)
{
    $user = NULL;
    $errors = [];

    if (empty($input['openid_url'])) {
        $errors[] = 'login_msg_no_email';
    }
    if (empty($input['openid_pass'])) {
        $errors[] = 'login_msg_no_password';
    }

    if (!$errors) {
        list($errors, $user) = db_authUserByEmail($input['openid_url'], $input['openid_pass']);
    }

    return [$errors, $user];
}

/**
 * Log in a user and potentially continue the requested identity approval
 */
function action_login()
{
    if (getLoggedInUser()) {
        return redirect_render(buildURL());
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $login_url = buildURL('login', true);

    switch ($method) {
    case 'GET':
        $t = getSmarty();
        $t->assign('login_url', $login_url);
        $t->assign('password_reset_url', buildURL('password_reset'));
        $t->assign('id_url', idURL('USERNAME'));
        $t->assign('email', '');
        return [ array(), $t->fetch('login.tpl') ];
    case 'POST':
        $info = getRequestInfo();
        $needed = $info ? $info->identity : false;

        $fields = $_POST;
        if (isset($fields['cancel'])) {
            return authCancel($info);
        }

        list ($errors, $user) = login_checkInput($fields);
        if (count($errors) || !$user) {
            if ($needed) {
                $errors[] = sprintf('login_needed', link_render($needed));
            }

            $t = getSmarty();
            $t->assign('login_url', $login_url);
            $t->assign('password_reset_url', buildURL('password_reset'));
            $t->assign('id_url', idURL('USERNAME'));
            $t->assign('email', @$fields['openid_url']);
            $t->assign('errors', $errors);
            $t->assign('needed', $needed);
            return [ array(), $t->fetch('login.tpl') ];
        } else {
            flash('message', 'message_login_ok');
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
            return [ array(), $t->fetch('profile.tpl') ];
            break;
        case 'POST':
            $uuid = $user['uuid'];
            $uuid_confirm = @$_POST['uuid'];
            $email = trim(@$_POST['email']);
            $password = @$_POST['password'];
            $password_confirm = @$_POST['password_confirm'];

            if (isset($_POST['cancel'])) {
                return redirect_render('../');
            }

            if (empty($uuid_confirm)) {
                $errors[] = 'profile_msg_unvalid_request';
            }
            if ($uuid != $uuid_confirm) {
                $errors[] = 'profile_msg_unvalid_request';
            }
            if (empty($email)) {
                //$errors[] = 'Email required';
            }
            if ($password != $password_confirm) {
                $errors[] = 'password_mismatch';
            }
            if ($user === false) {
                $errors[] = 'profile_msg_error_internal';
            }
            if ($user === null) {
                $errors[] = 'profile_msg_user_not_found';
            }

            //$user['email'] = $email;

            if (count($errors) > 0) {
                $t = getSmarty();
                $t->assign('user', $user);
                $t->assign('errors', $errors);
                return [array(), $t->fetch('profile.tpl')];
            }

            $user['password'] = $password;
            $res = db_saveUser($user);
            flash('message', 'profile_msg_update_ok');
            return redirect_render('../');
            break;
        default:
            return '';
    }
}

function action_language() {
    $lang = strtolower(@$_GET['lang'] ?: 'en');
    $_SESSION['lang'] = $lang;
    $ret = @$_GET['ret'] ?: buildURL();
    return redirect_render($ret);
}

function action_password_reset() {
    $method = $_SERVER['REQUEST_METHOD'];
    $reset_url = buildURL('password_reset', true);

    switch ($method) {
        case 'GET';
            $token = @$_GET['token'] ?: '';
            // ACCESS WITH TOKEN (ask new password)
            if ($token) {
                $email = @$_GET['user'];
                $user = null;
                $errors = [];
                $isValid = false;
                if (!$email) {
                    $errors[] = 'pwdrst_missing_parameter';
                }
                if (!$errors) {
                    $user = db_getUserByEmail($email);
                    if (!$user) {
                        $errors[] = 'pwdrst_missing_parameter';
                    }
                }
                if (!$errors) {
                    list($isValid, $errors) = db_checkPasswordResetToken($email, $token);
                }
                $t = getSmarty();
                $t->assign('token', $token);
                $t->assign('email', $user['email']);
                $t->assign('errors', $errors);
                return [ array(), $t->fetch('password_reset_change.tpl') ];

            }
            // SHOW SENT MESSAGE (access upon redirect)
            $sent = @$_GET['sent'] ?: '';
            if ($sent) {
                $t = getSmarty();
                $t->assign('email', urldecode($sent));
                return [ array(), $t->fetch('password_reset_sent.tpl') ];
            }
            // SHOW RESET MESSAGE (access upon redirect)
            $done = @$_GET['done'] ?: '';
            if ($done) {
                $t = getSmarty();
                $t->assign('email', urldecode($sent));
                return [ array(), $t->fetch('password_reset_done.tpl') ];
            }
            // ACCESS WITHOUT TOKEN (ask email to reset)
            $t = getSmarty();
            $t->assign('reset_url', $reset_url);
            $t->assign('email', '');
            return [ array(), $t->fetch('password_reset.tpl') ];
            break;
        case 'POST':
            // CHANGE PASSWORD
            if (@$_POST['action'] == 'do_reset') {
                $user = null;
                $errors = [];
                $email = @$_POST['email'] ?: '';
                $token = @$_POST['token'] ?: '';
                $password = @$_POST['password'] ?: '';
                $password_confirm = @$_POST['password_confirm'] ?: '';

                if (!$email || !$token) {
                    $errors[] = 'pwdrst_msg_unvalid_request';
                }
                if (!$password) {
                    $errors[] = 'pwdrst_msg_password_missing';
                } else if ($password !== $password_confirm) {
                    $errors[] = 'password_mismatch';
                }
                if (!$errors) {
                    $user = db_getUserByEmail($email);
                    if (!$user) {
                        $errors[] = 'pwdrst_msg_user_not_found';
                    }
                }
                if (!$errors) {
                    $user['password'] = $password;
                    $user = db_saveUser($user);
                    if (!$user) {
                        $errors[] = 'pwdrst_msg_error_internal';
                    }
                    if (!$errors) {
                        list($isDeleted, $errors) = db_removePasswordResetToken($user);
                    }
                    if (!$errors) {
                        return redirect_render( buildURL('password_reset?done=' . urlencode($email)) );
                    }
                }
                $t = getSmarty();
                $t->assign('token', $token);
                $t->assign('email', $email);
                $t->assign('errors', $errors);
                return [ array(), $t->fetch('password_reset_change.tpl') ];
            }
            // ASK FOR EMAIL
            $errors = [];
            $email = @$_POST['openid_url'] ?: '';
            $user = null;
            $t = getSmarty();
            $t->assign('reset_url', $reset_url);
            $t->assign('email', $email);

            if (!$email) {
                $errors[] = 'pwdrst_err_nomail';
            }

            if (!$errors) {
                $user = db_getUserByEmail($email);
                if (!$user) {
                    $errors[] = 'pwdrst_err_nouser';
                }
            }

            if (!$errors) {
                list($token, $errors) = db_createPasswordResetToken($user);
            }

            if ($errors) {
                $t->assign('errors', $errors);
                return [ array(), $t->fetch('password_reset.tpl') ];
            }

            if ($token) {
                global $config;
                require 'PHPMailerAutoload.php';

                $tokenUrl = buildURL('password_reset?user=' . urlencode($user['email']) . '&token=' . $token);

                $t = getSmarty();
                $t->assign('home_url', buildURL());
                $t->assign('reset_url', $tokenUrl);
                $mailBody = $t->fetch('password_reset.mail.tpl');
                $langs = $t->getTemplateVars('l');

                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->Host = $config['mail']['host'];
                $mail->SMTPAuth = $config['mail']['smtp_auth'];
                $mail->Username = $config['mail']['username'];
                $mail->Password = $config['mail']['password'];
                $mail->SMTPSecure = $config['mail']['smtp_secure'];
                $mail->Port = $config['mail']['port'];

                $mail->setFrom($config['mail']['from_address'], $langs['pswrst_email_from_name']);
                $mail->addAddress($user['email']);
                $mail->isHTML(true);

                $mail->Subject = 'ROUTE-TO-PA Password reset';
                $mail->Body    = $mailBody;
                $mail->AltBody = $langs['pswrst_email_content'] . "\r\n\r\n" . $tokenUrl;

                if(!$mail->send()) {
                    flash('message', 'message_mail_error', 'danger');
                    //echo 'Mailer Error: ' . $mail->ErrorInfo;
                } else {
                    flash('message', 'message_mail_ok');
                }
                return redirect_render( buildURL('password_reset?sent=' . urlencode($email)) );
            }

            return [ array(), $t->fetch('password_reset.tpl') ];
        default:
            return '';
    }
}

function action_signin() {
    global $config;
    if (!$config['allow_signin']) {
        return redirect_render( buildURL() );
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $signin_url = buildURL('signin', true);

    switch ($method) {
        case 'GET';
            $token = @$_GET['token'] ?: '';
            // ACCESS WITH TOKEN (verify token)

            if ($token) {
                $email = @$_GET['user'];
                $user = null;
                $errors = [];
                $isValid = false;
                if (!$email) {
                    $errors[] = 'signin_missing_parameter';
                }
                if (!$errors) {
                    $user = db_getUserByEmail($email, true);
                    if (!$user) {
                        $errors[] = 'signin_missing_parameter';
                    }
                }
                if (!$errors) {
                    list($isValid, $errors) = db_checkEmailVerifyToken($email, $token);
                }
                if (!$errors && $isValid) {
                    db_removeEmailVerifyToken($user);
                    setLoggedInUser($user);
                    //return redirect_render( buildURL('signin?done=' . urlencode($email)) );
                }
                $t = getSmarty();
                $t->assign('token', $token);
                $t->assign('email', $user['email']);
                $t->assign('errors', $errors);
                return [ array(), $t->fetch('signin_done.tpl') ];

            }
            // SHOW SENT MESSAGE (access upon redirect)
            $sent = @$_GET['sent'] ?: '';
            if ($sent) {
                $t = getSmarty();
                $t->assign('email', urldecode($sent));
                return [ array(), $t->fetch('signin_sent.tpl') ];
            }
            // ASK NEW ACCOUNT DETAILS
            $t = getSmarty();
            $t->assign('signin_url', $signin_url);
            return [ array(), $t->fetch('signin.tpl') ];
            break;
        case 'POST':
            $errors = [];
            $email = @$_POST['openid_url'] ?: '';
            $password = @$_POST['password'] ?: '';
            $password_confirm = @$_POST['password_confirm'] ?: '';
            $user = null;
            $t = getSmarty();
            $t->assign('signin_url', $signin_url);
            $t->assign('email', $email);

            if (!$email) {
                $errors[] = 'signin_err_nomail';
            }

            if (!$password) {
                $errors[] = 'signin_err_nopassword';
            }

            if (!$password_confirm) {
                $errors[] = 'signin_err_nopasswordconfirm';
            }

            if ($password !== $password_confirm) {
                $errors[] = 'signin_err_diffpassword';
            }

            // Check if user is present but not verified
            $user = db_getUserByEmail($email, true);;
            if ($user && $user['is_verified']==1) {
                $errors[] = 'signin_err_alreadyverified';
            }

            if (!$errors && $user) {
                $user['password'] = $password;
                db_saveUser($user);
            }

            if (!$errors && !$user) {
                $user = [
                    'email' => $email,
                    'password' => $password,
                ];
                list($user, $errors) = db_createUser($user);
            }

            if ($errors) {
                $t->assign('errors', $errors);
                return [ array(), $t->fetch('signin.tpl') ];
            }

            if ($user) {
                list($token, $errors) = db_createEmailVerifyToken($user);
            }

            if ($errors) {
                $t->assign('errors', $errors);
                return [ array(), $t->fetch('signin.tpl') ];
            }

            if ($user) {
                global $config;
                require 'PHPMailerAutoload.php';

                $tokenUrl = buildURL('signin?user=' . urlencode($user['email']) . '&token=' . $token);

                $t = getSmarty();
                $t->assign('home_url', buildURL());
                $t->assign('reset_url', $tokenUrl);
                $mailBody = $t->fetch('signin.mail.tpl');
                $langs = $t->getTemplateVars('l');

                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->Host = $config['mail']['host'];
                $mail->SMTPAuth = $config['mail']['smtp_auth'];
                $mail->Username = $config['mail']['username'];
                $mail->Password = $config['mail']['password'];
                $mail->SMTPSecure = $config['mail']['smtp_secure'];
                $mail->Port = $config['mail']['port'];

                $mail->setFrom($config['mail']['from_address'], $langs['signin_email_from_name']);
                $mail->addAddress($user['email']);
                $mail->isHTML(true);

                $mail->Subject =  $langs['signin_email_subject'];
                $mail->Body    = $mailBody;
                $mail->AltBody = $langs['signin_email_content'] . "\r\n\r\n" . $tokenUrl;

                if(!$mail->send()) {
                    flash('message', 'message_mail_error', 'danger');
                    //echo 'Mailer Error: ' . $mail->ErrorInfo;
                } else {
                    flash('message', 'message_mail_ok');
                }
                return redirect_render( buildURL('signin?sent=' . urlencode($email)) );
            }

            return [ array(), $t->fetch('signin.tpl') ];
        default:
            return '';
    }
}
