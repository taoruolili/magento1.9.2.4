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
    const XML_PATH_BLACKLIST_NAME = 'jeasy_sales/blacklist/name';
    const XML_PATH_BLACKLIST_CITY = 'jeasy_sales/blacklist/city';
    const XML_PATH_BLACKLIST_IP = 'jeasy_sales/blacklist/ip';

    public function getEmailBlacklist()
    {
        return $this->getConfigAsArray(self::XML_PATH_BLACKLIST_EMAIL);
    }

    public function getTelephoneBlacklist()
    {
        return $this->getConfigAsArray(self::XML_PATH_BLACKLIST_TELEPHONE);
    }

    public function getNameBlacklist()
    {
        return $this->getConfigAsArray(self::XML_PATH_BLACKLIST_NAME);
    }

    public function getCityBlacklist()
    {
        return $this->getConfigAsArray(self::XML_PATH_BLACKLIST_CITY);
    }

    public function getIpBlacklist()
    {
        return $this->getConfigAsArray(self::XML_PATH_BLACKLIST_IP);
    }

    private function getConfigAsArray($path)
    {
        $config = (string)Mage::getStoreConfig($path);
        return empty($config) ? [] : explode(',', $config);
    }
}