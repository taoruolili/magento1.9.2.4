<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2008-2013 Amasty (http://www.amasty.com)
 */

$this->startSetup();

$this->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('amfile/file')}` (
  `file_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `product_id` mediumint(9) NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `rating` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `{$this->getTable('amfile/store')}` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `file_id` mediumint(9) NOT NULL,
  `store_id` mediumint(9) NOT NULL,
  `label` varchar(255) DEFAULT '',
  `excluded` enum('1','0') NOT NULL DEFAULT '1',
  `position` char(255) DEFAULT NULL,
  `use_default_label` enum('on','off') NOT NULL DEFAULT 'off',
  `use_default_excluded` enum('on','off') NOT NULL DEFAULT 'off',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `{$this->getTable('amfile/icon')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `image` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `{$this->getTable('amfile/stat')}` (
  `product_id` mediumint(9) NOT NULL,
  `store` int(3) NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `rating` int(10) NOT NULL,
  `date` date NOT NULL,
  `file_id` mediumint(9) NOT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;



INSERT INTO `{$this->getTable('amfile/icon')}` (`id`, `type`, `image`, `active`) VALUES

(1,'flash', 'fla-24_32.png', '1'),
(2,'ini', 'ini-24_32.png', '1'),
(3,'jpg', 'jpeg-24_32.png', '1'),
(4,'mp3', 'mp3-24_32.png', '1'),
(5,'readme', 'readme-24_32.png', '1'),
(6,'txt', 'text-24_32.png', '1'),
(7,'zip', 'zip-24_32.png', '1'),
(8,'avi', 'avi-24_32.png', '1');

");

$this->endSetup();
