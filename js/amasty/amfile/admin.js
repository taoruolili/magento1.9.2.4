document.observe("dom:loaded", function(){
    window.amfile_dragndrop = 'draggable' in $('amfile-uploads');
    window.amfile_new_upload_template = $$('#amfile-uploads .box:last-child')[0].clone(true);
    window.amfile_new_upload_id = -1;

    $('amfile-uploads').down('button.add').observe('click', addNewFile);

    if (amfile_dragndrop)
    {
        var drop = $('amfile-uploads').down('.main.drop');
        drop.show();

        drop.observe('drop', function(e){
            e.stopPropagation();
            e.preventDefault();

            updateDrag(e);

            dropMultipleFiles(e.dataTransfer.files);
        });

        drop.observe('dragover', updateDrag);
        drop.observe('dragenter', updateDrag);
        drop.observe('dragleave', updateDrag);

        var input = new Element('input', {type: 'file', multiple: true, style:'display:none'});

        drop.observe('click', function(){
            input.click();
        });

        input.observe('change', function(){
            dropMultipleFiles(this.files);
        });

        drop.insert({after: input});
    }

    updateFileBoxes();
});

function updateFileBoxes(blink)
{
    $$('#amfile-uploads .box:not(.ready)').each(function(element) {
        bindActions(element, blink);
    });
}

function bindActions(box, blink)
{
    box.down('.delete').observe('click', removeFile);

    box.select('.default-value').each(function(element) {
        element.observe('click', useDefault);
    });

    box.down('input[name*=file_link]').observe('change', function(){
        box.down('input[name*=use][value=url]').checked = true;
    }.bind(box))

    box.down('input[name*=\[file\]]').observe('change', function(){
        box.down('input[name*=use][value=file]').checked = true;
    }.bind(box))

    if (amfile_dragndrop)
    {
        var drop = box.down('.drop');
        drop.show();

        box.down('input[type=file]').observe('change', function(){
            submitFile(box);
        }.bind(box));

        drop.observe('click', function(){
            box.down('input[type=file]').click();
        }.bind(box));

        drop.observe('drop', dropFile);

        drop.observe('dragover', updateDrag);
        drop.observe('dragenter', updateDrag);
        drop.observe('dragleave', updateDrag);

        box.down('input[type=file]').hide();
    }

    if (blink)
    {
        box.setStyle({'background':'#5c5'});

        new Effect.Morph(box, {
            style: {'background-color':'#E7EFEF'},
            duration: 3
        });
    }

    box.addClassName('ready');
}

function submitFile(box, file)
{
    showPreloader(box);

    var fd = new FormData;

    var elements = box.select('input:not([type=file]), select');

    elements.each(function(element){
        fd.append(element.name, element.value);
    });

    var fileInput = box.down('input[type=file]');

    fd.append(fileInput.name, file ? file : fileInput.files[0]);

    fd.append('id', $('amfile_product_id').value);
    fd.append('store', $('amfile_store_id').value);
    fd.append('form_key', FORM_KEY);


    var xhr = new XMLHttpRequest();

    xhr.addEventListener('load', function(e){
        var response = e.target.response.evalJSON();
        if (response.errors.length > 0)
        {
            removePreloader(box);
            Effect.Shake(box)
            alert(response.errors[0])
        }
        else
        {
            box.replace(response.content);
            updateFileBoxes(true);
        }
    }, false);

    xhr.open('POST', $('amfile_ajax_action').value);
    xhr.send(fd);
}

function showPreloader(box)
{
    var preloader = new Element('div', {class: 'preloader'});
    preloader.appendChild(new Element('img', {src: $('loading-mask').down('img').readAttribute('src')}));

    box.appendChild(preloader);
}

function removePreloader(box)
{
    box.down('.preloader').remove();
}

function updateDrag(e)
{
    e.stopPropagation();
    e.preventDefault();

    if (e.target.tagName == 'DIV')
    {
        if (e.type == 'dragover')
            e.target.addClassName('hover');
        else
            e.target.removeClassName('hover');
    }
}

function dropFile(e)
{
    e.stopPropagation();
    e.preventDefault();

    updateDrag(e);

    if (e.dataTransfer.files.length > 0)
    {
        this.up('.box').down('input[name*=use][value=file]').checked = true;
        submitFile(this.up('.box'), e.dataTransfer.files[0]);
    }
}

function dropMultipleFiles(files)
{
    for (var i = 0; i < files.length; i++)
    {
        addNewFile();
        submitFile($('amfile-uploads').down('.box:last'), files[i]);
    }
}

function useDefault()
{
    var input = $(this).up('td').down('input[type=text],select');

    if (this.checked)
        input.disable();
    else
        input.enable();
}

function removeFile()
{
    if (confirm("Are you sure ?"))
    {
        var box = this.up('.box');

        box.down('.delete-input').value = 1;
        box.setStyle({display: 'none'});
    }
}

function addNewFile()
{
    var block = amfile_new_upload_template.clone(true);
    var id = --amfile_new_upload_id;

    block = block.outerHTML.replace(/\[-\d\]/g, '['+id+']');
    $('amfile-uploads').down('.container').insert({bottom: block});

    updateFileBoxes();
}
