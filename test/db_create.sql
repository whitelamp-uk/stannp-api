-- db_create.sql

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `campaigns`;
CREATE TABLE `campaigns` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stannp_id` int(11) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET ascii,
  `send_date` date,
  `recipients_group`  int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE `stannp_id` (`stannp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `recipients`;
CREATE TABLE `recipients` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stannp_id` int(11) unsigned NOT NULL,
  `campaign_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  `account_id` int(11) unsigned NOT NULL,
  `email`  varchar(255) CHARACTER SET ascii,
  `title` varchar(255) CHARACTER SET ascii,
  `firstname` varchar(255) CHARACTER SET ascii,
  `lastname` varchar(255) CHARACTER SET ascii,
  `company` varchar(255) CHARACTER SET ascii,
  `job_title` varchar(255) CHARACTER SET ascii,
  `address1` varchar(255) CHARACTER SET ascii,
  `address2` varchar(255) CHARACTER SET ascii,
  `address3` varchar(255) CHARACTER SET ascii,
  `city` varchar(255) CHARACTER SET ascii,
  `county` varchar(255) CHARACTER SET ascii,
  `country` varchar(255) CHARACTER SET ascii,
  `postcode` varchar(255) CHARACTER SET ascii,
  PRIMARY KEY (`id`),
  UNIQUE `stannp_id_group_id` (`stannp_id`, `group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

