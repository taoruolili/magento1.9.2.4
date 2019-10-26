<?php

/**
 * Created by PhpStorm.
 * User: jp.dou
 * Date: 2019/5/24
 * Time: 22:58
 */
class Jeasy_Sales_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_BLACKLIST_TELEPHONE = 'jeasy_sales/blacklist/telephone';
    const XML_PATH_BLACKLIST_EMAIL = 'jeasy_sales/blacklist/email';
    const XML_PATH_BLACKLIST_FIRSTNAME = 'jeasy_sales/blacklist/firstname';
    const XML_PATH_BLACKLIST_LASTNAME = 'jeasy_sales/blacklist/lastname';
    const XML_PATH_BLACKLIST_CITY = 'jeasy_sales/blacklist/city';
    const XML_PATH_BLACKLIST_IP = 'jeasy_sales/blacklist/ip';

    public function getEmailBlacklist()
    {
        return explode(',', (string)Mage::getStoreConfig(self::XML_PATH_BLACKLIST_EMAIL));
    }

    public function getTelephoneBlacklist()
    {
        return explode(',', (string)Mage::getStoreConfig(self::XML_PATH_BLACKLIST_TELEPHONE));
    }

    public function getFirstnameBlacklist()
    {
        return explode(',', (string)Mage::getStoreConfig(self::XML_PATH_BLACKLIST_FIRSTNAME));
    }

    public function getLastnameBlacklist()
    {
        return explode(',', (string)Mage::getStoreConfig(self::XML_PATH_BLACKLIST_LASTNAME));
    }

    public function getCityBlacklist()
    {
        return explode(',', (string)Mage::getStoreConfig(self::XML_PATH_BLACKLIST_CITY));
    }

    public function getIpBlacklist()
    {
        return explode(',', (string)Mage::getStoreConfig(self::XML_PATH_BLACKLIST_IP));
    }
}