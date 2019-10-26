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

try{
    $installer->run("
        ALTER TABLE {$installer->getTable('review_detail')} ADD `recommend_to` VARCHAR( 127 ) NULL DEFAULT NULL AFTER `cons`;
    ");
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

$installer->updateAttribute('catalog_category', 'review_fields_available', array(
    'default_value'              => NULL,
    'frontend_input_renderer'    => 'detailedreview/adminhtml_catalog_category_helper_fields_available',
));

try {
    $sql = "SELECT * FROM {$installer->getTable('eav_attribute')} WHERE attribute_code = 'use_parent_review_settings'";
    $data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);
    if (!empty($data)) {
        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('use_parent_review_settings');
        $categories->load();

        foreach ($categories as $category) {
            if ($category->getData('use_parent_review_settings') == 1) {
                $category->setData('review_fields_available', NULL);
                $category->getResource()->saveAttribute($category, 'review_fields_available');
            }
        }
    }
} catch(Exception $e) {
    Mage::log($e->getMessage());
}

if (isset($data) && !empty($data)) {
    $installer->removeAttribute('catalog_category', 'use_parent_review_settings');
} else {
    try {
        $installer->removeAttribute('catalog_category', 'use_parent_review_settings');
    } catch(Exception $e) {
        Mage::log($e->getMessage());
    }
}

$this->endSetup();