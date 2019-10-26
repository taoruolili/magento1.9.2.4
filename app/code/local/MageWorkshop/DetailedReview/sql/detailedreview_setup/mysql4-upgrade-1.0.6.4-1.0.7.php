<?php
/**
 * MageWorkshop
 * Copyright (C) 2012  MageWorkshop <mageworkshophq@gmail.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2012 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('review_proscons')}` (
      `entity_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `status` tinyint(1) DEFAULT NULL,
      `wrote_by` tinyint(1) NOT NULL DEFAULT '0',
      `sort_order` smallint(5) DEFAULT NULL,
      `entity_type` varchar(1) NOT NULL,
      PRIMARY KEY (`entity_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$installer->getTable('review_proscons_store')}` (
      `entity_id` smallint(6) unsigned NOT NULL,
      `entity_type` varchar(1) NOT NULL,
      `store_id` smallint(6) unsigned NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

try{
    $installer->run("
        ALTER TABLE `{$installer->getTable('review_detail')}`
          ADD `pros` varchar(255) NULL,
          ADD `cons` varchar(255) NULL;
    ");
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

try{
    $installer->run("
        ALTER TABLE `{$installer->getTable('review_helpful')}`
          ADD `remote_addr` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
    ");
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

try{
    $installer->run("
        ALTER TABLE `{$installer->getTable('review_helpful')}`
          CHANGE `customer_id` `customer_id` BIGINT( 20 ) NULL DEFAULT NULL;
    ");
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

$this->endSetup();
