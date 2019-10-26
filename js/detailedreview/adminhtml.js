function onUseParentChangedHandler(element) {
    var useParent = (element.value == 1) ? true : false;
    var dependencies = {
        'use_parent_review_settings': 'review_fields_available',
        'use_parent_proscons_settings': ['pros', 'cons']
    }

    for(var cssClass in dependencies) {
        if(element.hasClassName(cssClass)) {
            var dependentAttrs = dependencies[cssClass];
            break;
        }
    }

    var changeState = function(el, idNamePart) {
        if(el.id.indexOf(idNamePart) !== -1) {
            el.disabled = useParent;
        }
    }

    element.up(2).select('select[multiple]').each(function(el) {
        if(typeof dependentAttrs == 'string') {
            changeState(el, dependentAttrs);
        } else {
            for(index in dependentAttrs) {
                changeState(el, dependentAttrs[index]);
            }
        }
    });
}