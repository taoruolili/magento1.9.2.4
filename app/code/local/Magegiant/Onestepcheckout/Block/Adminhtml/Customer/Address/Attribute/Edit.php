<?php


/**
 * Customer Address Attribute Edit Container Block
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Address_Attribute_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    const PAGE_TABS_BLOCK_ID = 'customer_address_attribute_edit_tabs';
    /**
     * Return current customer address attribute instance
     *
     * @return Mage_Customer_Model_Attribute
     */
    protected function _getAttribute()
    {
        return Mage::registry('entity_attribute');
    }

    /**
     * Initialize Customer Address Attribute Edit Container
     *
     */
    public function __construct()
    {
        $this->_objectId   = 'attribute_id';
        $this->_blockGroup = 'onestepcheckout';
        $this->_controller = 'adminhtml_customer_address_attribute';

        parent::__construct();

        $this->_addButton(
            'save_and_edit_button',
            array(
                'label'   => Mage::helper('onestepcheckout')->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
                'class'   => 'save'
            ),
            100
        );

        $this->_updateButton('save', 'label', Mage::helper('onestepcheckout')->__('Save Attribute'));
        if (!$this->_getAttribute()->getIsUserDefined()) {
            $this->_removeButton('delete');
        } else {
            $this->_updateButton('delete', 'label', Mage::helper('onestepcheckout')->__('Delete Attribute'));
        }
        $this->_formScripts[] = "

            function saveAndContinueEdit(urlTemplate){
                var urlTemplateSyntax = /(^|.|\\r|\\n)({{(\\w+)}})/;
                var template = new Template(urlTemplate, urlTemplateSyntax);
                var url = template.evaluate({tab_id:" . self::PAGE_TABS_BLOCK_ID . "JsTabs.activeTab.id});
                editForm.submit(url);
            }
        ";
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit',
            'tab'        => '{{tab_id}}',
            'active_tab' => null
        ));
    }

    /**
     * Return header text for edit block
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_getAttribute()->getId()) {
            $label = $this->_getAttribute()->getFrontendLabel();
            if (is_array($label)) {
                // restored label
                $label = $label[0];
            }

            return Mage::helper('onestepcheckout')->__('Edit Customer Address Attribute "%s"', $label);
        } else {
            return Mage::helper('onestepcheckout')->__('New Customer Address Attribute');
        }
    }

    /**
     * Return validation URL for edit form
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current' => true));
    }

    /**
     * Return save url for edit form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'back' => null));
    }
}
