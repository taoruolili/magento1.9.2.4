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
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * @category   Magegiant
 * @package    Magegiant_Onestepcheckout
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Customblock_Shoppingcart_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$model = Mage::registry('customblock_shoppingcart_data');
		$form  = new Varien_Data_Form();
		$form->setHtmlIdPrefix('rule_');

		$renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
			->setTemplate('promo/fieldset.phtml')
			->setNewChildUrl($this->getUrl('adminhtml/promo_quote/newConditionHtml/form/rule_conditions_fieldset'));

		$fieldset = $form
			->addFieldset(
				'conditions_fieldset',
				array(
					'legend' => $this->__(
						'Apply the rule only if the following conditions are met (leave blank for all products)'
					),
				)
			)
			->setRenderer($renderer);

		$fieldset
			->addField(
				'conditions',
				'text',
				array(
					'name'     => 'conditions',
					'label'    => $this->__('Conditions'),
					'title'    => $this->__('Conditions'),
					'required' => true,
				)
			)
			->setRule($model)
			->setRenderer(Mage::getBlockSingleton('rule/conditions'));

		$form->setValues($model->getData());
		$this->setForm($form);

		return parent::_prepareForm();
	}
}