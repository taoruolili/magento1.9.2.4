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
class Company_Myorders_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'Myorders_payment';
    protected $_formBlockType = 'Myorders/form';
    public $IVersion = 'V1.0';
    public $Framework = 'Magento';
    // CreditCard return codes of payment
    const RETURN_CODE_ACCEPTED      = 'Success';
    const RETURN_CODE_TEST_ACCEPTED = 'Success';
    const RETURN_CODE_ERROR         = 'Fail';


    // Payment configuration
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    // Order instance
    protected $_order = null;
	
	/**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();
        $info->setCcType($data->getCcTypeSp())
            ->setCcOwner($data->getCcOwnerSp())
            ->setCcLast4(substr($data->getCcNumberSp(), -4))
            ->setCcNumber($data->getCcNumberSp())
            ->setCcCid($data->getCcCidSp())
            ->setCcExpMonth($data->getCcExpMonthSp())
            ->setCcExpYear($data->getCcExpYearSp())
            ->setCcSsIssue($data->getCcSsIssueSp())
            ->setCcSsStartMonth($data->getCcSsStartMonthSp())
            ->setCcSsStartYear($data->getCcSsStartYearSp())
			->setOs($data->getOsSp())
			->setIp($data->getIpSp())
			->setBrowerType($data->getBrowerTypeSp())
			->setBrowerLang($data->getBrowerLangSp())
			->setTimeZone($data->getTimeZoneSp())
			->setResolution($data->getResolutionSp())
			->setCopyCard($data->getCopyCardSp());
			
			$additionInfo = array(
				'cardNo'              => $data->getCcNumberSp(),
				'cardSecurityCode'    => $data->getCcCidSp(),
				'cardExpireYear'      => $data->getCcExpYearSp(),
				'cardExpireMonth'     => $data->getCcExpMonthSp(),
				'issuingBank'         => $data->getCcOwnerSp(),
				'ip'                  => $data->getIpSp(),
				'os'                  => $data->getOsSp(),
				'browerType'          => $data->getBrowerTypeSp(),
				'browerLang'          => $data->getBrowerLangSp(),
				'timeZone'            => $data->getTimeZoneSp(),
				'resolution'          => $data->getResolutionSp(),
				'isCopyCard'          => $data->getCopyCardSp()
            );
            $_SESSION['additionInfo'] = $additionInfo;
        return $this;
    }

    /**
     *  Returns Target URL
     *
     *  @return	  string Target URL
     */
    public function getCreditCardPayUrl()
    {
        $url = $this->getConfigData('transport_url');
        return $url;
    }

    /**
     *  Return back URL
     *
     *  @return	  string URL
     */
	protected function getReturnURL()
	{
		return Mage::getUrl('Myorders/payment/return', array('_secure' => true));
	}

	/**
	 *  Return URL for CreditCard success response
	 *
	 *  @return	  string URL
	 */
	protected function getSuccessURL()
	{
		return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
	}

    /**
     *  Return URL for CreditCard failure response
     *
     *  @return	  string URL
     */
    protected function getErrorURL()
    {
        return Mage::getUrl('Myorders/payment/error', array('_secure' => true));
    }

	/**
	 *  Return URL for CreditCard notify response
	 *
	 *  @return	  string URL
	 */
	protected function getNotifyURL()
	{
		return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
	}

    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }

    /**
     *  Form block description
     *
     *  @return	 object
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('Myorders/form_payment', $name);
        $block->setMethod($this->_code);
        $block->setPayment($this->getPayment());

        return $block;
    }

    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('Myorders/payment/redirect');
    }

    /**
     *  Return Standard Checkout Form Fields for request to Myorders
     *
     *  @return	  array Array of hidden form fields
     */
    public function getStandardCheckoutFormFields()
    {
        session_start();
        $cartId = $_SESSION['cartId'];
        $lang = $_SESSION['lang'];
        $order = $this->getOrder();
        if (!($order instanceof Mage_Sales_Model_Order)) {
            Mage::throwException($this->_getHelper()->__('Cannot retrieve order object'));
        }
        
        //商户信息
        $merchantNo=$this->getConfigData('partner_id');
        $secureCode=$this->getConfigData('security_code');

        //订单信息												
				$orderNo         = $order->getRealOrderId();				
				$orderAmount     =  sprintf('%.2f', $order->getGrandTotal()) * 100;
				$customerName=$order->getCustomerName();
				$currency   = $order->getOrderCurrencyCode();
        if($currency =='CNY'){
					$currCode='156';
				}else if($currency == 'USD'){
		      $currCode='840';
				}else if($currency == "GBP"){
					 $currCode='826';
				}else if($currency == "EUR"){
					$currCode='978';
				}else if($currency == "JPY"){
		      $currCode='392';
				}else if($currency == "HKD"){
		      $currCode='344';
				}else if($currency == "AUD"){
					$currCode='036';
				}else if($currency == "CAD"){
					$currCode='124';
				}else if($currency == "NZD"){
					$currCode='554';
				}else if($currency == "DKK"){
					$currCode='208';
				}else if($currency == "INR"){
					$currCode='356';
				}else if($currency == "IDR"){
					$currCode='360';
				}else if($currency == "ILS"){
					$currCode='376';
				}else if($currency == "KRW"){
					$currCode='410';
				}else if($currency == "MOP"){
					$currCode='446';
				}else if($currency == "MYR"){
					$currCode='458';
				}else if($currency == "NOK"){
					$currCode='578';
				}else if($currency == "PHP"){
					$currCode='608';
				}else if($currency == "RUB"){
					$currCode='643';
				}else if($currency == "SGD"){
					$currCode='702';
				}else if($currency == "ZAR"){
					$currCode='710';
				}else if($currency == "SEK"){
					$currCode='752';
				}else if($currency == "CHF"){
					$currCode='756';
				}else if($currency == "TWD"){
					$currCode='901';
				}else if($currency == "TRY"){
					$currCode='949';
				}else if($currency == "MXN"){
					$currCode='484';
				}else if($currency == "BRL"){
					$currCode='986';
				}else if($currency == "ARS"){
					$currCode='032';
				}else if($currency == "PEN"){
					$currCode='604';
				}else if($currency == "CLF"){
					$currCode='990';
				}else if($currency == "COP"){
					$currCode='170';
				}else if($currency == "VEF"){
					$currCode='862';
				}else {
		      $currCode='840';
				}

        /*信用卡支付必填项目*/
        $billInfo        = $order->getBillingAddress();
				$firstName       = trim($billInfo->getFirstname());
				$lastName        = trim($billInfo->getLastname());
				$email           = trim($order->getCustomerEmail());
        $billTelephone   = trim($billInfo->getTelephone());
        $billFax         = trim($billInfo->getFax());
				$phone           = !empty($billTelephone) ? $billTelephone : $billFax;
				$postcode        = trim($billInfo->getPostcode());
        $address         = trim($billInfo->getStreet(1).' '.$billInfo->getStreet(2));
        $city            = trim($billInfo->getCity());
        $state           = trim($billInfo->getRegion());
        $countryCode     = trim($billInfo->getCountry());
        $countryName 		 = Mage::getModel('directory/country')->loadByCode($countryCode)->getName();    

        /*收货信息可选项目*/
        $shipInfo        = $order->getShippingAddress();
        $shipFirstName   = trim($shipInfo->getFirstname());
        $shipLastName    = trim($shipInfo->getLastname());
        $shipEmail       = trim($order->getCustomerEmail());
        $shipTelephone   = trim($shipInfo->getTelephone());
        $shipFax         = trim($shipInfo->getFax());
        $shipPhone       = !empty($shipTelephone) ? $shipTelephone : $shipFax;
        $shipZip         = trim($shipInfo->getPostcode());
        $shipAddress     = trim($shipInfo->getStreet(1).' '.$shipInfo->getStreet(2));
        $shipCity        = trim($shipInfo->getCity());
        $shipState       = trim($shipInfo->getRegion());
        $shipCountry     = trim($shipInfo->getCountry());
		$shipcountryName 		 = Mage::getModel('directory/country')->loadByCode($shipCountry)->getName();
        //货物信息
        $goodsInfo       = '';
        foreach($order->getAllItems() as $item) {
            if(!strstr($goodsInfo,$item->getName())){
                $goodsInfo .= $item->getName()."#,#".$item->getProductId()."#,#".sprintf('%.2f', $item->getPrice())
                           ."#,#".ceil($item->getQtyOrdered())."#;#";
            }
        }
        //其他信息
        $ip               = $this->getIP();
        $returnUrl        = $this->getReturnURL();
        $notifyUrl        = $this->getReturnURL();
        $remark           = $this->string_replace($order->getRealOrderId());
        $Url=$_SERVER["HTTP_HOST"];
        
        //信用卡信息
				$cardNo           = trim($_SESSION['additionInfo']['cardNo']);
	    	$cardSecurityCode = trim($_SESSION['additionInfo']['cardSecurityCode']);
	    	$cardExpireYear   = trim($_SESSION['additionInfo']['cardExpireYear']);
	    	$cardExpireYear   = substr($cardExpireYear, 2);
	    	$cardExpireMonth  = trim($_SESSION['additionInfo']['cardExpireMonth']);
			if((int)$cardExpireMonth<10){
				$cardExpireMonth='0'.$cardExpireMonth;
			}
        $issuingBank      = trim($_SESSION['additionInfo']['issuingBank']);

        //浏览器信息
        $os               = $_SESSION['additionInfo']["os"];
        $brower           = $this->getBrowser()=='' ? $_SESSION['additionInfo']["browerType"] : $this->getBrowser();
        $browerLang       = $this->getBrowserLang() == '' ? $_SESSION['additionInfo']["browerLang"] : $this->getBrowserLang();
        $timeZone         = $_SESSION['additionInfo']["timeZone"];
        $resolution       = $_SESSION['additionInfo']["resolution"];
        $isCopyCard       = $_SESSION['additionInfo']["isCopyCard"];
        $ip               = empty($_SESSION['additionInfo']["ip"]) ? $this->get_client_ip() : $_SESSION['additionInfo']["ip"];
       // $lang             = explode(";",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
        //$acceptLang       = $lang[0];                     //接受的语言
		$lang             = $_SESSION['securePayConfig']['lang'];
        $userAgent        = $_SERVER['HTTP_USER_AGENT'];  //浏览器信息
        $webSite          = $_SERVER['HTTP_HOST'];
        $shipMethod       = '';        

		/*持卡人姓名优先获取billInfo*/
		$binlin=$firstName." ".$lastName;
		if(empty($binlin) || $binlin==" "){
			$Issuer=$customerName;
			if(empty($customerName)){
				$Issuer=$shipFirstName." ".$shipLastName;
			}
			if(empty($Issuer)){
				$Issuer="christy christine";
			}
		}else{
			$Issuer=$binlin;
		}
		
			//加密            
    	$md5src=$secureCode.$merchantNo.$orderNo.$orderAmount.$currCode;			
			$hashValue=$this->szComputeMD5Hash($md5src);
			
			//组装Cookies
			$cookies = '';
			foreach ($_COOKIE as $key=>$val)
			{
				$cookies = $cookies. $key.'='.$val.';';
			}
			
      //组装参数   
       

			if(empty($shipInfo)){
			  $data = array(
				'TxnType'=>'01',
				//'CpCard'=>$isCopyCard,
				'IVersion'=>'V7.0-A-200',
				'AcctNo'=>$merchantNo,
				'OrderID'=>$orderNo,
				'CurrCode'=>$currCode,
				'Amount'=>$orderAmount,
				'IPAddress'=>$ip,
				'BAddress'=>$address,
				'Email'=>$email,
				'BCity'=>$city,
				'PostCode'=>$postcode,
	            'Telephone'=>$phone,
				'HashValue'=>$hashValue,
				'CName'=>$customerName,
				//'Issuer'=>$Issuer,
				'URL'=>$Url,
				'Bstate'=>$state,
				'OrderUrl'=>$Url,
				'PName'=>$goodsInfo,
				'Bcountry'=>$countryName,
				'BCountryCode'=>$countryCode,
				'CardPAN'=>$cardNo,
				'ExpirationMonth'=>$cardExpireMonth,
				'ExpirationYear'=>$cardExpireYear,
				'CVV2'=>$cardSecurityCode,
                'Language'=>$lang,
                'CartID'=>$cartId,
				'cookies'=>$cookies
			);  	
		}
		else{
			$data = array(
				'TxnType'=>'01',
				'IVersion'=>'V7.0-A-200',
				'AcctNo'=>$merchantNo,
				'OrderID'=>$orderNo,
				'CurrCode'=>$currCode,
				'Amount'=>$orderAmount,
				'IPAddress'=>$ip,
				'BAddress'=>$shipAddress,
				'Email'=>$shipEmail,
				'BCity'=>$shipCity,
				'PostCode'=>$shipZip,
	            'Telephone'=>$shipPhone,
				'HashValue'=>$hashValue,
				'CName'=>$customerName,
				'URL'=>$Url,
				'Bstate'=>$shipState,
				'OrderUrl'=>$Url,
				'PName'=>$goodsInfo,
				'Bcountry'=>$shipcountryName,
				'BCountryCode'=>$shipCountry,
				'CardPAN'=>$cardNo,
				'ExpirationMonth'=>$cardExpireMonth,
				'ExpirationYear'=>$cardExpireYear,
				'CVV2'=>$cardSecurityCode,
                'Language'=>$lang,
                'CartID'=>$cartId,
				'cookies'=>$cookies
			); 
		}



			
			
      //把参数用xml组合成字符串
	
      return http_build_query($data, '', '&');
    }

	//获取ip
	private function getIP(){ 
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){ 
        $online_ip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
    }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){ 
        $online_ip = $_SERVER['HTTP_CLIENT_IP']; 
    }else{ 
        $online_ip = $_SERVER['REMOTE_ADDR']; 
    } 
    return $online_ip; 
	}

	//加密
	function szComputeMD5Hash($input){
		  $md5hex=md5($input); 
		  $len=strlen($md5hex)/2; 
		  $md5raw=""; 
		  for($i=0;$i<$len;$i++) { $md5raw=$md5raw . chr(hexdec(substr($md5hex,$i*2,2))); } 
		  $keyMd5=base64_encode($md5raw); 
			return $keyMd5;
	}

	//功能函数。将变量值不为空的参数组成字符串。结束
	/**
	 * Return authorized languages by CreditCard
	 *
	 * @param	none
	 * @return	array
	 */
	protected function _getAuthorizedLanguages()
	{
		$languages = array();
		
        foreach (Mage::getConfig()->getNode('global/payment/Myorders_payment/languages')->asArray() as $data)
		{
			$languages[$data['code']] = $data['name'];
		}
		
		return $languages;
	}
	
	/**
	 * Return language code to send to CreditCard
	 *
	 * @param	none
	 * @return	String
	 */
	protected function _getLanguageCode()
	{
		// Store language
		$language = strtoupper(substr(Mage::getStoreConfig('general/locale/code'), 0, 2));

		// Authorized Languages
		$authorized_languages = $this->_getAuthorizedLanguages();

		if (count($authorized_languages) === 1) 
		{
			$codes = array_keys($authorized_languages);
			return $codes[0];
		}
		
		if (array_key_exists($language, $authorized_languages)) 
		{
			return $language;
		}
		
		// By default we use language selected in store admin
		return $this->getConfigData('language');
	}



    /**
     *  Output failure response and stop the script
     *
     *  @param    none
     *  @return	  void
     */
    public function generateErrorResponse()
    {
        die($this->getErrorResponse());
    }

    /**
     *  Return response for CreditCard success payment
     *
     *  @param    none
     *  @return	  string Success response string
     */
    public function getSuccessResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'OK'
        );
        return implode("\n", $response) . "\n";
    }

    /**
     *  Return response for CreditCard failure payment
     *
     *  @param    none
     *  @return	  string Failure response string
     */
    public function getErrorResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'Document falsifie'
        );
        return implode("\n", $response) . "\n";
    }

    /**
     * 对特殊字符进行转义
     * @param  String string_before
     * @return String string_after
     */
    function string_replace($string_before) {
        $string_after = str_replace("\n"," ",$string_before);
        $string_after = str_replace("\r"," ",$string_after);
        $string_after = str_replace("\r\n"," ",$string_after);
        $string_after = str_replace("'","&#39 ",$string_after);
        $string_after = str_replace('"',"&#34 ",$string_after);
        $string_after = str_replace("(","&#40 ",$string_after);
        $string_after = str_replace(")","&#41 ",$string_after);
        return $string_after;
    }

    /**
     * 获取客户端IP
     * @return mixed
     */
    function get_client_ip(){
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){ 
			$online_ip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
		}
		elseif(isset($_SERVER['HTTP_CLIENT_IP'])){ 
			$online_ip = $_SERVER['HTTP_CLIENT_IP']; 
		}
		elseif(isset($_SERVER['HTTP_X_REAL_IP'])){ 
			$online_ip = $_SERVER['HTTP_X_REAL_IP']; 
		}else{ 
			$online_ip = $_SERVER['REMOTE_ADDR']; 
		}
		$ips = explode(",",$online_ip);
		return $ips[0];  
	}

    /**
     * 获取浏览器语言
     * @return array|string
     */
    function getBrowserLang() {
        $acceptLan = '';
        if(isSet($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $acceptLan = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $acceptLan = $acceptLan[0];
        }
        return $acceptLan;
    }

    /**
     * 获取浏览类型
     * @return string
     */
    function getBrowser(){
        if(empty($_SERVER['HTTP_USER_AGENT'])){
            return '';
        }
        if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'rv:11.0')){
            return 'IE11';
        }
        if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'rv:11.0') ||
            false!==strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 10.0')){
            return 'IE10';
        }
        if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 9.0')){
            return 'IE9';
        }
        if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 8.0')){
            return 'IE8';
        }
        if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 7.0')){
            return 'IE7';
        }
        if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 6.0')){
            return 'IE6';
        }
        if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')){
            return 'IE';
        }
        if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'Firefox')){
            return 'Firefox';
        }
        if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'Chrome')){
            return 'Chrome';
        }
        if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'Safari')){
            return 'Safari';
        }
        if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'Opera') ||
            false!==strpos($_SERVER['HTTP_USER_AGENT'],'OPR')){
            return 'Opera';
        }
        return '';
    }
}