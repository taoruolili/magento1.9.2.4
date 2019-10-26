(function ($) {
    $(document).ready(function(){
        // use live() to handle dynamically added buttons (pre jQuery v1.7)
        $("form").on("click", "button", function(){
            var f = $(this).get(0).form;

            if (typeof(f) !== 'undefined') {
                if (this.type && this.type != 'submit')
                    return;

                $("input[type='hidden'][name='"+this.name+"']", f).remove();

                if (typeof(this.attributes.value) !== 'undefined')
                    $(f)
                        .append('<input name="'+this.name+
                '" value="'+this.attributes.value.value+'" type="hidden">');

                $(f).trigger('submit');
            }
        });
    });
})(DRjQuery)
