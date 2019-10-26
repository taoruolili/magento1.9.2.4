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
 * Customer Order resource
 *
 * @category    Onestepcheckout
 * @package     Magegiant_Onestepcheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magegiant_Onestepcheckout_Model_Resource_Sales_Order extends Magegiant_Onestepcheckout_Model_Resource_Sales_Abstract
{
    /**
     * Main entity resource model name
     *
     * @var string
     */
    protected $_parentResourceModelName = 'sales/order';

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('onestepcheckout/sales_order', 'entity_id');
    }
}
