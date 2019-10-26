<?php

/**
 * Created by PhpStorm.
 * User: jp.dou
 * Date: 2019/7/1
 * Time: 21:50
 */
class Jeasy_Sales_Block_Adminhtml_Sales_Order_Grid_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    private $countries;


    public function render(Varien_Object $row)
    {
        /** @var Mage_Sales_Model_Order $row */

        /** @var Mage_Adminhtml_Block_Widget_Grid_Column $col */
        $col = $this->getColumn();
        $index = $col->getData('index');

        $result = '';
        switch ($index) {
            case 'paypal_account':
                $info = $row->getPayment()->getData('additional_information');
                $result = isset($info['paypal_payer_email']) ? $info['paypal_payer_email'] : '';
                break;
            case 'payment_method':
                $result = $row->getPayment()->getMethod();
                break;
            case 'track_number':
                /** @var Mage_Sales_Model_Resource_Order_Shipment_Track_Collection $tracks */
                $tracks = $row->getTracksCollection();
                $result = $tracks->getFirstItem()->getData('track_number');
                break;
            case 'shipping_country_id':
                $countryId = $row->getShippingAddress()->getCountryId();
                $result = $this->getCountryName($countryId);
                break;
            case 'shipping_telephone':
                $result = $row->getShippingAddress()->getTelephone();
                break;
            case 'coupon_code':
                $result = $row->getCouponCode();
                break;
        }



        return $result;
    }

    private function getCountryName($countryId)
    {
        if ($this->countries === null) {
            $_countries = Mage::getResourceModel('directory/country_collection')
                ->loadData()
                ->toOptionArray(false);


            $this->countries = [];
            foreach ($_countries as $item) {
                $this->countries[$item['value']] = $item['label'];
            }
            $this->countries['DE'] = '德国';
            $this->countries['GB'] = '英国';
            $this->countries['FR'] = '法国';
            $this->countries['IT'] = '意大利';
            $this->countries['ES'] = '西班牙';
        }
        return isset($this->countries[$countryId]) ? $this->countries[$countryId] : $countryId;
    }
}