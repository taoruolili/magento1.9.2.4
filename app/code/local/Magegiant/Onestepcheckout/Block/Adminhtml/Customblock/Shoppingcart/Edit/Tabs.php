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
 * @package     Magegiant_CheckoutPromotion
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * @category   Magegiant
 * @package    Magegiant_Onestepcheckout
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Customblock_Shoppingcart_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('onestepcheckout_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('onestepcheckout')->__('Rule Information'));
	}

	/**
	 * prepare before render block to html
	 *
	 * @return Magegiant_CheckoutPromotion_Block_Adminhtml_Checkoutpromotion_Edit_Tabs
	 */
	protected function _beforeToHtml()
	{
		$this->addTab('form_section', array(
			'label'   => Mage::helper('onestepcheckout')->__('Rule Information'),
			'title'   => Mage::helper('onestepcheckout')->__('Rule Information'),
			'content' => $this->getLayout()
				->createBlock('onestepcheckout/adminhtml_customblock_shoppingcart_edit_tab_form')
				->toHtml(),
		));

		$this->addTab(
			'conditions_section',
			array(
				'label'   => $this->__('Conditions'),
				'title'   => $this->__('Conditions'),
				'content' => $this->getLayout()->createBlock('onestepcheckout/adminhtml_customblock_shoppingcart_edit_tab_conditions')->toHtml(),
			)
		);

		$this->addTab(
			'actions_section',
			array(
				'label'   => $this->__('Actions'),
				'title'   => $this->__('Actions'),
				'content' => $this->getLayout()->createBlock('onestepcheckout/adminhtml_customblock_shoppingcart_edit_tab_actions')->toHtml(),
			)
		);
		$this->_updateActiveTab();

		return parent::_beforeToHtml();
	}

	protected function _updateActiveTab()
	{
		$tabId = $this->getRequest()->getParam('tab');
		if ($tabId) {
			$tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
			if ($tabId) {
				$this->setActiveTab($tabId);
			}
		}
	}

}