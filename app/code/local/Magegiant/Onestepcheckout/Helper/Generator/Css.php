<?php

class Magegiant_Onestepcheckout_Helper_Generator_Css extends Mage_Core_Helper_Abstract
{
    /**
     * Path and directory of the automatically generated CSS
     *
     * @var string
     */
    protected $_generatedCssFolder;
    protected $_generatedCssPath;
    protected $_generatedCssDir;
    protected $_templatePath;

    public function __construct()
    {
        //Create paths
        $this->_generatedCssFolder = 'magegiant/onestepcheckout/css/_config/';
        $this->_templatePath       = 'magegiant/onestepcheckout/generator/css/';
        $this->_generatedCssDir    = Mage::getBaseDir('media') . '/' . $this->_generatedCssFolder;
        $this->_generatedCssPath   = Mage::getBaseUrl('media') . '/' . $this->_generatedCssFolder;
    }

    /**
     * Get directory of automatically generated CSS
     *
     * @return string
     */
    public function getGeneratedCssDir()
    {
        return $this->_generatedCssDir;
    }

    /**
     * Get path to CSS template
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->_templatePath;
    }

    /**
     * Get file path: CSS design
     *
     * @return string
     */
    public function getDesignFile()
    {
        $file = $this->_generatedCssPath . 'design_' . Mage::app()->getStore()->getCode() . '.css';
        $link = '<link rel="stylesheet" type="text/css" href="' . $file . '" />';

        return $link;
    }
}
