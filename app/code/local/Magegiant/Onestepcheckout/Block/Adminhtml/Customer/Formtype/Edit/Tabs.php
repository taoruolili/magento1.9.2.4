<?php



/**
 * Fort Type Edit Tabs Block
 *
 * @category   Magegiant
 * @package    Magegiant_Onestepcheckout
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Formtype_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize edit tabs
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('onestepcheckout_formtype_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('onestepcheckout')->__('Form Type Information'));
    }
}
