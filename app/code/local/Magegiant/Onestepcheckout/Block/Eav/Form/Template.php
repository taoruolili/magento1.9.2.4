<?php
/**
 * Magento Magegiant Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Magegiant Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/Magegiant-edition
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
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/Magegiant-edition
 */


/**
 * EAV Dynamic attributes Form Block
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magegiant_Onestepcheckout_Block_Eav_Form_Template extends Mage_Core_Block_Abstract
{
    /**
     * Array of attribute renderers data keyed by attribute front-end type
     *
     * @var array
     */
    protected $_renderBlocks    = array();

    /**
     * Add custom renderer block and template for rendering EAV entity attributes
     *
     * @param string $type
     * @param string $block
     * @param string $template
     * @return Magegiant_Onestepcheckout_Block_Eav_Form_Template
     */
    public function addRenderer($type, $block, $template)
    {
        $this->_renderBlocks[$type] = array(
            'block'     => $block,
            'template'  => $template,
        );

        return $this;
    }

    /**
     * Return array of attribute renderers block and template data keyed by front-end type
     *
     * @return array
     */
    public function getRenderers()
    {
        return $this->_renderBlocks;
    }
}
