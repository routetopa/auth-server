<?php

/*
 * Rename this file as 'config.dev.php' or 'congig.prod.php'
 */

return [
    /*
     * GENERAL CONFIGURATION
     */
    // Full URL of this OpenID provider index
    'server_url' => 'http://your-site/openid/index.php',

    // True if users can sign in, false if users shall be added by admin
    'allow_signin' => false,

    /*
     * DATABASE CONFIGURATION
     */
    'db' => [
        // Please leave 'mysql'
        'phptype'  => 'mysql',
        // MySQL username
        'username' => 'your_sql_username',
        // Database name
        'dbname' => 'openid',
        // MySQL password
        'password' => 'your_sql_password',
        // MySQL Server address. Use 'localhost' if the MySQL daemon is on the same host.
        'hostspec' => 'your_sql_hostname'
    ],

    /*
     * OUTGOING MAIL CONFIGURATION
     */
    'mail' => [
        // Mail server address
        'host' => 'smtp.gmail.com',
        // Mail server listening port
        'port' => 587,
        // true if authentication is required
        'smtp_auth' => true,
        // Authentication type ('tls', 'ssl')
        'smtp_secure' => 'tls',
        // SMTP username
        'username' => 'gmail-account@gmail.com',
        // SMTP password
        'password' => 'secret',
        // Sender e-mail address (usually the same as username)
        'from_address' => 'gmail-account@gmail.com',
        // Sender name (something descriptive)
        'from_name' => 'The ROUTE-TO-PA Project',
    ],

    /*
     * TRUSTED HOSTS
     * A list of servers that the OpenID provider will automatically trust
     * Only insert full hostnames (without protocol, subdirectories, etc...
     */
    'trusted_roots' => [
        'localhost', // May be useful while testing on a local machine
        'your-site',
    ],

    /*
     * LINKED PLATFORMS LOGIN URLS
     * A list of platform (SPOD, TET) that may use this OpenID Server for
     * authentication. This is used to force platforms to start an OpenID
     * request to this OpenID server.
     */
    'login_urls' => [
        'SPOD' => 'http://your-site/openid-connect/login',
        'TET' => 'http://tet.your-site:8080/user/oilogin?id=http%3A%2F%2Fyour-site%2Fopenid%2F',
    ],

    /*
     * POLICIES
     * A list of Policies that the user MUST accept before registering.
     */
    'policies' => [
        /*
        [
            'url' => '',
            'title' => '',
        ],
        */
    ],
];