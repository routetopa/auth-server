CREATE TABLE IF NOT EXISTS `users` (
  `uuid` char(36) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `is_verified` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `password_reset` (
  `email` varchar(200) NOT NULL,
  `token` varchar(32) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`email`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `email_verify` (
  `email` varchar(200) NOT NULL,
  `token` varchar(32) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`email`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;