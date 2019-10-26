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

class MageWorkshop_DetailedReview_Model_Observer
{

    public function adminReviewSave($observer)
    {
        $files = Mage::helper('detailedreview')->uploadImages();
        $session = Mage::getSingleton('core/session');
        if(!empty($files['images'])){
            Mage::app()->getRequest()->setPost('image', implode(",",$files['images']));
        } else {
            Mage::app()->getRequest()->setPost('image', null);
        }
        if (!empty($files['errors'])) {
            foreach ($files['errors'] as $imageName => $errorMessages) {
                foreach($errorMessages as $message) {
                    $session->addError(Mage::helper('detailedreview')->__('Image \'%s\' has the following problem: ', $imageName) . $message);
                }
            }
        }
    }

    public function configSave($observer)
    {
        $groups = Mage::app()->getRequest()->getParam('groups');
        $enableFlag = Mage::getStoreConfig('detailedreview/settings/enable_flag');
        if (isset($groups['settings']['fields']['enable']['value'])) {
            $enable = $groups['settings']['fields']['enable']['value'];
            if ($enable !== $enableFlag) {
                Mage::getModel('core/config')->saveConfig('detailedreview/settings/enable_flag', $enable);
            }
        }
        if (isset($groups['modules_disable_output']['fields']['Mage_Review']['value'])) {
            $mageReview = $groups['modules_disable_output']['fields']['Mage_Review']['value'];
            if ($mageReview == 1) {
                Mage::getModel('core/config')->saveConfig('detailedreview/settings/enable', 0);
            } else {
                Mage::getModel('core/config')->saveConfig('detailedreview/settings/enable', Mage::getStoreConfig('detailedreview/settings/enable_flag'));
            }
        }
    }

}
?>