<?php



/**
 * Customer address attributes Grid Container
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Address_Attribute
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Define controller, block and labels
     *
     */
    public function __construct()
    {
        $this->_blockGroup = 'onestepcheckout';
        $this->_controller = 'adminhtml_customer_address_attribute';
        $this->_headerText = Mage::helper('onestepcheckout')->__('Manage Customer Address Attributes');
        parent::__construct();
    }
}
