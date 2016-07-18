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
    $user = NULL;
    $res = $store->connection->query(
        "SELECT uuid, email, password, is_admin FROM users WHERE email = ?",
        [ $email ]
    );

    if (PEAR::isError($res)) {
        $errors[] = 'login_msg_server_error';
    } else if (1 != $res->numRows()) {
        $errors[] = 'login_msg_invalid';
    }

    if (count($errors) == 0) {
        $row = $res->fetchRow();
        if (TRUE === password_verify($password, $row['password'])) {
            unset($row['password']);
            $user = $row;
        } else {
            $errors[] = 'login_msg_invalid';
        }
    }
    return [$errors, $user];
}

function db_getUserByEmail($email, $include_unverified = false) {
    $store = getOpenIDStore();
    $errors = [];

    $verified_condition = $include_unverified ? '' : " AND (is_verified=1) ";

    $res = $store->connection->query(
        "SELECT uuid, email, is_admin, is_verified FROM users WHERE (email = ?) {$verified_condition}",
        [ $email ]
    );
    if (PEAR::isError($res)) {
        $errors[] = 'Server error';
    }
    if ($res->numRows() <= 0) {
        return null;
    }
    return (count($errors) > 0) ? false : $res->fetchRow();
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

function db_createPasswordResetToken($user) {
    $store = getOpenIDStore();
    $errors = [];
    $token = null;
    $res = null;

    while (true) { // Will exit with a break...
        $token = md5( $user['email'] . time() . openssl_random_pseudo_bytes(16) );
        $res = $store->connection->query(
            'DELETE FROM password_reset WHERE email = ?',
            [$user['email']]
        );

        if (!(PEAR::isError($res))) {
            $store->connection->query(
                'INSERT INTO password_reset(email, token, timestamp) VALUES (?, ?, NOW())',
                [$user['email'], md5($token)]);
        }
        if (PEAR::isError($res)) {
            $store->connection->rollback();
            $token = null;
        } else {
            $store->connection->commit();
            break;
        }
    }

    return [$token, $errors];
}

function db_checkPasswordResetToken($email, $token) {
    $store = getOpenIDStore();
    $isValid = false;
    $errors = [];
    $res = null;

    if (!$errors) {
        $res = $store->connection->query(
            'SELECT email, token, timestamp FROM password_reset WHERE email = ? AND DATEDIFF(timestamp, NOW()) < 2 ORDER BY timestamp DESC LIMIT 1',
            [$email]
        );
    }

    if (!$errors && PEAR::isError($res)) {
        $errors[] = 'Server error';
    }

    if (!$errors && $res->numRows() <= 0) {
        $errors[] = 'pwdrst_token_not_found';
    }

    if (!$errors) {
        $row = $res->fetchRow();
        if (md5($token) != $row['token']) {
            $errors[] = 'pwdrst_token_not_found';
        } else {
            $isValid = true;
        }
    }

    return [$isValid, $errors];
}

function db_removePasswordResetToken($user) {
    $store = getOpenIDStore();
    $isDeleted = false;
    $errors = [];
    $res = null;

    if (!$errors) {
        $res = $store->connection->query(
            'DELETE FROM password_reset WHERE email = ? LIMIT 1',
            [$user['email']]
        );
    }

    if (PEAR::isError($res)) {
        $store->connection->rollback();
        $token = null;
        $isDeleted = false;
    } else {
        $store->connection->commit();
        $isDeleted = true;
    }

    return [$isDeleted, $errors];
}

function db_createEmailVerifyToken($user) {
    $store = getOpenIDStore();
    $errors = [];
    $token = null;
    $res = null;

    while (true) { // Will exit with a break...
        $token = md5( $user['email'] . time() . openssl_random_pseudo_bytes(16) );
        $res = $store->connection->query(
            'DELETE FROM email_verify WHERE email = ?',
            [$user['email']]
        );

        if (!(PEAR::isError($res))) {
            $store->connection->query(
                'INSERT INTO email_verify(email, token, timestamp) VALUES (?, ?, NOW())',
                [$user['email'], md5($token)]);
        }
        if (PEAR::isError($res)) {
            $store->connection->rollback();
            $token = null;
        } else {
            $store->connection->commit();
            break;
        }
    }

    return [$token, $errors];
}

function db_checkEmailVerifyToken($email, $token) {
    $store = getOpenIDStore();
    $isValid = false;
    $errors = [];
    $res = null;

    if (!$errors) {
        $res = $store->connection->query(
            //'SELECT email, token, timestamp FROM email_verify WHERE email = ? AND DATEDIFF(timestamp, NOW()) < 2 ORDER BY timestamp DESC LIMIT 1',
            'SELECT email, token, timestamp FROM email_verify WHERE email = ? ORDER BY timestamp DESC LIMIT 1',
            [$email]
        );
    }

    if (!$errors && PEAR::isError($res)) {
        $errors[] = 'Server error';
    }

    if (!$errors && $res->numRows() <= 0) {
        $errors[] = 'signin_token_not_found';
    }

    if (!$errors) {
        $row = $res->fetchRow();
        if (md5($token) != $row['token']) {
            $errors[] = 'signin_token_not_found';
        } else {
            $res = $store->connection->query(
                'UPDATE users SET is_verified=1 WHERE email = ?',
                [$email]
            );
            if (PEAR::isError($res)) {
                $errors[] = 'Server error';
                $isValid = false;
            } else {
                $isValid = true;
            }
        }
    }

    return [$isValid, $errors];
}

function db_removeEmailVerifyToken($user) {
    $store = getOpenIDStore();
    $isDeleted = false;
    $errors = [];
    $res = null;

    if (!$errors) {
        $res = $store->connection->query(
            'DELETE FROM email_verify WHERE email = ? LIMIT 1',
            [$user['email']]
        );
    }

    if (PEAR::isError($res)) {
        $store->connection->rollback();
        $token = null;
        $isDeleted = false;
    } else {
        $store->connection->commit();
        $isDeleted = true;
    }

    return [$isDeleted, $errors];
}