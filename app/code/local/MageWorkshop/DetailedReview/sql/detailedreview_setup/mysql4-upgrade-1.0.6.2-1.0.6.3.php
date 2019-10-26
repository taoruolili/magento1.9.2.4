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

Mage::app()->cleanCache();
Mage::getConfig()->reinit();
Mage::app()->reinitStores();

$active = Mage::getStoreConfig('detailedreview/rating_image/active');
$unactive = Mage::getStoreConfig('detailedreview/rating_image/unactive');

if($active == null){
    Mage::getModel('core/config')->saveConfig('detailedreview/rating_image/active', 'default/active-star.png' );
}else{
    Mage::getModel('core/config')->saveConfig('detailedreview/rating_image/active', $active );
}

if($unactive == null){
    Mage::getModel('core/config')->saveConfig('detailedreview/rating_image/unactive', 'default/unactive-star.png' );
}else{
    Mage::getModel('core/config')->saveConfig('detailedreview/rating_image/unactive', $unactive );
}

Mage::getConfig()->reinit();
Mage::app()->reinitStores();

$this->endSetup();
