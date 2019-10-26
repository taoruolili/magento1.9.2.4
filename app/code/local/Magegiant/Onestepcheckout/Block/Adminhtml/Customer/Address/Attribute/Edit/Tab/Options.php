<?php



/**
 * Customer Address Attribute General Tab Block
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Address_Attribute_Edit_Tab_Options
    extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Options_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('onestepcheckout')->__('Manage Label / Options');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('onestepcheckout')->__('Properties');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
