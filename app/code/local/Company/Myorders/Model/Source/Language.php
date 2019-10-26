<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category	Myorders
 * @package 	Myorders
 * @copyright	Copyright (c) 2015 Paytos.
 */
class Company_Myorders_Model_Source_Language
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'EN', 'label' => Mage::helper('Myorders')->__('English')),
            array('value' => 'FR', 'label' => Mage::helper('Myorders')->__('French')),
            array('value' => 'DE', 'label' => Mage::helper('Myorders')->__('German')),
            array('value' => 'IT', 'label' => Mage::helper('Myorders')->__('Italian')),
            array('value' => 'ES', 'label' => Mage::helper('Myorders')->__('Spain')),
            array('value' => 'NL', 'label' => Mage::helper('Myorders')->__('Dutch')),
        );
    }
}



