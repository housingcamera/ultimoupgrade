    
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

Event.observe(window, 'load', function() {
    amLabelSetCorrectHeight();
});