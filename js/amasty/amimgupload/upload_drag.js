/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Imgupload
*/

var amUploadDrag = new Class.create();

amUploadDrag.prototype = {
    
    initialize: function(dropArea)
    {
        this.dropArea = dropArea;
        if (this.supported())
        {
            this.attachListeners();
        }
    },
    
    supported: function()
    {
        var xhr = new XMLHttpRequest();  
        if (xhr.upload)
        {
            return true;
        }
        return false;
    },
    
    get: function()
    {
        if (this.supported())
        {
            return this;
        }
        return null;
    },
    
    attachListeners: function()
    {
        this.dropArea.observe('dragenter', this.onDragEnter.bind(this));
        this.dropArea.observe('dragexit', this.onDragExit.bind(this));
        this.dropArea.observe('dragover', this.onDragOver.bind(this));
        this.dropArea.observe('drop', this.onDrop.bind(this));
    },
    
    onDragEnter: function(event)
    {
        this.dropArea.addClassName('dropable-over');
        this.dropArea.select('span#drag_text').each(function(span){
            span.style.display = 'none';
        });
        this.dropArea.select('span#drop_text').each(function(span){
            span.style.display = 'block';
        });
        
        event.stopPropagation();
        event.preventDefault();
    },
    
    onDragExit: function(event)
    {
        this.dropArea.removeClassName('dropable-over');
        this.dropArea.select('span#drop_text').each(function(span){
            span.style.display = 'none';
        });
        this.dropArea.select('span#drag_text').each(function(span){
            span.style.display = 'block';
        });
        
        event.stopPropagation();
        event.preventDefault();
    },
    
    onDragOver: function(event)
    {
        event.stopPropagation();
        event.preventDefault();
    },
    
    onDrop: function(event)
    {
        this.dropArea.removeClassName('dropable-over');
        this.dropArea.select('span#drop_text').each(function(span){
            span.style.display = 'none';
        });
        this.dropArea.select('span#drag_text').each(function(span){
            span.style.display = 'block';
        });
        
        event.stopPropagation();
        event.preventDefault();
        
        this.uploader.handleFilesSelected(event);
    }
    
};
