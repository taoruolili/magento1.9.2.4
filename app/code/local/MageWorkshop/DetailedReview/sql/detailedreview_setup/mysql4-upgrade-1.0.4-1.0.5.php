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

$this->startSetup();

$this->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('detailedreview/author_ips')}` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `expiration_time` timestamp NOT NULL,
  `remote_addr` VARCHAR( 255 ),
  `customer_id` bigint(20),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
");

try{
    $installer->run("
        ALTER TABLE `{$this->getTable('review/review_detail')}`
	      ADD `remote_addr` VARCHAR( 255 ) NULL;
    ");
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

try{
    $installer->run("
        ALTER TABLE `{$this->getTable('detailedreview/author_ips')}`
            ADD INDEX (remote_addr),
            ADD INDEX (customer_id);
    ");
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

$this->endSetup();
