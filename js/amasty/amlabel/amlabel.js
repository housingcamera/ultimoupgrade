
var amLabelPosition = new Class.create();
amLabelPosition.prototype = {
    name: null,
    element: null,
    table: null,
    tds: null,

    initialize: function (name) {
        this.name = name;
        this.element = $(name);
        this.table = $('amlabel-table-' + this.name);
        if(!this.table || !this.element) {
            return;
        }
        this.element.hide();

        var self = this;
        this.tds = this.table.select('td');
        this.tds.each(function(item){
            item.observe('click', function(){ self.tdClick(item)});
        });

        var currentValue = this.element.value;
        if(currentValue) {
            td = this.getElementByIndex(parseInt(currentValue));
            td.addClassName('selected');
        }

    },

    tdClick: function(item){
        var value = this.index(item, 1) - 1;
        if(value >= 0) {
            this.element.value = value;
            this.tds.each(function(td){
                td.removeClassName('selected');
            });
            item.addClassName('selected');

            var type = this.element.id.replace('_pos', '');
            var positionClass = positionClasses[this.element.value];
            var textBlock = $(type + '_preview').select('.amlabel-txt2')[0];

            $(textBlock).className = "amlabel-txt2";
            $(textBlock).addClassName(positionClass);
        }
    },

    getElementByIndex: function(currentValue){
       var col = Math.floor(currentValue/3);
       var cell = currentValue % 3;
       var element = this.table.select('tr:nth-child(' + (col + 1) + ') td:nth-child('+ (cell + 1) + ')')[0];
       return element;
    },

    index: function(node, parent) {
        var index = 0;
        var siblings = node.parentNode.childNodes;
        for (var j in siblings) if (siblings.hasOwnProperty(j)) {
            if (siblings[j].nodeType != Node.ELEMENT_NODE) {
                continue;
            }
            ++index;
            if (siblings[j] == node) {
                break;
            }
        }
        if(parent) {
            index += (this.index(node.parentNode, 0) - 1) * 3;
        }
        return index || -1;
    }
}

var positionClasses = [
    'top-left','top-center', 'top-right', 'middle-left','middle-center',
    'middle-right', 'bottom-left', 'bottom-center', 'bottom-right'
]

document.observe("dom:loaded", function() {
    try {
        var cat_color = new colorPicker('cat_color_wheel', {
            inputElement:'cat_color',
            color: '#' + $('cat_color').value,
            previewElement : 'cat_color'
        });
        var prod_color = new colorPicker('prod_color_wheel', {
            inputElement:'prod_color',
            color: '#' + $('prod_color').value,
            previewElement : 'prod_color'
        });
        var prod_label_color = new colorPicker('prod_label_color_wheel', {
            inputElement:'prod_label_color',
            color: '#' + $('prod_label_color').value,
            previewElement : 'prod_label_color'
        });
        var cat_label_color = new colorPicker('cat_label_color_wheel', {
            inputElement:'cat_label_color',
            color: '#' + $('cat_label_color').value,
            previewElement : 'cat_label_color'
        });
    }catch(ex){
        console.log(ex);
    }

    amLabelPreviewInit('prod');
    amLabelPreviewInit('cat');

    amLabelCreateCustomObservers('prod');
    amLabelCreateCustomObservers('cat');

    $$('.amlabel-choose-container input').each(function(item){
        item.observe('click', function(){
            if (item.value.indexOf('shape') >= 0) {
                var hide = $('amlabel-' + item.value.replace('shape', 'download'));
                var show = $('amlabel-' + item.value);
                type = item.value.replace('shape', '').replace('_img', '');
                $(type + '_label_color').up('tr').show();
            }
            else{
                var hide = $('amlabel-' + item.value.replace('download', 'shape'));
                var show = $('amlabel-' + item.value);
                type = item.value.replace('download', '').replace('_img', '');
                $(type + '_label_color').up('tr').hide();
            }
            show.show();
            hide.hide();

            $$('.amlabel-shapes-container-clone').each(function(clone){
                var shapeBlock  = clone.parentNode.select('.amlabel-shapes-container')[0];
                var height = shapeBlock.getHeight();
                if(height) {
                    $(clone).setStyle({
                        'height' : height + 'px'
                    });
                }
            });
        });
    });
    
    $$('.amlabel-choose-container input::checked').each(function(item){
        $(item).click();
    });

    /*return to current tab functionality*/
    $$('.tab-item-link').each(function(item){
        $(item).observe('click', function(){
            $('open_tab_input').value = this.id;
        });
    });
});

Event.observe(window, 'load', function() {
    $$('#labelTabs_product, #labelTabs_category').each(function(item){
        $(item).observe('click', function(){
            $$('.amlabel-shapes-container-clone').each(function(clone){
                var shapeBlock  = clone.parentNode.select('.amlabel-shapes-container')[0];
                var height = shapeBlock.getHeight();
                if(height) {
                    $(clone).setStyle({
                        'height' : height + 'px'
                    });
                }
            });
            amLabelSetCorrectHeight();
        });
    });
});

amLabelSetCorrectHeight = function () {
    $$('.amlabel-txt2').each(function(item){
        imageSrc = $(item).getStyle('background-image').replace(/url\((['"])?(.*?)\1\)/gi, '$2')
            .split(',')[0];
        var image = new Image();
        image.src = imageSrc;

        var width = image.width,
            height = image.height;
        if(width && height) {
            var heightNew = item.getWidth() *   height/width;
            if(heightNew) {
                $(item).setStyle({
                    'height' : heightNew + 'px'
                });
            }
        }
    });
}

amLabelCreateCustomObservers = function (type) {
    var productPreview = $(type + '_preview');
    if (productPreview) {
        var width = $(type + '_image_width');
        width.observe('blur', function(){
            var type = this.id.replace('_image_width', '');
            var textBlock = $(type + '_preview').select('.amlabel-txt2')[0];
            $(textBlock).setStyle({
                width: this.value + '%'
            });
            amLabelSetCorrectHeight();
        });

        var color = $(type + '_color');
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                var type = mutation.target.id.replace('_color', '');
                var textBlock = $(type + '_preview').select('.amlabel-txt2')[0];
                $(textBlock).setStyle({
                    color:  '#' + mutation.target.value
                });
            });
        });
        var config = { attributes: true, attributeFilter: ['style'] };
        observer.observe(color, config);

        var size = $(type + '_size');
        size.observe('change', function(){
            var type = this.id.replace('_size', '');
            var textBlock = $(type + '_preview').select('.amlabel-txt2')[0];
            $(textBlock).setStyle({
                fontSize:  this.value
            });
        });

        var txt = $(type + '_txt');
        txt.observe('change', function(){
            var type = this.id.replace('_txt', '');
            var textBlock = $(type + '_preview').select('.amlabel-txt')[0];
            $(textBlock).innerHTML = this.value.replace(new RegExp("{(.*?)}", 'g'), '00');
        });

        var style = $(type + '_style');
        style.observe('change', function(){
            var type = this.id.replace('_style', '');
            var textBlock = $(type + '_preview').select('.amlabel-txt2')[0];
            var oldStyle = $(textBlock).getAttribute('style');
            var newStyle = this.value;
            newStyle = newStyle.split(";");
            for(item in newStyle) {
                if(parseInt(item) >= 0) {
                    var st = newStyle[item];
                    var styleName = st.substring(0, st.indexOf(':'));
                    if(styleName) {
                        if(oldStyle.indexOf(styleName) !== -1) {
                            regexp = new RegExp( styleName + "(.*?);");
                            oldStyle = oldStyle.replace(regexp, st + ';');
                        }
                        else{
                            oldStyle += st + ";";
                        }
                    }
                }
            }
            $(textBlock).setAttribute('style', oldStyle);
        });
    }
}

amLabelPreviewInit = function (type) {
    var productPreview = $(type + '_preview');
    if (productPreview) {
        var textBlock = productPreview.select('.amlabel-txt2')[0];
        var iconImage = $('image_preview' + type + '_img');
        if (iconImage) {
            $(textBlock).setStyle({
                'background' : "url('" + iconImage.src + "') no-repeat 0 0",
                'max-width'  : iconImage.naturalWidth + 'px',
                'max-height' : iconImage.naturalHeight + 'px'
            });
        }

        var positionClass = positionClasses[$(type + '_pos').value];
        $(textBlock).addClassName(positionClass);

        var width = $(type + '_image_width');
        if (width && width.value) {
            $(textBlock).setStyle({
                width: width.value + '%'
            });
        }

        var additionalStyles = $(type + '_style').value;
        $(textBlock).setAttribute('style', $(textBlock).getAttribute('style') + additionalStyles);
        var text = productPreview.select('.amlabel-txt')[0];
        text.innerHTML = $(type + '_txt').value.replace(new RegExp("{(.*?)}", 'g'), '00');

        /*add position absolute if it can be moved to the right*/
        var horScroll = productPreview.up('.hor-scroll');
        horScroll.setStyle({
            'position' : 'relative'
        });
        if ($$('.main-col-inner')[0] &&
            $$('.main-col-inner')[0].getWidth() > 750){
            productPreview.setStyle({
                'position' : 'absolute',
                'left' : '520px',
                'bottom' : '23px'
            });
        }
    }
}

