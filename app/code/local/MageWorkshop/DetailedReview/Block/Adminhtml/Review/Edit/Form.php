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
class MageWorkshop_DetailedReview_Block_Adminhtml_Review_Edit_Form extends Mage_Adminhtml_Block_Review_Edit_Form
{
    /**
     * @inherit
     */
    protected function _prepareForm()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::_prepareForm();
        }
        $helper = Mage::helper('detailedreview');
        /** @var Mage_Review_Helper_Data $reviewHelper */
        $reviewHelper = Mage::helper('review');
        $review = Mage::registry('review_data');
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')->load($review->getEntityPkValue());
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load($review->getCustomerId());
        $statuses = Mage::getModel('review/review')
            ->getStatusCollection()
            ->load()
            ->toOptionArray();

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => (int) $this->getRequest()->getParam('id'), 'ret' => Mage::registry('ret'))),
            'method'    => 'post',
            'enctype'	=> 'multipart/form-data'
        ));

        $fieldSet = $form->addFieldset('review_details', array('legend' => $reviewHelper->__('Review Details')));

        $fieldSet->addField('product_name', 'note', array(
            'label'     => Mage::helper('review')->__('Product'),
            'text'      => '<a href="' . $this->getUrl('*/catalog_product/edit', array('id' => $product->getId())) . '" onclick="this.target=\'blank\'">' . $product->getName() . '</a>'
        ));

        $customerText = '';
        if ($customer->getId()) {
            $customerText = $reviewHelper->__('<a href="%1$s" onclick="this.target=\'blank\'">%2$s %3$s</a> <a href="mailto:%4$s">(%4$s)</a>',
                $this->getUrl('*/customer/edit', array('id' => $customer->getId(), 'active_tab'=>'review')),
                $this->escapeHtml($customer->getFirstname()),
                $this->escapeHtml($customer->getLastname()),
                $this->escapeHtml($customer->getEmail()));
        } else {
            if (is_null($review->getCustomerId())) {
                $customerText = $reviewHelper->__('Guest');
            } elseif ($review->getCustomerId() == 0) {
                $customerText = $reviewHelper->__('Administrator');
            }
        }

        $fieldSet->addField('customer', 'note', array(
            'label'     => $reviewHelper->__('Posted By'),
            'text'      => $customerText,
        ));

        /** @var Mage_Adminhtml_Block_Review_Rating_Summary $summaryBlock */
        $summaryBlock = $this->getLayout()->createBlock('adminhtml/review_rating_summary');
        $fieldSet->addField('summary_rating', 'note', array(
            'label'     => $reviewHelper->__('Average Rating'),
            'text'      => $summaryBlock->toHtml(),
        ));

        /** @var Mage_Adminhtml_Block_Review_Rating_Detailed $ratingBlock */
        $ratingBlock = $this->getLayout()->createBlock('adminhtml/review_rating_detailed');
        $fieldSet->addField('detailed_rating', 'note', array(
            'label'     => $reviewHelper->__('Overall Rating'),
            'required'  => true,
            'text'      => '<div id="rating_detail">' . $ratingBlock->toHtml() . '</div>',
        ));

        $fieldSet->addField('status_id', 'select', array(
            'label'     => $reviewHelper->__('Status'),
            'required'  => true,
            'name'      => 'status_id',
            'values'    =>$reviewHelper->translateArray($statuses),
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldSet->addField('select_stores', 'multiselect', array(
                'label'     => $reviewHelper->__('Visible In'),
                'required'  => true,
                'name'      => 'stores[]',
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
            ));
            $review->setSelectStores($review->getStores());
        }
        else {
            $fieldSet->addField('select_stores', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $review->setSelectStores(Mage::app()->getStore(true)->getId());
        }

        $fieldSet->addField('nickname', 'text', array(
            'label'     => $reviewHelper->__('Nickname'),
            'required'  => true,
            'name'      => 'nickname'
        ));


        if (Mage::getStoreConfig('detailedreview/show_settings/allow_image')) {
            $element = $fieldSet->addField('image', 'hidden', array(
                'label'     => $helper->__('Image'),
                'required'  => true,
                'name'      => 'image'
            ));
            /** @var MageWorkshop_DetailedReview_Block_Adminhtml_Renderer_MultiImage $renderer */
            $renderer = Mage::app()->getLayout()->createBlock('detailedreview/adminhtml_renderer_multiImage');
            $element->setRenderer($renderer);
        }

        if (Mage::getStoreConfig('detailedreview/social_share_optios/recommended_product')) {
            $fieldSet->addField('recommend_to', 'select', array(
                'name'      => 'recommend_to',
                'label'     => $helper->__('Recommend this product to a friend'),
                'options' => Mage::getSingleton('detailedreview/review_recommendProduct')->getOptionArray(),
            ));
        }

        $fieldSet->addField('title', 'text', array(
            'label'     => $reviewHelper->__('Review Title'),
            'required'  => true,
            'name'      => 'title',
        ));

        if (Mage::getStoreConfig('detailedreview/show_settings/allow_video')) {
            $fieldSet->addField('video', 'text', array(
                'label'     => $helper->__('Attached Video'),
                'name'      => 'video',
            ));
        }

        if (Mage::getStoreConfig('detailedreview/show_settings/allow_sizing')) {
            $fieldSet->addField('sizing', 'select', array(
                'name'      => 'sizing',
                'title' => $helper->__('Sizing'),
                'label'     => $helper->__('Sizing'),
                'options' => Mage::getSingleton('detailedreview/review_sizing')->getOptionArray(),
            ));
        }

        if (Mage::getStoreConfig('detailedreview/show_settings/allow_about_you') ) {
            $fieldSet->addField('body_type', 'select', array(
                'name'      => 'body_type',
                'title' => $helper->__('Body Type'),
                'label'     => $helper->__('Body Type'),
                'options' => Mage::getSingleton('detailedreview/review_bodyType')->toOptionArray(),
            ));
            $fieldSet->addField('location', 'text', array(
                'label'     => $helper->__('Location'),
                'name'      => 'location',
            ));
            $fieldSet->addField('age', 'text', array(
                'label'     => $helper->__('Age'),
                'name'      => 'age',
            ));
            $fieldSet->addField('height', 'text', array(
                'label'     => $helper->__('Height'),
                'name'      => 'height',
            ));
        }

        if (Mage::getStoreConfig('detailedreview/show_settings/allow_good_and_bad_detail')) {
            $fieldSet->addField('good_detail', 'textarea', array(
                'name'      => 'good_detail',
                'title'     => $helper->__('What do you like about this item?'),
                'label'     => $helper->__('What do you like about this item?'),
                'style'     => 'width: 700px; height: 300px;',
            ));

            $fieldSet->addField('no_good_detail', 'textarea', array(
                'name'      => 'no_good_detail',
                'title'     => $helper->__('What do you dislike about this item?'),
                'label'     => $helper->__('What do you dislike about this item?'),
                'style'     => 'width: 700px; height: 300px;',
            ));
        }

        if (Mage::getStoreConfig('detailedreview/show_settings/allow_pros_and_cons')) {
            $fieldSet->addField('pros', 'multiselect', array(
                'name'      => 'pros',
                'label'     => $helper->__('Pros'),
                'title'     => $helper->__('Pros'),
                'values'    => Mage::helper('detailedreview')->getProsConsValues(MageWorkshop_DetailedReview_Model_Source_EntityType::PROS),
            ));

            $fieldSet->addField('cons', 'multiselect', array(
                'name'      => 'cons',
                'label'     => $helper->__('Cons'),
                'title'     => $helper->__('Cons'),
                'values'    => Mage::helper('detailedreview')->getProsConsValues(MageWorkshop_DetailedReview_Model_Source_EntityType::CONS),
            ));
        }

        $fieldSet->addField('detail', 'textarea', array(
            'label'     => $reviewHelper->__('Overall Review'),
            'required'  => true,
            'name'      => 'detail',
            'style'     => 'width:700px; height:24em;',
        ));

        if (Mage::getStoreConfig('detailedreview/show_settings/allow_response')) {
            $fieldSet->addField('response', 'textarea', array(
                'label'     => $helper->__('Administration Response'),
                'name'      => 'response',
                'style'     => 'width:700px; height:24em;',
            ));
        }
        $dateFormat = Mage::getStoreConfig('detailedreview/datetime_options/date_format');
        $timeFormat = Mage::getStoreConfig('detailedreview/datetime_options/time_format');
        if (($dateFormat !== '') && ($timeFormat !== '')) {
            $dateTimeFormat = $dateFormat.' '.$timeFormat;
        } else {
            $dateTimeFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        }

        $fieldSet->addField('created_at', 'date', array(
            'label'		=> $reviewHelper->__('Created at'),
            'required'	=> false,
            'name'		=> 'created_at',
            'time'		=> true,
            'format' => $dateTimeFormat,
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'style' => 'width: 140px;'
        ));

        $form->setUseContainer(true);
        $form->setValues($review->getData());
        $this->setForm($form);
        return $this;
    }
}