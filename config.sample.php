<?php

/**
 * Rename this file as 'config.dev.php' or 'congig.prod.php'
 */

return [
    'server_url' => 'http://spod.routetopa.eu/openid',
    'db' => [
        'phptype'  => 'mysql',
        'username' => 'your_sql_username',
        'password' => 'your_sql_password',
        'hostspec' => 'your_sql_hostname'
    ],
    'mail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'smtp_auth' => true,
        'smtp_secure' => 'tls',
        'username' => 'gmail-account@gmail.com',
        'password' => 'secret',
        'from_address' => 'gmail-account@gmail.com',
        'from_name' => 'The ROUTE-TO-PA Project',
    ],
    'trusted_roots' => [
        'localhost',
        'spod.routetopa.eu',
    ]
];