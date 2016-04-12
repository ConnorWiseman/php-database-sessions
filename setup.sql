CREATE DATABASE test;

USE test;

CREATE TABLE users (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255),
    `username` VARCHAR(32) NOT NULL,
    `password` CHAR(60) NOT NULL,
    PRIMARY KEY(`id`),
    CONSTRAINT `username` UNIQUE(`username`)
) ENGINE=InnoDB;

CREATE TABLE sessions (
    `id` CHAR(86) NOT NULL,
    `auth_key` CHAR(32) NOT NULL,
    `user_id` INT(11) UNSIGNED,
    `ip_address` INT(11) UNSIGNED NOT NULL,
    `accessed` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY(`id`),
    FOREIGN KEY(`user_id`) REFERENCES users(`id`)
) ENGINE=InnoDB;