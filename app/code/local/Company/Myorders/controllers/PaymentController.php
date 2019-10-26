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
class Company_Myorders_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;

    /**
     *  Get order
     *
     * @param    none
     * @return      Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
            $this->_order->loadByIncrementId($session->getLastRealOrderId());
        }
        return $this->_order;
    }

    /**
     * When a customer chooses CreditCard on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setCreditCardPaymentQuoteId($session->getQuoteId());

        $order = $this->getOrder();

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('Myorders')->__('Customer was redirected to creditcard')
        );
        $order->save();

        $this->getResponse()
            ->setBody($this->getLayout()
                ->createBlock('Myorders/redirect')
                ->setOrder($order)
                ->toHtml());

        $session->unsQuoteId();
    }

    /**
     *  CreditCard response router
     *
     * @param    none
     * @return      void
     */
    public function notifyAction()
    {
        return $this->returnAction();
    }

    /**
     *  Save invoice for order
     *
     * @param    Mage_Sales_Model_Order $order
     * @return      boolean Can save invoice or not
     */
    protected function saveInvoice(Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) {
            $convertor = Mage::getModel('sales/convert_order');
            $invoice = $convertor->toInvoice($order);
            foreach ($order->getAllItems() as $orderItem) {
                if (!$orderItem->getQtyToInvoice()) {
                    continue;
                }
                $item = $convertor->itemToInvoiceItem($orderItem);
                $item->setQty($orderItem->getQtyToInvoice());
                $invoice->addItem($item);
            }
            $invoice->collectTotals();
            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
            return true;
        }

        return false;
    }

    /**
     *  Success payment page
     *
     * @param    none
     * @return      void
     */
    public function dataproAction()
    {
        return $this->returnAction();
    }

    /**
     *  Success payment page
     *
     * @param    none
     * @return      void
     */
    public function returnAction()
    {
         session_start();
        $model = Mage::getModel('Myorders/payment');
		// $AcctNo = $_POST["par1"];
        // $orderID = $_POST["par2"];
        // $PGTxnID = $_POST["par3"];
        // $RespCode = $_POST["par4"];
        // $RespMsg = $_POST["par5"];
        // $Amount = $_POST["par6"];
		// $status=$_POST["par7"];
		// $msg=$_POST["par8"];
        // $returnedMAC = $_POST["par9"];
		
		//获取参数
        $AcctNo = $_REQUEST["par1"];
        $orderID = $_REQUEST["par2"];
        $PGTxnID = $_REQUEST["par3"];
        $RespCode = $_REQUEST["par4"];
        $RespMsg = $_REQUEST["par5"];
        $Amount = $_REQUEST["par6"];
		$status=$_REQUEST["status"];
		$msg=$_REQUEST["msg"];
        $returnedMAC = $_REQUEST["HashValue"];
        $order = $this->getOrder();
        if (!$order->getId()) {
            $order = Mage::getModel('sales/order')
                ->loadByIncrementId($orderID);
        }
		//$MD5key=$model->setOrder($this->getOrder())->getConfigData('security_code'); //获取密钥
		//$signMsgVal = $MD5key . $AcctNo . $orderID . $PGTxnID . $RespCode . $RespMsg . $Amount;
        //$secureCode.$merchantNo.$orderNo.$orderAmount.$currCode;
        //MD5检验结果
        //$correctMAC = $this->szComputeMD5Hash($signMsgVal);
        $_language_code = Mage::app()->getStore()->getCode();
		if(empty($_language_code)){
			$_language_code = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
		}
		if(empty($_language_code)){
			
			$_language_code='en';
		}
		include_once("./app/code/local/Company/Myorders/lang/".$_language_code.".php");
		if(!is_array($arr)){
			include_once("./app/code/local/Company/Myorders/lang/en.php");
		}
		$masseges=$arr;
        if($status=="0000"){ //succeed
           
           if($this->_validated()){
                $order->addStatusToHistory(
                    Mage_Sales_Model_Order::STATE_PROCESSING,
                    Mage::helper('Myorders')->__('Payment succeed!')
                );
                //$ttmp = $model->getConfigData('order_status_payment_accepted');
                $order->setState($model->getConfigData('order_status_payment_accepted'), true);
                $order->save();
                $order->sendNewOrderEmail();
                $this->_redirect('checkout/onepage/success');
              }else{
                $order->addStatusToHistory(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage::helper('Myorders')->__('Payment failed!')
                );
                //$order->setState($model->getConfigData('order_status_payment_refused'), true);
                //$order->save();
           
			  $errorMessage=str_replace('@@@','the data validation failed',$masseges["payFailure"]); 
              Mage::getSingleton('checkout/session')->setErrorMessage($errorMessage);
             
			  $this->_redirect('checkout/onepage/failure');
            }
        }elseif($status=="0004"){ //交易0004提示成功pending状态
	//	echo 2222;
		//exit;
			   $order->addStatusToHistory(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage::helper('Myorders')->__("result respcode:".$RespCode)
                );
			//	Mage::getSingleton('checkout/session')->setErrorMessage('//0002超时交易0004处理中');
               $order->sendNewOrderEmail();
                $this->_redirect('checkout/onepage/success');
		}else{
			if(empty($msg)){
				$order->addStatusToHistory(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage::helper('Myorders')->__("result respcode:".$RespCode)
                );
				$errorMessage=$masseges["payPending"];
                Mage::getSingleton('checkout/session')->setErrorMessage($errorMessage);
                $this->_redirect('checkout/onepage/failure');
				
			}else{
				
				$order->addStatusToHistory(
                       Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                      Mage::helper('Myorders')->__('Payment failed! result respcode:'.$RespCode)
                );
                //$ttmp = $model->getConfigData('order_status_payment_accepted');
			    $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true);
                $order->save();
		        $errorMessage=str_replace('@@@',$msg,$masseges["payFailure"]); 
				//echo  $errorMessage;
			//echo 111;
				//exit;
                Mage::getSingleton('checkout/session')->setErrorMessage($errorMessage);
                $this->_redirect('checkout/onepage/failure');
			}
          //  exit;
        }

    }




    private function _validated()
    {
        $AcctNo = $_REQUEST["Par1"];
        $orderID = $_REQUEST["Par2"];
        $PGTxnID = $_REQUEST["Par3"];
        $RespCode = $_REQUEST["Par4"];
        $RespMsg = $_REQUEST["Par5"];
        $Amount = $_REQUEST["Par6"];
        $returnedMAC = $_REQUEST["HashValue"];

        $model = Mage::getModel('Myorders/payment');
        //MD5私钥
        $MD5key = $model->getConfigData('security_code');
		//$MD5key=$model->setOrder($this->getOrder())->getConfigData('security_code');
        //校验源字符串
        $signMsgVal = $MD5key . $AcctNo . $orderID . $PGTxnID . $RespCode . $RespMsg . $Amount;
        //MD5检验结果
        $correctMAC = $this->szComputeMD5Hash($signMsgVal);
        if (($returnedMAC == $correctMAC) && (($RespCode == '00') || ($RespCode == 'OK'))) {
            return true;
        } else {
            return false;
        }
    }

    function szComputeMD5Hash($input)
    {
        $md5hex = md5($input);
        $len = strlen($md5hex) / 2;
        $md5raw = "";
        for ($i = 0; $i < $len; $i++) {
            $md5raw = $md5raw . chr(hexdec(substr($md5hex, $i * 2, 2)));
        }
        $keyMd5 = base64_encode($md5raw);
        return $keyMd5;
    }

    /**
     *  Success payment page
     *
     * @param    none
     * @return      void
     */
    public function successAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getCreditCardPaymentQuoteId());
        $session->unsCreditCardPaymentQuoteId();

        $order = $this->getOrder();

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('Myorders')->__('Customer successfully returned from Credit Card')
        );

        $order->save();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function failureAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getCreditCardPaymentQuoteId());
        $session->unsCreditCardPaymentQuoteId();

        $order = $this->getOrder();
        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     *  Failure payment page
     *
     * @param    none
     * @return      void
     */
    public function errorAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $errorMsg = Mage::helper('Myorders')->__('There was an error occurred during paying process.');

        $order = $this->getOrder();

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }
        if ($order instanceof Mage_Sales_Model_Order && $order->getId()) {
            $order->addStatusToHistory(
                Mage_Sales_Model_Order::STATE_CANCELED,//$order->getStatus(),
                Mage::helper('Myorders')->__('Customer returned from CreditCard.') . $errorMsg
            );

            $order->save();
        }

        $this->loadLayout();
        $this->renderLayout();
        Mage::getSingleton('checkout/session')->unsLastRealOrderId();
    }

    //判断是否是异步返回
    public function _returnMode($returnType)
    {
        if ($returnType == '3') {
            echo "ok";
            exit;
        }
    }
}
