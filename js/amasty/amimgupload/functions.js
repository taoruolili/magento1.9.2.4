function resizeView(img)
{
    var imgObj = new Image();
    imgObj.src = img.src;
    if (imgObj.height > imgObj.width)
    {
        img.style.height = '150px';
        img.style.width  = 'auto';
    } else 
    {
        img.style.height = 'auto';
        img.style.width  = '150px';
    }
}

function itemOver(item)
{
    item.select('.actions span').each(function(elem){
        elem.style.display = '';
    });
}

function itemOut(item)
{
    item.select('.actions span').each(function(elem){
        elem.style.display = 'none';
    });
}

function enableImage(link)
{
    link.parentNode.select('input.img-disable-input').each(function(input){
        input.value = 0;
    });
    link.parentNode.parentNode.parentNode.removeClassName('img-disabled');
}

function disableImage(link)
{
    link.parentNode.select('input.img-disable-input').each(function(input){
        input.value = 1;
    });
    link.parentNode.parentNode.parentNode.addClassName('img-disabled');
}

function deleteImage(link)
{
    link.parentNode.select('input.img-delete-input').each(function(input){
        input.value = 1;
    });
    link.parentNode.parentNode.parentNode.style.display = 'none';
}

Draggables.register = function(draggable) {
//    if(this.drags.length == 0) {
        this.eventMouseUp   = this.endDrag.bindAsEventListener(this);
        this.eventMouseMove = this.updateDrag.bindAsEventListener(this);
        this.eventKeypress  = this.keyPress.bindAsEventListener(this);

        Event.observe(document, "mouseup", this.eventMouseUp);
        Event.observe(draggable.element, "mousemove", this.eventMouseMove);
        Event.observe(document, "keypress", this.eventKeypress);
//    }
    this.drags.push(draggable);
};

Draggables.unregister = function(draggable) {
    this.drags = this.drags.reject(function(d) { return d==draggable });
//    if(this.drags.length == 0) {
      Event.stopObserving(document, "mouseup", this.eventMouseUp);
      Event.stopObserving(draggable.element, "mousemove", this.eventMouseMove);
      Event.stopObserving(document, "keypress", this.eventKeypress);
//    }
}