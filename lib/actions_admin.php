<?php

require_once('render.php');
require_once('dbservice.php');
require_once('Uuid.php');

function admin_check_auth() {
    $auth = getLoggedInProfile();
    if (!$auth || !$auth['is_admin']) {
        return [
            [ 'HTTP/1.0 403 Forbidden' ],
            'Forbidden'
        ];
    }
    return true;
}

function action_admin_users() {
    if ( true !== ($authres = admin_check_auth())) { return $authres; }

    $res = db_getUsers();

    $t = getSmarty();
    $t->assign( 'users', $res );
    return [ array(), $t->fetch('admin_users_index.tpl') ];
}

function action_admin_users_edit() {
    if ( true !== ($authres = admin_check_auth())) { return $authres; }

    $uuid = getPathComponents(1);

    $user = db_getUserByUuid($uuid);
    $errors = [];

    $method = strtoupper($_SERVER['REQUEST_METHOD']);

    switch ($method) {

        case 'GET':
            if ($user === false) {
                // Error
            } else if ($user === null) {
                // Not found
            } else {
                $t = getSmarty();
                $t->assign('user', $user);
                $t->assign('errors', $errors);
                return [array(), $t->fetch('admin_users_edit.tpl')];
            }
            break;

        case 'POST':
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
                $errors[] = 'Email required';
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

            $user['email'] = $email;

            if (count($errors) > 0) {
                $t = getSmarty();
                $t->assign('user', $user);
                $t->assign('errors', $errors);
                return [array(), $t->fetch('admin_users_edit.tpl')];
            }

            $user['password'] = $password;
            $res = db_saveUser($user);
            return redirect_render('../admin_users');
            break;

        default:
            die();
    }
}

function action_admin_users_create() {
    if ( true !== ($authres = admin_check_auth())) { return $authres; }

    $user = [];
    $errors = [];

    $method = strtoupper($_SERVER['REQUEST_METHOD']);

    switch ($method) {

        case 'GET':
            $t = getSmarty();
            $t->assign('user', $user);
            $t->assign('errors', $errors);
            return [array(), $t->fetch('admin_users_edit.tpl')];
            break;

        case 'POST':
            $email = trim(@$_POST['email']);
            $password = @$_POST['password'];
            $password_confirm = @$_POST['password_confirm'];

            if (empty($email)) {
                $errors[] = 'Email required';
            }
            if (empty($password) || empty($password_confirm)) {
                $errors[] = 'Password required';
            }
            if ( $password && $password_confirm && $password != $password_confirm) {
                $errors[] = 'Password and confirmation do not match';
            }

            $user['email'] = $email;

            if (count($errors) > 0) {
                $t = getSmarty();
                $t->assign('user', $user);
                $t->assign('errors', $errors);
                return [array(), $t->fetch('admin_users_edit.tpl')];
            }

            $user['password'] = $password;
            list($user, $errors) = db_createUser($user);

            if (count($errors) > 0) {
                $t = getSmarty();
                $t->assign('user', $user);
                $t->assign('errors', $errors);
                return [array(), $t->fetch('admin_users_edit.tpl')];
            }

            return redirect_render('admin_users');
            break;

        default:
            die();
    }
}

function action_admin_users_delete() {
    if ( true !== ($authres = admin_check_auth())) { return $authres; }

    $uuid = getPathComponents(1);

    $user = db_getUserByUuid($uuid);
    $errors = [];

    $method = strtoupper($_SERVER['REQUEST_METHOD']);

    switch ($method) {

        case 'GET':
            if ($user === false) {
                // Error
            } else if ($user === null) {
                // Not found
            } else {
                $t = getSmarty();
                $t->assign('user', $user);
                $t->assign('errors', $errors);
                return [array(), $t->fetch('admin_users_delete.tpl')];
            }
            break;

        case 'POST':
            $uuid_confirm = @$_POST['uuid'];

            if (array_key_exists('cancel', $_POST)) {
                return redirect_render('../admin_users');
            }

            if ($uuid != $uuid_confirm) {
                $errors[] = 'Request not valid';
            }
            if ($user === false) {
                $errors[] = 'Internal error, please contact system administrator';
            }
            if ($user === null) {
                $errors[] = 'User not found';
            }
            if ($uuid == getLoggedInUser()) {
                $errors[] = 'You can\'t delete yourself!';
            }

            if (count($errors) > 0) {
                $t = getSmarty();
                $t->assign('user', $user);
                $t->assign('errors', $errors);
                return [array(), $t->fetch('admin_users_delete.tpl')];
            }

            $res = db_deleteUser($user);
            return redirect_render('../admin_users');
            break;

        default:
            die();
    }
}
