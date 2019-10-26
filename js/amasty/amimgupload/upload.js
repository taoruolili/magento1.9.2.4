/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Imgupload
*/

var amUpload = new Class.create();

amUpload.prototype = {
    
    initialize: function(formName, dropAreaName, fileSelectName, uploadTrackingAreaName, productId, reloadTabUrl)
    {
        if (this.supported())
        {
            this.productId    = productId;
            this.form         = $(formName);
            this.uploadArea   = $(uploadTrackingAreaName);
            this.reloadTabUrl = reloadTabUrl;
            
            this.select = new amUploadSelect($(fileSelectName)).get();
            if (null != this.select)
            {
                this.select.uploader = this;
            }
            
            this.drag = new amUploadDrag($(dropAreaName)).get();

            if (null != this.drag)
            {
                this.drag.uploader = this;
                $(dropAreaName).style.display = 'block';
            }
        }
    },
    
    supported: function()
    {
        if (window.File && window.FileList)
        {
            return true;
        }
        return false;
    },
    
    handleFilesSelected: function(event)
    {
        var files = event.target.files || event.dataTransfer.files;
        for (var i = 0, f; f = files[i]; i++) {
        	var formData = new FormData();
            // handling only images
            if (f.type.indexOf("image") == 0) {
            	formData.append("form_key", FORM_KEY);
            	formData.append("product_id", this.productId);
            	formData.append("file_select", f);
                this.upload(f, formData);
            }
        }
    },
    
    upload: function(file, formData)
    {
    	var xhr       = new XMLHttpRequest();
    	var container = this.startUpload(file);

        Event.observe(xhr.upload, 'progress', this.trackProgress.bindAsEventListener(this, container));
        Event.observe(xhr.upload, 'load', this.uploadComplete.bindAsEventListener(this, container));
        Event.observe(xhr.upload, 'error', this.uploadFailed.bindAsEventListener(this, container));
        Event.observe(xhr.upload, 'abort', this.uploadCanceled.bindAsEventListener(this, container));
        Event.observe(xhr, 'readystatechange', this.uploadStateChanged.bindAsEventListener(this, container, xhr));
        
        xhr.open("POST", this.form.readAttribute('action'), true);
        xhr.send(formData);
    },
    
    uploadStateChanged: function()
    {
        var args = $A(arguments);
        event = container = args[0];
        args.shift();
        container = args[0];
        xhr = args[1];

        if (200 == xhr.status && 4 == xhr.readyState)
        {
            if (!this.productId)
            {
                this.addNewImage(xhr.responseText, container);
            }
        }
    },
    
    startUpload: function(file)
    {
        container = document.createElement('div');
        container.addClassName('file');
        
        progress = document.createElement('div');
        progress.addClassName('progress');
        progressBar = document.createElement('div');
        progressBar.addClassName('progress-bar');
        progressBar.innerHTML = '&nbsp;';
        progressBar.style.width = '0%';
        progress.appendChild(progressBar);
        
        fileName = document.createElement('div');
        fileName.addClassName('file-name');
        fileName.innerHTML = ( typeof(file.fileName) == "undefined" ? file.name : file.fileName );
        
        percentage = document.createElement('div');
        percentage.addClassName('file-percentage');
        percentage.innerHTML = '(0%)';
        
        container.appendChild(progress);
        container.appendChild(fileName);
        container.appendChild(percentage);
        
        this.uploadArea.appendChild(container);
        
        return container;
    },
    
    trackProgress: function(event)
    {
        var percent = parseInt((event.loaded / event.total * 100));
        var args = $A(arguments);
        args.shift();
        container = args[0];
        
        if (container)
        {
            container.select('div.progress-bar').each(function(bar){
                bar.style.width = percent + '%';
            });
            container.select('div.file-percentage').each(function(prc){
                prc.innerHTML = '(' + percent + '%)';
            });
        }
    },
    
    uploadComplete: function(event)
    {
        var args = $A(arguments);
        args.shift();
        container = args[0];
        
        if (container)
        {
            container.select('div.progress-bar').each(function(bar){
                bar.style.width = 100 + '%';
            });
            container.select('div.file-percentage').each(function(prc){
                prc.innerHTML = '(' + 100 + '%)';
            });
            container.addClassName('upload-complete');
        }
        
        this.checkAllUploaded();
    },
    
    uploadFailed: function(event)
    {
        var args = $A(arguments);
        args.shift();
        container = args[0];
        
        if (container)
        {
            container.addClassName('upload-complete');
        }
        
        this.checkAllUploaded();
    },
    
    uploadCanceled: function(event)
    {
        var args = $A(arguments);
        args.shift();
        container = args[0];
        
        if (container)
        {
            container.addClassName('upload-complete');
        }
        
        this.checkAllUploaded();
    },
    
    checkAllUploaded: function()
    {
        var notUploadedCnt = 0;
        this.uploadArea.select('.file').each(function(container){
            if (!container.hasClassName('upload-complete'))
            {
                notUploadedCnt++;
            }
        });
        if (0 == notUploadedCnt && this.productId)
        {
            this.reloadTabContents();
        }
    },
    
    reloadTabContents: function()
    {
        if ('fieldset' == this.form.tagName.toLowerCase())
        {
            this.form.wrap('form'); // we need form to be able to serialize it. but initially we use fieldset, becuase form inside form is incorrect.
            this.form = this.form.parentNode;
        }
        new Ajax.Updater('product_info_tabs_product_images_content', this.reloadTabUrl, {
            parameters: this.form.serialize(true),
            evalScripts: true
        });
    },
    
    addNewImage: function(imageDataStr, uploadContainer)
    {
        if (uploadContainer.hasClassName('image-created'))
        {
            return false;
        }
        uploadContainer.addClassName('image-created');
        var imageData = imageDataStr.evalJSON();
        if (!$('am_images_grid'))
        {
            var grid = document.createElement('div');
            grid.id = 'am_images_grid';
            grid.addClassName('am_images_grid');
            $('am_images_grid_new_container').appendChild(grid);
            this.imgNum = 1;
        }
        
        var item = imItemTemplate.replace(/{i}/g, this.imgNum); 
        item = item.replace(/{url}/g, imageData.url);
        item = item.replace(/{file}/g, imageData.file);
        
        $('am_images_grid').innerHTML += item;
        
        Sortable.create('am_images_grid', {
            tag: 'div',
            only: 'am_item',
            handles: $$('#am_images_grid div.' + imDragHandler),
            overlap: 'horizontal',
            constraint: false,
            onUpdate: function(){
                Sortable.sequence("am_images_grid").each(function(idNum, i){
                    $('am_images_grid_amitem_' + idNum).select('.img-position-input').each(function(input){
                        input.value = i + 1;
                    });
                });
            }
        });
                    
        this.imgNum++;
    }
};
