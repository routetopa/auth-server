<?php

function db_generateUiid() {
    $attempts = 0;
    $uuid = false;
    $found = false;
    do {
        $attempts++;
        $uuid = Uuid::generate(4);
        $uuid = $uuid->string;
        $found = db_isUuidFree($uuid);
    } while ($attempts < 100 && !$found);

    return $found ? $uuid : false;
}

function db_isEmailFree($email) {
    $store = getOpenIDStore();
    $errors = [];
    $res = $store->connection->query(
        "SELECT email FROM users WHERE email = ? LIMIT 1",
        [ $email ]
    );
    if (PEAR::isError($res)) {
        $errors[] = 'Server error';
        return false;
    }
    return $res->numRows() == 0;
}

function db_isUuidFree($uuid) {
    $store = getOpenIDStore();
    $errors = [];
    $res = $store->connection->query(
        "SELECT uuid FROM users WHERE uuid = ? LIMIT 1",
        [ $uuid ]
    );
    if (PEAR::isError($res)) {
        $errors[] = 'Server error';
        return false;
    }
    return $res->numRows() == 0;
}

function db_getUsers() {
    $store = getOpenIDStore();
    $errors = [];
    $res = $store->connection->query(
        "SELECT uuid, email, is_admin FROM users"
    );
    if (PEAR::isError($res)) {
        $errors[] = 'Server error';
    }
    return (count($errors) > 0) ? false : $res;
}

function db_authUserByEmail($email, $password) {
    $store = getOpenIDStore();
    $errors = [];
    $res = $store->connection->query(
        "SELECT uuid, email, password, is_admin FROM users WHERE email = ?",
        [ $email ]
    );

    if (PEAR::isError($res)) {
        $errors[] = 'Server error';
    } else if (1 != $res->numRows()) {
        $errors[] = 'Invalid Username';
    }

    if (count($errors) == 0) {
        $user = $res->fetchRow();
        if (password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
    }

    return false;
}

function db_getUserByUuid($uuid) {
    $store = getOpenIDStore();
    $errors = [];
    $res = $store->connection->query(
        "SELECT uuid, email, is_admin FROM users WHERE uuid = ?",
        [ $uuid ]
    );
    if (PEAR::isError($res)) {
        $errors[] = 'Server error';
    }
    if ($res->numRows() <= 0) {
        return null;
    }
    return (count($errors) > 0) ? false : $res->fetchRow();
}

function db_saveUser($user) {
    $store = getOpenIDStore();
    $errors = [];
    $res = null;

    $query_string = 'UPDATE users SET email = ?';
    $query_params = [ $user['email'] ];

    if (array_key_exists('password', $user) && !empty($user['password'])) {
        $query_string .= ', PASSWORD = ?';
        $query_params[] = password_hash($user['password'], PASSWORD_DEFAULT);
    }

    $query_string .= ' WHERE uuid = ?';
    $query_params[] = $user['uuid'];

    $res = $store->connection->query($query_string, $query_params);

    if (PEAR::isError($res)) {
        $errors[] = 'Server error';
        $store->connection->rollback();
    }

    $store->connection->commit();

    return (count($errors) > 0) ? false : $user;
}

function db_createUser($user) {
    $store = getOpenIDStore();
    $errors = [];
    $res = null;

    if (!db_isEmailFree($user['email'])) {
        $errors[] = 'Email already registered';
        return [$user, $errors];
    }

    $attempts = 0;
    do {
        $created = true;
        $attempts++;

        $uuid = db_generateUiid();

        if (!$uuid) {
            $errors[] = 'Could not generate UIID';
            break; // exit from do..while
        } else {
            $errors = [];

            $res = $store->connection->query(
                'INSERT INTO users(uuid, email, password) VALUES (?, ?, ?)',
                [$uuid, $user['email'], password_hash($user['password'], PASSWORD_DEFAULT)]
            );

            if (PEAR::isError($res)) {
                $created = false;
                if ($res->code == -5) {
                    $errors[] = 'Could not generate UUID';
                } else {
                    $errors[] = 'Server error';
                }
            }
        }
    } while ($attempts < 15 && !$created);

    if (count($errors) == 0) {
        $store->connection->commit();
    } else {
        $store->connection->rollback();
    }

    return [$user, $errors];
}

function db_deleteUser($user) {
    $store = getOpenIDStore();
    $errors = [];
    $res = null;

    $res = $store->connection->query(
        'DELETE FROM users WHERE uuid = ? LIMIT 1',
        [ $user['uuid'] ]);

    if (PEAR::isError($res)) {
        $errors[] = 'Server error';
        $store->connection->rollback();
    }

    $store->connection->commit();

    return [$user, $errors];
}