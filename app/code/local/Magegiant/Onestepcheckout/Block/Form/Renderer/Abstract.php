<?php



/**
 * Customer Attribute Form Renderer Abstract Block
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Magegiant_Onestepcheckout_Block_Form_Renderer_Abstract extends Magegiant_Onestepcheckout_Block_Eav_Form_Renderer_Abstract
{

    /**
     * Get additional description message for attribute field
     *
     * @return boolean|string
     */
    public function getAdditionalDescription()
    {
        $result = false;
        if ($this->isRequired() &&
            $this->getEntity()->getId() &&
            $this->getEntity()->validate() === true &&
            $this->validateValue($this->getValue()) !== true) {
                $result = Mage::helper('onestepcheckout')->__('To use this attribute in address template you should edit it here.');
            }

        return $result;
    }

    /**
     * Validate attribute value
     *
     * @param array|string $value
     * @throws Mage_Core_Exception
     * @return boolean
     */
    public function validateValue($value)
    {
        $dataModel = Mage_Customer_Model_Attribute_Data::factory($this->getAttributeObject(), $this->getEntity());
        $result = $dataModel->validateValue($this->getValue());
        return $result;
    }
}
