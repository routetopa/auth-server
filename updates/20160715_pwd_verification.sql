ALTER TABLE `users` ADD `is_verified` BOOLEAN NOT NULL DEFAULT FALSE AFTER `is_admin`;


CREATE TABLE `email_verify` (
  `email` varchar(200) NOT NULL,
  `token` varchar(32) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `email_verify`
  ADD PRIMARY KEY (`email`),
  ADD UNIQUE KEY `token` (`token`);