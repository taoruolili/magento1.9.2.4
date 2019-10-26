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

class MageWorkshop_DetailedReview_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_PROS = 'detailedreview/proscons/pros';
    const XML_PATH_CONS = 'detailedreview/proscons/cons';

    private $baseMediaDir = null;
    private $baseDir = null;
    private $baseMediaUrl = null;
    private $optionalCategories = array();
    private $_currentProtocolSecurity;

    public function getCurrentUrlWithNewParam($key, $value) {
        $params = '?feedback=1&';
        foreach ($_GET as $k => $v) {
            if ($k == 'feedback' || ($k == $key && $v == 'true') || $k == 'show_popup' )
                continue;
            $params .= $k . '=' . (($k == $key) ? $value : $v ) . '&';
        }
        if (!isset($_GET[$key]))
            $params .= $key . '=' . $value . '&';
        return substr($params, 0, -1);
    }

    public function isInGetParams($key, $value = null) {
        if ($value) {
            return (isset($_GET[$key]) && $_GET[$key] == $value) ? true : false;
        } else {
            if (is_array($key)){
                foreach ($key as $v) {
                    if (isset($_GET[$v])){
                        return true;
                    }
                }
            }else{
                return (isset($_GET[$key])) ? true : false;
            }
            return false;
        }
    }

    public function getResizedImage($imageUrl, $w, $h, $q=100) {
        $w = $w ? (int) $w : null;
        $h = $h ? (int) $h : null;

        if (is_null($this->baseMediaDir)) {
            $this->baseDir = Mage::getBaseDir();
            $this->baseMediaDir = $this->baseDir . DS . 'media';
            $this->baseMediaUrl = Mage::getBaseUrl('media');
        }

        $dirImg = strstr($imageUrl, '/media/detailedreview');
        $dirImg = $dirImg ? $dirImg : strstr($imageUrl, '/catalog');
        $dirImg = $dirImg ? $dirImg : strstr($imageUrl, '/skin');
        if (!$dirImg) {
            return false;
        }
        $dirImg = preg_replace('/\s+/', '', $dirImg);
        $imageResized = 'catalog/resized/' . $w . 'x' . $h . $dirImg;

        $baseDir = $this->baseDir;
        $baseResizedDir = $this->baseMediaDir . DS . 'catalog' . DS . 'resized' . DS . $w . 'x' . $h;
        $dirImg = str_replace("/", DS, $dirImg);
        if (!file_exists($baseResizedDir . $dirImg)) {
            if (!file_exists($baseDir))
                mkdir($baseDir, 0777, true);
            $dirnameImg = dirname($dirImg);
            $baseResizedDirnameImg = $baseResizedDir . DS . $dirnameImg;
            if (!file_exists($baseResizedDirnameImg))
                mkdir($baseResizedDirnameImg, 0777, true);

            if (!file_exists($baseDir . $dirImg)) {
                return false;
            }
            $imageObj = new Varien_Image($baseDir . $dirImg);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
	        $imageObj->keepTransparency(true);
            $imageObj->keepFrame(false);
            $imageObj->quality($q);
            $imageObj->resize($w, $h);
            $imageObj->save($baseResizedDir . $dirImg);
        }

        return $this->baseMediaUrl . $imageResized;
    }

    public function fixFormActionForIE($action) {
        return preg_replace('/(.*)\//', '$1', $action);
    }

    public function canSendNewReviewEmail($store = null) {
        return Mage::getStoreConfigFlag(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_ENABLED, $store);
    }

    public function checkFieldAvailable($field, $product = null){
        if ( Mage::getStoreConfig('detailedreview/show_settings/allow_'.$field) ) {
            $optionalCategory = $this->getOptionalCategory($product);
            $reviewFieldsAvailable = $optionalCategory->getData('review_fields_available');
            if (is_null($reviewFieldsAvailable) || empty($reviewFieldsAvailable)) {
                return true;
            }
            if (is_string($reviewFieldsAvailable)) {
                $reviewFieldsAvailable =  explode(',', $reviewFieldsAvailable);
            }
            foreach ($reviewFieldsAvailable as $availableField ) {
                if ( $availableField == $field ) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getOptionalCategory($product = null, $param = null){
        if ( is_null($product) ) {
            $product = Mage::registry('current_product');
        }
        if ( !$product || !$product_id = $product->getId() ) return false;
        if ( is_null($param) ) {
            if ( !isset($this->optionalCategories[$product_id])) {
                $categoryCollection = $this->addReviewFieldsToSelect($product->getCategoryCollection());
                $this->tryToFindOptionalCategory($categoryCollection, $product_id, $param);
                if ( !isset($this->optionalCategories[$product_id]) ) {
                    $category = $categoryCollection->getFirstItem();
                    if ( $category->getLevel() <= 1 ) {
                        $this->optionalCategories[$product_id] = $category;
                    } else {
                        $this->getParentCategories($category,$product_id,$param);
                        if ( !isset($this->optionalCategories[$product_id]) ) {
                                $this->optionalCategories[$product_id] = $categoryCollection->getLastItem();
                        }
                    }
                }
            }
            return $this->optionalCategories[$product_id];
        }
        else {
            if ( !isset($this->optionalCategories[$param][$product_id]) ) {
                $categoryCollection = $this->addReviewFieldsToSelect($product->getCategoryCollection());
                $this->tryToFindOptionalCategory($categoryCollection, $product_id, $param);
                if ( !isset($this->optionalCategories[$param][$product_id]) ) {
                    $category = $categoryCollection->getFirstItem();
                    $this->getParentCategories($category,$product_id,$param);
                    if ( !isset($this->optionalCategories[$param][$product_id]) ) {
                        $this->optionalCategories[$param][$product_id] = $this->_getDefaults($param);
                    }
                }
            }
            return $this->optionalCategories[$param][$product_id];
        }
    }
    protected function getParentCategories($category,$product_id,$param) {
        $parentsCategories = explode('/', preg_replace('/\d+\/(.*)\/.*/','$1',$category->getPath()));
        $categoryCollection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToFilter('entity_id', array('in'=>$parentsCategories));
        $categoryCollection = $this->addReviewFieldsToSelect($categoryCollection);
        $this->tryToFindOptionalCategory($categoryCollection, $product_id,$param);
        return $this;
    }

    protected function tryToFindOptionalCategory($categoryCollection, $index, $param){
        foreach ( $categoryCollection as $category ) {
            if ( is_null($param) ){
                if ( $category->getData('review_fields_available') !== NULL  ) {
                    $this->optionalCategories[$index] = $category;
                    return $categoryCollection;
                }
            } else {
                if ( $category->getData($param) === '0'  ) {
                    $this->optionalCategories[$param][$index] = $category;
                    return $categoryCollection;
                }
            }

        }
        return $categoryCollection;
    }

    protected function addReviewFieldsToSelect($categoryCollection){
        $categoryCollection
            ->addAttributeToSelect('review_fields_available')
            ->addAttributeToSelect('use_parent_proscons_settings')
            ->addAttributeToSelect('pros')
            ->addAttributeToSelect('cons')
            ->setOrder('level','DESC');
        return $categoryCollection;
    }

    protected function _getDefaults($param)
    {
        $settings = new Varien_Object();
        switch ($param) {
            case 'use_parent_proscons_settings':
                $settings->setData(array(
                    'pros' => Mage::getStoreConfig(self::XML_PATH_PROS),
                    'cons' => Mage::getStoreConfig(self::XML_PATH_CONS)
                ));
                break;
        };
        return $settings;
    }

    public function isUserAbleToWriteReview(){
        $customerHelper = Mage::helper('customer');
        if ( $customerHelper->isLoggedIn() ){
            return $customerHelper->getCustomer()->getIsBannedWriteReview() ? false : true;
        } else {
            $authorIpModel = Mage::getModel('detailedreview/authorIps')->load(Mage::helper('core/http')->getRemoteAddr(), 'remote_addr');
            if ( $authorIpModel->getId() ) {
                if ( Mage::app()->getLocale()->date($authorIpModel->getExpirationTime()) > Mage::app()->getLocale()->date() ) {
                    return false;
                }
            }
            return true;
        }
    }

    public function getDetailReviewJsUrl(){
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . 'detailedreview';
    }

    public function getDetailReviewCssUrl(){
        return Mage::getDesign()->getSkinUrl('css/detailedreview');
    }

    public function getProsConsValues($type){
        $collection = Mage::getModel('detailedreview/review_proscons')->getCollection()
            ->setType($type)
            ->load();
        foreach ($collection as $item){
            $values[] = array('label' => $item->getName(), 'value' => $item->getEntityId());

        }
        return($values);
    }

    public function checkEnabledRatings(){
        $collection = Mage::getModel('rating/rating')->getCollection()
            ->setStoreFilter(Mage::app()->getStore()->getStoreId());
        if ($collection->getSize()){
            return true;
        }else{
            return false;
        }
    }

    public function getProsConsText($list, $type){
        $proscons_array = explode(',',$list);
        $collection = Mage::getModel('detailedreview/review_proscons')->getCollection()->setType($type)
            ->addFieldToFilter('entity_id', array('in' => $proscons_array))
            ->load();
        $values = $collection->getColumnValues('name');
        return(implode(', ', $values));
    }

    public function checkPackageFile(){
        // Find the package
        $packageFile = FALSE;
        $downloaderFiles = glob(getcwd() . DS . 'var' . DS . 'package' . DS . '*.xml');
        foreach ($downloaderFiles as $v) {
            $name = explode(DS, $v);
            $checkName = substr($name[count($name) - 1], 0, -4);
            if (strpos($checkName,'DetailedReview') !== FALSE){
                $packageFile = 'var' . DS . 'package' . DS . $name[count($name) - 1];
            }
        }
        return $packageFile;
    }

    public function getExtensionVersion()
    {
        return (string) Mage::getConfig()->getNode()->modules->MageWorkshop_DetailedReview->version;
    }

    public function uploadImages()
    {
        $files = array(
            'success' => true,
            'images'  => array(),
            'errors'  => array()
        );
        if($images = Mage::app()->getRequest()->getParam('image')) {
            $files['images'] = $images;
        }

        $errors = array();
        if(array_key_exists('image', $_FILES) && count($_FILES['image']) > 1 && !empty($_FILES['image']['name'][0])){
            foreach($_FILES['image']['name'] as $i => $value) {
                $image = array(
                    'name'      => $_FILES['image']['name'][$i],
                    'type'      => $_FILES['image']['type'][$i],
                    'tmp_name'  => $_FILES['image']['tmp_name'][$i],
                    'error'     => $_FILES['image']['error'][$i],
                    'size'      => $_FILES['image']['size'][$i]
                );
                if (empty($image['name'])){
                    $files['success'] = false;
                    $files['errors'][$image['name']][] = $this->__('Image was not uploaded!');
                    continue;
                }
                $filename = uniqid() . stripslashes($image['name']);
                $dimension = getimagesize($image['tmp_name']);
                $minWidth = Mage::getStoreConfig('detailedreview/image_options/min_image_width');
                $minHeight = Mage::getStoreConfig('detailedreview/image_options/min_image_height');
                if ($dimension[0] < $minWidth || $dimension[1] < $minHeight) {
                    $files['success'] = false;
                    $files['errors'][$image['name']][] = $this->__('One of your image dimensions is less then %dpx', $minWidth);
                    continue;
                }

                $size = filesize($image['tmp_name']);
                $maxSize = Mage::getStoreConfig('detailedreview/image_options/max_image_size');
                $maxUploadSize = Mage::helper('detailedreview')->getMaxUploadSize();
                if (($size > $maxSize * 1024 * 1024)||($size > $maxUploadSize * 1024 * 1024)) {
                    $files['success'] = false;
                    $files['errors'][$image['name']][] = $this->__('You have exceeded the size limit!');
                    continue;
                }

                //todo: Move this to config!!!!!!!
                $folder = 'media' . DS . 'detailedreview';
                $uploader = new Varien_File_Uploader($image);
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'))
                    ->setAllowRenameFiles(false)
                    ->setFilesDispersion(1);
                $new_filename = $uploader->getCorrectFileName($filename);
                try {
                    $uploader->save($folder, $new_filename);
                } catch (Exception $e) {
                    $files['success'] = false;
                    $files['errors'][$image['name']][] = $this->__('Some problems appeared while saving image.');
                    continue;
                }
                $new_filename = $uploader->getUploadedFileName();
                $files['images'][] = 'detailedreview/' . $new_filename;
            }
        }
        return $files;
    }

    public function getMaxUploadSize()
    {
        return (int) min(ini_get('post_max_size'), ini_get('upload_max_filesize'));
    }

    public function checkAvailableFilter($key)
    {
        $reviewCollection = clone Mage::getSingleton('detailedreview/review')->getReviewsCollection();
        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $totalsCollection */
        switch ($key) {
            case 'vb':
                $reviewCollection->addVerifiedBuyersFilter();
                break;
            case 'vr':
                $reviewCollection->addVideoFilter();
                break;
            case 'ir':
                $reviewCollection->addImagesFilter();
                break;
            case 'mr':
                $reviewCollection->addManuResponseFilter();
                break;
            case 'hc':
                $reviewCollection->addHighestContributorFilter();
                break;
        }
        return $reviewCollection->getSize();
    }

    /**
     * @param string $url
     * @param array $param
     * @return string
     */
    public function addRequestParam($url, $param)
    {
        $startDelimiter = (false === strpos($url,'?'))? '?' : '&';

        $arrQueryParams = array();
        foreach($param as $key=>$value) {
            if (is_numeric($key) || is_object($value)) {
                continue;
            }

            if (is_array($value)) {
                // $key[]=$value1&$key[]=$value2 ...
                $arrQueryParams[] = $key . '[]=' . implode('&' . $key . '[]=', $value);
            } elseif (is_null($value)) {
                $arrQueryParams[] = $key;
            } else {
                $arrQueryParams[] = $key . '=' . $value;
            }
        }
        $url .= $startDelimiter . implode('&', $arrQueryParams);

        return $url;
    }

    public function checkVideoLink($url = null) {
        if(is_null($url)) return null;
        $width = Mage::getStoreConfig('detailedreview/video_options/width_video_preview');
        $height = Mage::getStoreConfig('detailedreview/video_options/height_video_preview');

        if(strpos($url, 'youtube') !== false || strpos($url, 'youtu') !== false) {
            if(strpos($url, 'watch?v=') !== false) {
                if(strpos($url, '&') !== false) {
                    $videoEnd = strpos($url, '&') - 1;
                } else {
                    $videoEnd = strlen($url);
                }
                $videoStart = strpos($url, 'watch?v=') + 8;
                $video = substr($url, $videoStart, (( $videoEnd - $videoStart ) + 1));
            }
             else {
                $tmpArr = explode("/", $url);
                foreach ($tmpArr as $key => $value) {
                    if(!isset($tmpArr[$key + 1])) $video = $tmpArr[$key];
                }
                 if(strpos($video, 'watch?feature=') !== false){
                     $linkParts = explode("v=",$video);
                     if (isset($linkParts[1])) $video = $linkParts[1];
                 }
            }
            return '<iframe width="'. $width .'" height="'. $height .'" src="//www.youtube.com/embed/'. $video .'?wmode=transparent" frameborder="0" allowfullscreen></iframe>';
        }

        if(strpos($url, 'vimeo') !== false) {
            $tmpArr = explode("/", $url);
            foreach ($tmpArr as $key => $value) {
               if(!isset($tmpArr[$key + 1])) $video = $tmpArr[$key];
            }
            return '<iframe src="//player.vimeo.com/video/'. $video .'?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;color=7c9c70" width="'. $width .'" height="'. $height .'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
        }
        return null;
    }


    public function smartyModifierTruncate($string, $length = 120, $etc = '...', $break_words = false, $middle = false)
    {
        if (strlen($string) > $length) {
            $length -= strlen($etc);
            if (!$break_words && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
            }
            if(!$middle) {
                return substr($string, 0, $length).$etc;
            } else {
                return substr($string, 0, $length/2) . $etc . substr($string, -$length/2);
            }
        } else {
            return $string;
        }
    }

    public function getCurrentTheme()
    {
        return Mage::getStoreConfig('detailedreview/settings/theme');
    }

    public function applyTheme($block)
    {
        $theme = $this->getCurrentTheme();
        if ($theme == 'standard') {
            return $block;
        }
        $currentTemplate = $block->getTemplate();
        $newTemplate = str_replace('detailedreview', 'detailedreview/' . $theme, $currentTemplate);
        $block->setTemplate($newTemplate);
        return $block;
    }

    public function checkHttps()
    {
        if (empty($this->_currentProtocolSecurity)) {
            if (isset($_SERVER['HTTPS']) &&
                ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
                isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                    $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $secure = true;
            }
            else {
                $secure = false;
            }
            $this->_currentProtocolSecurity = $secure;
        } else {
            $secure = $this->_currentProtocolSecurity;
        }

        return $secure;
    }
    public function clearCacheAfterInstall()
    {
        $allTypes = Mage::app()->useCache();
        foreach($allTypes as $type => $key) {
            Mage::app()->getCacheInstance()->cleanType($type);
            Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
        }
    }

    public function reindexDataAfterInstall()
    {
        $indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection();
        foreach ($indexingProcesses as $process) {
            if ( $process->getStatus() == Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX ) {
                $process->reindexAll();
            }
        }
    }
}