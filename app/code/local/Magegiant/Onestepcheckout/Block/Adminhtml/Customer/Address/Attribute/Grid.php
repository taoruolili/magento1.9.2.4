<?php



/**
 * Customer Address Attributes Grid Block
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Address_Attribute_Grid
    extends Mage_Eav_Block_Adminhtml_Attribute_Grid_Abstract
{
    /**
     * Initialize grid, set grid Id
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('sort_order');
        $this->setId('customerAddressAttributeGrid');
    }

    /**
     * Prepare customer address attributes grid collection object
     *
     * @return Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Address_Attribute_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Customer_Model_Entity_Address_Attribute_Collection */
        $collection = Mage::getResourceModel('customer/address_attribute_collection')
            //->addSystemHiddenFilter()
            ->addExcludeHiddenFrontendFilter();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare customer address attributes grid columns
     *
     * @return Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Address_Attribute_Grid
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
            'header'   => Mage::helper('onestepcheckout')->__('Show on Giant One Step Checkout'),
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
