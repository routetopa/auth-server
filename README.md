# ROUTE-TO-PA OpenID Server Endpoint

Provides an OpenID 2.0 authentication server. It is based on [PHP OpenID library by JanRain, Inc](https://github.com/openid/php-openid).

## Requirements
- PHP 5.4 or grater
- MySQL 5.6 or greater

## Installation
Clone the repository on your webserver (in this example, we will deploy the server in the `openid` directory).
```bash
git clone https://github.com/routetopa/auth-server.git openid
```
Create a database (in this example we will use `opendb`) , import the structure and add yourself as administrator (only change value `your@email.com`).
```bash
mysql -uroot -p -e 'create database opendb'
mysql -uroot -p opendb < openid.sql
mysql -uroot -p opendb -e "INSERT INTO users VALUES(UUID(), 'your@email.com', '',  1, 1 )"
```
Create a configuration file and edit it. The `config.sample.php` file is commented so you should not have problems editing it.
```bash
cp config.sample.php config.prod.php
```
You can now acess the URL http://your-site/openid and use the reset password link to set your password.