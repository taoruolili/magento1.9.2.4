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

class MageWorkshop_DetailedReview_Adminhtml_MainController extends Mage_Adminhtml_Controller_Action
{
    protected $attributesCode = array(
        'review_fields_available' => array(
            'entity_type_code' => 'catalog_category',
            'entity_type' => 'text'
        ),
        'use_parent_review_settings' => array(
            'entity_type_code' => 'catalog_category',
            'entity_type' => 'int'
        ),
        'pros' => array(
            'entity_type_code' => 'catalog_category',
            'entity_type' => 'text'
        ),
        'cons' => array(
            'entity_type_code' => 'catalog_category',
            'entity_type' => 'text'
        ),
        'popularity_by_sells' => array(
            'entity_type_code' => 'catalog_product',
            'entity_type' => 'int'
        ),
        'popularity_by_reviews' => array(
            'entity_type_code' => 'catalog_product',
            'entity_type' => 'int'
        ),
        'popularity_by_rating' => array(
            'entity_type_code' => 'catalog_product',
            'entity_type' => 'int'
        )
    );

    public function uninstallAction()
    {
        $session = Mage::getSingleton('core/session');
        $helper = Mage::helper('detailedreview');
        if ($helper->checkPackageFile()) {
            try {
                $this->_clearDatabaseInformation();
                $this->_processUninstallPackage();
                Mage::app()->cleanCache();
                Mage::app()->getConfig()->reinit();
                $session->addSuccess($helper->__('Detailed Review extension has been completely uninstalled.'));
            } catch (Mage_Core_Exception $e) {
                $session->addException($e, $helper->__('There was a problem with uninstalling: %s', $e->getMessage()));
            } catch (Exception $e) {
                $session->addException($e, $helper->__('There was a problem with uninstalling.'));
            }
            $this->_redirect('adminhtml/system_config/index');
        } else {
            $session->addException($helper->__('Cannot find package file for DetailedReview plugin. Please, install extension correctly in magento downloader.'));
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'detailedreview'));
        }
    }

    private function _clearDatabaseInformation()
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->startSetup();

        $coreResource = Mage::getSingleton('core/resource');
        $reviewHelpfulTable = $coreResource->getTableName('detailedreview/review_helpful');
        $authorIpsTable = $coreResource->getTableName('detailedreview/author_ips');
        $prosCons = $coreResource->getTableName('detailedreview/review_proscons');
        $prosConsStore = $coreResource->getTableName('detailedreview/review_proscons_store');
        $reviewDetailTable = $coreResource->getTableName('review/review_detail');
        $coreResourceTable = $coreResource->getTableName('core/resource');
        $sql = "DELETE FROM `$coreResourceTable` WHERE code = 'detailedreview_setup';";

        $sql .= "DROP TABLE IF EXISTS `$reviewHelpfulTable`;";
        $sql .= "DROP TABLE IF EXISTS `$prosConsStore`;";
        $sql .= "DROP TABLE IF EXISTS `$prosCons`;";

        $sql .= "DROP TABLE IF EXISTS `$reviewHelpfulTable`;";
        $sql .= "DROP TABLE IF EXISTS `$authorIpsTable`;";
        $sql .= "ALTER TABLE `$reviewDetailTable`
                    DROP `remote_addr`,
                    DROP `sizing`,
                    DROP `body_type`,
                    DROP `location`,
                    DROP `age`,
                    DROP `height`,
                    DROP `good_detail`,
                    DROP `no_good_detail`,
                    DROP `response`,
                    DROP `image`,
                    DROP `video`,
                    DROP `pros`,
                    DROP `cons`,
                    DROP `recommend_to`;
                    ";

        $eavAttribute = $setup->getTable('eav_attribute');
        $eavEntityType = $setup->getTable('eav_entity_type');

        $customerEavAttribute = $setup->getTable('customer_eav_attribute');
        $customerEntityInt = $setup->getTable('customer_entity_int');

        $sqlSelect = "SELECT attribute_id FROM `$eavAttribute`
                        LEFT JOIN `$eavEntityType` ON $eavEntityType.entity_type_id = $eavAttribute.entity_type_id
                        WHERE (attribute_code = 'is_banned_write_review' AND $eavEntityType.entity_type_code = 'customer')";
        $data = $coreResource->getConnection('core_read')->fetchOne($sqlSelect);
        if(!empty($data)){
            $sql .= "DELETE `$eavAttribute` FROM `$eavAttribute` LEFT JOIN `$eavEntityType` ON $eavEntityType.entity_type_id = $eavAttribute.entity_type_id WHERE (attribute_code = 'is_banned_write_review' AND $eavEntityType.entity_type_code = 'customer');
                     DELETE FROM `$customerEavAttribute` WHERE attribute_id IN ($data);
                     DELETE FROM `$customerEntityInt` WHERE attribute_id IN ($data);";
        }

        $catalogEavAttribute = $setup->getTable('catalog_eav_attribute');
        $catalogCategoryEntityText = $setup->getTable('catalog_category_entity_text');
        $catalogCategoryEntityInt = $setup->getTable('catalog_category_entity_int');
        $catalogProductEntityInt = $setup->getTable('catalog_product_entity_int');

        foreach ($this->attributesCode as $attributeCode => $attribute) {
            $entityTypeCode = $attribute['entity_type_code'];
            $sqlSelect = "SELECT attribute_id FROM `$eavAttribute`
                        LEFT JOIN `$eavEntityType` ON $eavEntityType.entity_type_id = $eavAttribute.entity_type_id
                        WHERE (attribute_code = '$attributeCode' AND $eavEntityType.entity_type_code = '$entityTypeCode')";
            $data = $coreResource->getConnection('core_read')->fetchOne($sqlSelect);
            if(!empty($data)){
                $catalogEntity = ($attribute['entity_type_code'] == 'catalog_category') ? (($attribute['entity_type'] == 'text') ? $catalogCategoryEntityText : $catalogCategoryEntityInt) : $catalogProductEntityInt;
                $sql .= "DELETE `$eavAttribute`  FROM `$eavAttribute` LEFT JOIN `$eavEntityType` ON $eavEntityType.entity_type_id = $eavAttribute.entity_type_id WHERE (attribute_code = '$attributeCode' AND $eavEntityType.entity_type_code = '$entityTypeCode');
                     DELETE FROM `$catalogEavAttribute` WHERE attribute_id IN ($data);
                     DELETE FROM `$catalogEntity` WHERE attribute_id IN ($data);";
            }
        }

        $setup->run($sql);
        $setup->endSetup();
    }

    private function _processUninstallPackage()
    {
        $packageFile = Mage::helper('detailedreview')->checkPackageFile();

        if ($packageFile) {
            try {
                $package = new Mage_Connect_Package($packageFile);
                $contents = $package->getContents();

                $targetPath = rtrim(getcwd(), "\\/");
                foreach ($contents as $file) {
                    $fileName = basename($file);
                    $filePath = dirname($file);
                    $dest = $targetPath . DS . $filePath . DS . $fileName;
                    if (@file_exists($dest)) {

                        @unlink($dest);
                        $this->_removeEmptyDirectory(dirname($dest));
                    }
                }

                $destDir = $targetPath . DS . 'var' . DS . 'package';
                $downloaderCacheFile = $targetPath . DS . 'downloader' . DS . 'cache.cfg';
                $destFile = $package->getReleaseFilename() . '.xml';
                @unlink($destDir . DS . $destFile);
                @unlink($downloaderCacheFile);
            } catch (Exception $e) {
                $session = Mage::getSingleton('core/session');
                $session->addException($e, Mage::helper('detailedreview')->__('There was a problem with uninstalling.'));
                return false;
            }
        }
        return true;
    }

    /**
     * Remove empty directories recursively up
     *
     * @param string $dir
     * @param Mage_Connect_Ftp $ftp
     */
    private function _removeEmptyDirectory($dir, $ftp = null)
    {
        if ($ftp) {
            if (count($ftp->nlist($dir))==0) {
                if ($ftp->rmdir($dir)) {
                    $this->_removeEmptyDirectory(dirname($dir), $ftp);
                }
            }
        } else {
            if (@rmdir($dir)) {
                $this->_removeEmptyDirectory(dirname($dir), $ftp);
            }
        }
    }
}

