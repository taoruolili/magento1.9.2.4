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

/**
 * Class MageWorkshop_DetailedReview_Block_Adminhtml_Uninstall_Button
 *
 * @method setElement(Varien_Data_Form_Element_Abstract $element)
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Uninstall_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @inherit
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        /** @var MageWorkshop_DetailedReview_Helper_Data $helper */
        $helper = $this->helper('detailedreview');

        /** @var Mage_Adminhtml_Block_Widget_Button $buttonWidget */
        $buttonWidget = $this->getLayout()->createBlock('adminhtml/widget_button');
        $buttonWidget->setType('button')
            ->setClass('scalable')
            ->setLabel('Uninstall');

        if ($helper->checkPackageFile()) {
            $url = $this->getUrl('detailedreviewadmin/adminhtml_main/uninstall');
            $text = $helper->__('This will completely uninstall Detailed Review extension and delete all related information. Reviews will get back to original (standard) state. Are you sure?');
            $buttonWidget->setOnClick("if(confirm('$text')){setLocation('$url');}");
        } else {
            $text = $helper->__('Detailed Review extension was not correctly installed. This functionality will not work if you installed extension not from Magento downloader (for example by just copying files to Magento directory).');
            $buttonWidget->setOnClick("alert('$text')");
        }
        return $buttonWidget->toHtml();
    }
}