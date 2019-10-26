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
class Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Formtype_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize Grid Block
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('code');
        $this->setDefaultDir('asc');
    }

    /**
     * Prepare grid collection object
     *
     * @return Enterprice_Customer_Block_Adminhtml_Customer_Formtype_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('eav/form_type')
            ->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid columns
     *
     * @return Enterprice_Customer_Block_Adminhtml_Customer_Formtype_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header'    => Mage::helper('onestepcheckout')->__('Form Type Code'),
            'index'     => 'code',
        ));

        $this->addColumn('label', array(
            'header'    => Mage::helper('onestepcheckout')->__('Label'),
            'index'     => 'label',
        ));

        $this->addColumn('store_id', array(
            'header'    => Mage::helper('onestepcheckout')->__('Store View'),
            'index'     => 'store_id',
            'type'      => 'store'
        ));

        $design = Mage::getModel('core/design_source_design')
            ->setIsFullLabel(true)->getAllOptions(false);
        array_unshift($design, array(
            'value' => 'all',
            'label' => Mage::helper('onestepcheckout')->__('All Themes')
        ));
        $this->addColumn('theme', array(
            'header'     => Mage::helper('onestepcheckout')->__('For Theme'),
            'type'       => 'theme',
            'index'      => 'theme',
            'options'    => $design,
            'with_empty' => true,
            'default'    => Mage::helper('onestepcheckout')->__('All Themes')
        ));

        $this->addColumn('is_system', array(
            'header'    => Mage::helper('onestepcheckout')->__('System'),
            'index'     => 'is_system',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('onestepcheckout')->__('No'),
                1 => Mage::helper('onestepcheckout')->__('Yes'),
            )
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve row click URL
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('type_id' => $row->getId()));
    }
}
