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

$installer->addAttribute('catalog_category', 'review_fields_available', array(
    'type'                       => 'text',
    'label'                      => 'Available Review Fields',
    'input'                      => 'multiselect',
    'source'                     => 'detailedreview/category_attribute_source_fields',
    'backend'                    => 'detailedreview/category_attribute_backend_fields',
    'sort_order'                 => 70,
    'required'                   => 0,
    'input_renderer'             => 'detailedreview/adminhtml_catalog_category_helper_fields_available',
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'                      => 'Display Settings',
));

$installer->addAttribute('catalog_category', 'use_parent_review_settings', array(
    'type'                       => 'int',
    'label'                      => 'Use Parent Category Settings for Review Fields',
    'input'                      => 'select',
    'source'                     => 'eav/entity_attribute_source_boolean',
    'default'                    => 1,
    'sort_order'                 => 80,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'                      => 'Display Settings',
));

$installer->endSetup();
