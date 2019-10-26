<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magegiant Developer
 */
class Magegiant_Onestepcheckout_Block_Customblock_Abstract extends Mage_Core_Block_Template
{


    protected $_quote;
    protected $_appliedRules = array();

    /**
     * prepare block's layout
     *
     * @return Magegiant_Onestepcheckout_Block_Checkoutpromotion
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }


    public function __construct()
    {
        parent::__construct();

    }

    /**
     * @return array
     */
    public function getAppliedRules($page, $position)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer && $this->_quote && count($this->_quote->getAllItems())) {
            try {
                $ruleCollection = $this->getRuleCollection($page, $position);
                foreach ($ruleCollection as $rule) {
                    if ($rule->checkRule($this->_quote)) {
                        $this->_appliedRules[] = $rule;
                        if ($rule->getStopRules()) {
                            break;
                        }
                    }
                }
                //endforeach
            } catch (Exception $e) {
                Mage::log($e->getMessage());
            }

        }

        return $this->_appliedRules;
    }

    public function getFilterBlocks($rule)
    {
        $helper    = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        $blocksIds = explode(',', $rule->getStaticBlocksIds());
        $toReturn  = array();
        foreach ($blocksIds as $blockId) {
            $content    = Mage::getModel('cms/block')->load($blockId)->getContent();
            $toReturn[] = $processor->filter($content);;
        }

        return $toReturn;
    }

    public function getBlockToHtml($page, $position)
    {
        $html  = '';
        $rules = $this->getAppliedRules($page, $position);
        if (count($rules)) {
            foreach ($rules as $rule) {
                $blocks = $this->getFilterBlocks($rule);
                $html .= implode('', $blocks);
            }
        }

        return $html;
    }


    public function getRuleCollection($page, $position)
    {
        $customer       = Mage::getModel('customer/session')->getCustomer();
        $ruleCollection = Mage::getModel('onestepcheckout/customblock_shoppingcart')
            ->getCollection()
            ->isActiveFilter()
            ->addFilterByCustomerGroup($customer->getGroupId())
            ->addFilterByWebsiteId(Mage::app()->getWebsite()->getId())
            ->setOrder('sort_order', Varien_Data_Collection::SORT_ORDER_DESC);
        if ($page == 'onestepcheckout') {
            $ruleCollection->addFieldToFilter('is_onestepcheckout', $position);
        } else {
            $ruleCollection->addFieldToFilter('is_checkout_success', $position);
        }

        return $ruleCollection;
    }
}