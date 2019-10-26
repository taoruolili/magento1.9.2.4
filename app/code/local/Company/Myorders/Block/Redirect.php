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
class Company_Myorders_Block_Redirect extends Mage_Core_Block_Abstract
{
    function http_post($url, $data)
    {
        $options = array(
            'http' => array(
                'method' => "POST",
                'header' => "Accept-language: en\r\n" . "Cookie: foo=bar\r\n",
                'content-type' => "multipart/form-data",
                'content' => $data,
                'timeout' => 15 * 60 //超时时间（单位:s）
            )
        );
        //创建并返回一个流的资源
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    /**
     * 解析XML格式的字符串
     * @param string $str
     * @return 解析正确就返回解析结果,否则返回空,说明字符串不是XML格式
     */
    function xml_parser($str)
    {
        $xml_parser = xml_parser_create();
        if (!xml_parse($xml_parser, $str, true)) {
            xml_parser_free($xml_parser);
            return '';
        } else {
            return (json_decode(json_encode(simplexml_load_string($str)), true));
        }
    }

    protected function _toHtml()
    {
        session_start();
		
        $securePayConfig = $_SESSION['securePayConfig'];

        $url = $securePayConfig['gatewayUrl'];
		// var_dump($url);
		// exit;
	
        $standard = Mage::getModel('Myorders/payment');
        $result = $this->payment_submit($url, $standard->setOrder($this->getOrder())->getStandardCheckoutFormFields());

        $resultJSON = json_decode($result, TRUE);

        $form = new Varien_Data_Form();
        $form->setAction($this->getUrl('Myorders/payment/datapro'))
            ->setId('Myorders_payment_checkout')
            ->setName('Myorders_payment_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);

        if ($resultJSON["status"] != "0000") {
            $resultData = array(
                'msg' => $resultJSON["msg"],
                'isPendingPayment' => $resultJSON["isPendingPayment"],
                'status' => $resultJSON["status"]
            );
        }else {
            //组装返回信息
            $resultData = array(
                'Par1' => $resultJSON["data"]["par1"],
                'Par2' => $resultJSON["data"]["par2"],
                'Par3' => $resultJSON["data"]["par3"],
                'Par4' => $resultJSON["data"]["par4"],
                'Par5' => $resultJSON["data"]["par5"],
                'Par6' => $resultJSON["data"]["par6"],
                'amount' => $resultJSON["data"]["amount"],
                'status' => $resultJSON["status"],
                'HashValue' => $resultJSON["data"]["hashValue"]
            );
        }

        foreach ($resultData as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        $spinJs = $this->getSkinUrl('js/spin.min.js');
        $formHTML = $form->toHtml();
        $html = '<html><script type="text/javascript" src="'.$spinJs.'"></script>';
        $html .= '<body style="background-color:#EEEEEE;" onload=\'var spinner = new Spinner({}).spin(document.body);document.getElementById("Myorders_payment_checkout").submit();\'>';
        $html .= $formHTML;
        $html .= '</body></html>';

        return $html;
		   //$resultJSON = json_decode($result, TRUE);
		/*parse_str($result,$resultJSON);
	//	var_dump($resultJSON);
		//exit;
        $form = new Varien_Data_Form();
        $form->setAction($this->getUrl('Myorders/payment/datapro'))
            ->setId('Myorders_payment_checkout')
            ->setName('Myorders_payment_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
            //组装返回信息
        $resultData = array(
                'par1' => $resultJSON["par1"],
                'par2' => $resultJSON["par2"],
                'par3' => $resultJSON["par3"],
                'par4' => $resultJSON["par4"],
                'par5' => $resultJSON["par5"],
                'par6' => $resultJSON["par6"],
                'par7' => $resultJSON["par7"],
                'par8' => $resultJSON["par8"],
                'par9' => $resultJSON["par9"]
            );
		
	   foreach ($resultData as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
		
        $spinJs = $this->getSkinUrl('js/spin.min.js');
        $formHTML = $form->toHtml();
		  //  var_dump($formHTML);
		//exit;
        $html = '<html><script type="text/javascript" src="'.$spinJs.'"></script>';
        $html .= '<body style="background-color:#EEEEEE;" onload=\'var spinner = new Spinner({}).spin(document.body);document.getElementById("Myorders_payment_checkout").submit();\'>';
        $html .= $formHTML;
        $html .= '</body></html>';
    
        return $html;*/
    }

    function http_response($url, $status = null, $wait = 3)
    {
        $time = microtime(true);
        $expire = $time + $wait;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $returnInfo = curl_exec($ch);

        return $returnInfo;
    }

    /*
    * 通过普通的http发送post请求
    * http_build_query($post_data, '', '&')用于生成URL-encode之后的请求字符串
    * stream_context_create() 创建并返回一个流的资源
    * @param string $url 请求地址
    * @param array $post_data post键值对数据
    * @return string
    */

    /**
     * 提交支付请求
     * 其中分为两种方式提交，curl和普通的http提交
     * @return string
     */
    function payment_submit($url, $data)
    {
        //crul请求
        $returnInfo = $this->curl_post($url, $data);
        //普通http请求
        //$returnInfo = $this->http_post($url, $data);
        return $returnInfo;
    }

    /**
     * Store additional order information
     * @param String $url
     * @param String $data
     * @return Array $returnInfo
     */
    function curl_post($url, $data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_REFERER, '');
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $returnInfo = curl_exec($curl);
        curl_close($curl);
        return $returnInfo;
    }
}