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

if ($installer->getTable('detailedreview/review_helpful') != 'review_helpful') {
    $sql = "SELECT * FROM `review_helpful`";
    $data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);

    $this->run("
        DROP TABLE IF EXISTS `review_helpful`;
    ");

    $this->run("
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('detailedreview/review_helpful')}` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
          `review_id` bigint(20) NOT NULL,
          `customer_id` bigint(20) NOT NULL,
          `is_helpful` tinyint(1) NOT NULL,
          KEY `id` (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
    ");

    foreach ($data as $item) {
        $model = Mage::getModel('detailedreview/review_helpful');
        unset($item['id']);
        $model->setData($item);
        $model->save();
    }
}

$this->endSetup();
