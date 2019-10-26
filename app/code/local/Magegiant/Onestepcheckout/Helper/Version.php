<?php
/**
 * MageGiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageGiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Onestepcheckout Helper
 *
 * @category    MageGiant
 * @package     Magegiant_Onestepcheckout
 * @author      MageGiant Developer
 */
class Magegiant_Onestepcheckout_Helper_Version extends Mage_Core_Helper_Abstract
{
    /**
     * True if the version of Magento currently being run is Enterprise Edition
     */
    public function isEnterprise()
    {
        $isMageEnterprise = Mage::getConfig()->getModuleConfig('Enterprise_Enterprise')
            && Mage::getConfig()->getModuleConfig('Enterprise_AdminGws')
            && Mage::getConfig()->getModuleConfig('Enterprise_Checkout')
            && Mage::getConfig()->getModuleConfig('Enterprise_Customer');

        return $isMageEnterprise;
    }

    /**
     * True if the base version is at least the verison specified without checking
     *
     * @param string $version
     */
    public function isEnterpriseAtLeast($version)
    {
        if (!$this->isEnterprise()) {
            return false;
        }

        return $this->isRawVerAtLeast($version);
    }

    /**
     * True if the base version is at least the verison specified without converting version numbers to other versions
     * of Magento.
     *
     * @param string       $version
     * @param unknown_type $task
     * @return boolean
     */
    public function isRawVerAtLeast($version)
    {
        // convert Magento Enterprise, Professional, Community to a base version
        $mage_base_version = Mage::getVersion();

        if (version_compare($mage_base_version, $version, '>=')) {
            return true;
        }

        return false;
    }

    /**
     * get design package name
     *
     * @return string
     */
    public function getPackageName()
    {
        return Mage::getSingleton('core/design_package')->getPackageName();
    }

    /**
     * get frontend theme name
     *
     * @return mixed
     */
    public function getFrontendThemeName()
    {
        return Mage::getSingleton('core/design_package')->getTheme('frontend');
    }

}