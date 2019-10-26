<?php
/**
 * Magento Onestepcheckout Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Onestepcheckout Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Onestepcheckout
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Customer Sales Address abstract resource
 *
 * @category    Onestepcheckout
 * @package     Magegiant_Onestepcheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Magegiant_Onestepcheckout_Model_Resource_Sales_Address_Abstract
    extends Magegiant_Onestepcheckout_Model_Resource_Sales_Abstract
{
    /**
     * Used us prefix to name of column table
     *
     * @var null | string
     */
    protected $_columnPrefix     = null;

    /**
     * Attachs data to collection
     *
     * @param Varien_Data_Collection_Db $collection
     * @return Magegiant_Onestepcheckout_Model_Resource_Sales_Address_Abstract
     */
    public function attachDataToCollection(Varien_Data_Collection_Db $collection)
    {
        $items      = array();
        $itemIds    = array();
        foreach($collection->getItems() as $item) {
            $itemIds[] = $item->getId();
            $items[$item->getId()] = $item;
        }

        if ($itemIds) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where("{$this->getIdFieldName()} IN (?)", $itemIds);
            $rowSet = $this->_getReadAdapter()->fetchAll($select);
            foreach ($rowSet as $row) {
                $items[$row[$this->getIdFieldName()]]->addData($row);
            }
        }

        return $this;
    }
}
