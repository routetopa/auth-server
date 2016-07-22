<?php

/*
 * Rename this file as 'config.dev.php' or 'congig.prod.php'
 */

return [
    /*
     * GENERAL CONFIGURATION
     */
    // Full URL of OpenID provider index
    'server_url' => 'http://spod.routetopa.eu/openid',

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
        'localhost',
        'spod.routetopa.eu',
    ]
];

/* ****************************************************************************
   DATABASE SETUP
   ****************************************************************************

   In order to work, the ROUTE-TO-PA OpenID Provider needs the 'users' table.
   Please execute this query to create it:

    CREATE TABLE IF NOT EXISTS `users` (
      `uuid` char(36) NOT NULL,
      `email` varchar(200) NOT NULL,
      `password` varchar(200) NOT NULL,
      `is_admin` tinyint(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`uuid`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

    INSERT INTO `users` VALUES(
        UUID(),
        'YOUR_ADMIN_EMAIL',
        '',
        1
    );

    CREATE TABLE IF NOT EXISTS `password_reset` (
      `email` varchar(200) NOT NULL,
      `token` varchar(32) NOT NULL,
      `timestamp` datetime NOT NULL,
      PRIMARY KEY (`email`),
      UNIQUE KEY `token` (`token`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

    Remember to set a valid and active t-mail address instead of
    'YOUR_ADMIN_EMAIL'. At this point you need to access the OpenID provider
    and reset your password before logging in.

*/