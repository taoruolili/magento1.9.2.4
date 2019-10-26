<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magegiant.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2014 Magegiant (http://www.magegiant.com/)
 * @license     http://www.magegiant.com/license-agreement.html
 */

/**
 * CustomerAttributes Edit Form Content Tab Block
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magegiant Developer
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Attribute_Grid
    extends Mage_Eav_Block_Adminhtml_Attribute_Grid_Abstract
{
    /**
     * Initialize grid, set grid Id
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('customerAttributeGrid');
        $this->setDefaultSort('sort_order');
    }

    /**
     * Prepare customer attributes grid collection object
     *
     * @return Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Attribute_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/attribute_collection')
//            ->addSystemHiddenFilter()
            ->addExcludeHiddenFrontendFilter();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare customer attributes grid columns
     *
     * @return Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Attribute_Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('is_visible', array(
            'header'    => Mage::helper('onestepcheckout')->__('Visible on Frontend'),
            'sortable'  => true,
            'index'     => 'is_visible',
            'type'      => 'options',
            'options'   => array(
                '0' => Mage::helper('onestepcheckout')->__('No'),
                '1' => Mage::helper('onestepcheckout')->__('Yes'),
            ),
            'align'     => 'center',
        ));
        $this->addColumn('is_used_for_onestepcheckout', array(
            'header'   => Mage::helper('onestepcheckout')->__('Use for onestepcheckout'),
            'sortable' => true,
            'index'    => 'is_used_for_onestepcheckout',
            'type'     => 'options',
            'options'  => Mage::getModel('onestepcheckout/system_config_source_fieldOption')->toOption(),
            'align'    => 'center',
        ));

        $this->addColumn('sort_order', array(
            'header'    => Mage::helper('onestepcheckout')->__('Sort Order'),
            'sortable'  => true,
            'align'     => 'center',
            'index'     => 'sort_order'
        ));

        return $this;
    }
}
