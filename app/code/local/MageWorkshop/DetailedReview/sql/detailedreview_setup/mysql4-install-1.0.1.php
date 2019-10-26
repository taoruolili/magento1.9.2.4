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

$installer->addAttribute('catalog_product', 'popularity_by_sells', array(
    'group' => 'Popularity',
    'type' => 'int',
    'backend' => '',
    'frontend_class' => 'validate-digits',
    'label' => 'Bestselling',
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => false,
    'required' => false,
    'apply_to' => '',
    'is_configurable' => false,
    'note' => '',
    'used_in_product_listing' => true,
    'used_for_sort_by' => true
));

$installer->addAttribute('catalog_product', 'popularity_by_reviews', array(
    'group' => 'Popularity',
    'type' => 'int',
    'backend' => '',
    'frontend_class' => 'validate-digits',
    'label' => 'Most Reviewed',
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => false,
    'required' => false,
    'apply_to' => '',
    'is_configurable' => false,
    'note' => '',
    'used_in_product_listing' => true,
    'used_for_sort_by' => true
));

$installer->addAttribute('catalog_product', 'popularity_by_rating', array(
    'group' => 'Popularity',
    'type' => 'int',
    'backend' => '',
    'frontend_class' => 'validate-digits',
    'label' => 'Highly Rated',
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => false,
    'required' => false,
    'apply_to' => '',
    'is_configurable' => false,
    'note' => '',
    'used_in_product_listing' => true,
    'used_for_sort_by' => true
));

Mage::getModel('detailedreview/sort')->refreshAllIndices();


$installer->run("
    CREATE TABLE IF NOT EXISTS `review_helpful` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `review_id` bigint(20) NOT NULL,
      `customer_id` bigint(20) NOT NULL,
      `is_helpful` tinyint(1) NOT NULL,
      KEY `id` (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
");

try{
    $installer->run("
        ALTER TABLE `{$installer->getTable('review_detail')}`
            ADD `good_detail` TEXT NULL ,
            ADD `no_good_detail` TEXT NULL ,
            ADD `response` TEXT NULL ,
            ADD `image` VARCHAR( 255 ) NULL ,
            ADD `video` VARCHAR( 255 ) NULL;
    ");
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

$installer->endSetup();
