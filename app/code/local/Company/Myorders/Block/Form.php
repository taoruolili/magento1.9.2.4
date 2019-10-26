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
 * @category    Myorders
 * @package    Myorders
 * @copyright    Copyright (c) 2015 Paytos.
 */
class Company_Myorders_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code => $name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }

    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] = $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0 => $this->__('Year')) + $years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Retrive has verification configuration
     *
     * @return boolean
     */
    public function hasVerification()
    {
        if ($this->getMethod()) {
            $configData = $this->getMethod()->getConfigData('useccv');
            if (is_null($configData)) {
                return true;
            }
            return (bool)$configData;
        }
        return true;
    }

    public function hasSsCardType()
    {
        $availableTypes = explode(',', $this->getMethod()->getConfigData('cctypes'));
        $ssPresenations = array_intersect(array('SS', 'SM', 'SO'), $availableTypes);
        if ($availableTypes && count($ssPresenations) > 0) {
            return true;
        }
        return false;
    }

    /*
    * Whether switch/solo card type available
    */

    public function getSsStartYears()
    {
        $years = array();
        $first = date("Y");

        for ($index = 5; $index >= 0; $index--) {
            $year = $first - $index;
            $years[$year] = $year;
        }
        $years = array(0 => $this->__('Year')) + $years;
        return $years;
    }

    /*
    * solo/switch card start year
    * @return array
    */

    function getMyordersConfig()
    {
        $securePayConfig = null;

        session_start();
       // if (!isset($_SESSION['securePayConfig'])) {
           // $lang = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
           // $result = $this->http_response('http://merchant.paytos.com/CubePaymentGateway/gateway/action.PayConfigService.do?Language='. $lang);
        $_language_code = Mage::app()->getStore()->getCode();
		if(empty($_language_code)){
			$_language_code = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
		}
		if(empty($_language_code)){
			
			$_language_code='en';
		}
		include_once("./app/code/local/Company/Myorders/lang/".$_language_code.".php");
		if(empty($arr)){
			include_once("./app/code/local/Company/Myorders/lang/en.php");
		}
		    $securePayConfig =$arr;
			//$securePayConfig = json_decode($result, TRUE);
            //"http://192.168.1.33:8081/FrontGateway/Gateway/submit.do";
			//$securePayConfig['gatewayUrl']="http://payment.sslrouter.com/FrontGateway/Gateway/submit.do";
			$securePayConfig['gatewayUrl']="http://merchant.paytos.com/CubePaymentGateway/gateway/action.NewSubmitAction.do";
			
            $_SESSION['securePayConfig'] = $securePayConfig;
			$_SESSION['securePayConfig']['lang'] = $lang;
     /*   } else {
            $securePayConfig = $_SESSION['securePayConfig'];
        }*/
        $cart = Mage::getSingleton('checkout/session');
        $cartId = $cart->getQuote()->getId();
        $_SESSION['cartId'] = $cartId;
        $_SESSION['lang'] = $lang;
      //  $this->postMonitor($securePayConfig["monitorUrl"],$cartId);
	     //var_dump($securePayConfig);
	   //  exit;
        return $securePayConfig;
		
    }

	 private function postMonitor($monitorUrl,$cartId){
		$standard = Mage::getModel('Myorders/payment');
		$merchantNo = $standard->getConfigData('partner_id');
		$IVersion = $standard ->IVersion;
		$framework = $standard -> Framework;
		$CMSVersion = Mage::getVersion();
		$PHPVersion = phpversion();
		$data = array(
			'IVersion' => $IVersion,
			'CartID' => $cartId,
			'AcctNo' =>$merchantNo,
			'CMSVersion'=>$CMSVersion,
			'PHPVersion'=>$PHPVersion,
			'Framework'=>$framework
		);
		$this -> http_response($monitorUrl,http_build_query($data, '', '&'));
	}

    function http_response($url,$data=null, $status = null, $wait = 3)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER["HTTP_REFERER"]);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        return curl_exec($ch);
    }

    protected function _construct()
    {
        $this->setTemplate('Myorders/form.phtml');
        parent::_construct();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
    		$resource = Mage::getSingleton('core/resource');
    		$writeConnection = $resource->getConnection('core_write');
    		$writeConnection->query(
    			"update core_config_data set value = 'Myorders Payment' where path = 'payment/Myorders_payment/title' and value <> 'Myorders Payment'");
    		
        Mage::dispatchEvent('payment_form_block_to_html_before', array(
            'block' => $this
        ));
        return parent::_toHtml();
    }
}