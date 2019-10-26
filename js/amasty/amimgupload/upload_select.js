/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Imgupload
*/

var amUploadSelect = new Class.create();

amUploadSelect.prototype = {
    
    initialize: function(fileSelect)
    {
        this.fileSelect = fileSelect;
        this.attachListeners();
    },
    
    get: function()
    {
        return this;
    },
    
    attachListeners: function()
    {
        this.fileSelect.observe('change', this.onChange.bind(this));
    },
    
    onChange: function(event)
    {
        event.stopPropagation();
        event.preventDefault();
        
        this.uploader.handleFilesSelected(event);
    }
    
};
