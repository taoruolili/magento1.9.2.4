<?php
/**
 * MageWorkshop
 * Copyright (C) 2012  MageWorkshop <mageworkshophq@gmail.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2012 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

/**
 * Class MageWorkshop_DetailedReview_Block_Adminhtml_Catalog_Category_Helper_Fields_Available
 *
 * @method string getValue()
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Catalog_Category_Helper_Fields_Available
    extends Varien_Data_Form_Element_Multiselect
{
    /**
     * Retrieve Element HTML fragment
     *
     * @return string
     */
    public function getElementHtml()
    {
        $disabled = (bool) !$this->getValue();
        if ($disabled) {
            $this->setData('disabled', 'disabled');
        }
        $html = parent::getElementHtml();
        $htmlId = 'use_config_' . $this->getHtmlId();
        $html .= '<input id="'.$htmlId.'" name="use_config[]" value="' . $this->getId() . '"';
        $html .= ($disabled ? ' checked="checked"' : '');

        if ($this->getReadonly()) {
            $html .= ' disabled="disabled"';
        }

        $html .= ' onclick="toggleValueElements(this, this.parentNode);" class="checkbox" type="checkbox" />';

        $html .= ' <label for="'.$htmlId.'" class="normal">'
            . Mage::helper('detailedreview')->__('Use Parent Category Settings').'</label>';
        $html .= '<script type="text/javascript">toggleValueElements($(\''.$htmlId.'\'), $(\''.$htmlId.'\').parentNode);</script>';

        return $html;
    }
}
