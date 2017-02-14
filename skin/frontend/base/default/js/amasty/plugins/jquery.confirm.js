document.observe("dom:loaded", function() {
AmAjaxShoppCartLoad();

    (function($){

    $.confirm = function(params){
	    if($('#confirmOverlay', window.parent.document).length > 0){
		    // A confirm is already shown on the page:
		    return false;
	    }
	    
	    var buttonHTML = '';
	    $.each(params.buttons,function(name,obj){
		    
		    // Generating the markup for the buttons:
		    
		    buttonHTML += '<button class="'+obj['class']+'"><span><span>'+obj['name']+'</span></span></button>';
		    
		    if(!obj.action){
			    obj.action = function(){};
		    }
	    });
        
        var confirmOverlay = document.createElement('div');
        confirmOverlay = $(confirmOverlay); // fix for IE
        confirmOverlay.attr('id','confirmOverlay');

        var hideDiv = document.createElement('div');
        hideDiv = $(hideDiv); // fix for IE
        hideDiv.attr('id','hideDiv');
        hideDiv.appendTo(confirmOverlay);
        
        var confirmBox = document.createElement('div');
        confirmBox = $(confirmBox); // fix for IE
        confirmBox.attr('id','confirmBox');
        switch(AmAjaxObj.options['align']){
            case "1":
                confirmBox.css('top', '130px'); 
                confirmBox.css('left', '50%'); 
                break;
            case "2":
                confirmBox.css('top', '130px'); 
                confirmBox.css('left', '230px'); 
                 break;
            case "3":
                confirmBox.css('top', '130px'); 
                confirmBox.css('right', '0px'); 
                 break;
            case "4":
                confirmBox.css('top', '30%'); 
                confirmBox.css('left', '230px'); 
                break;
            case "5":
                confirmBox.css('top', '30%'); 
                confirmBox.css('right', '0px'); 
                break;
            case "0":                
            default:
                confirmBox.css('top', '30%');    
                confirmBox.css('left', '50%');    
        }
        confirmBox.appendTo(confirmOverlay);
        
        var confirmButtons = document.createElement('div');
        confirmButtons = $(confirmButtons); // fix for IE
        confirmButtons.attr('id','confirmButtons');
        confirmButtons.html(buttonHTML);  
        confirmButtons.appendTo(confirmBox);
        
        var messageBox = document.createElement('div');
        messageBox = $(messageBox); // fix for IE
        messageBox.attr('id','messageBox');
        messageBox.html(params.message);  
        messageBox.insertBefore(confirmButtons);

        var title = document.createElement('h1');
        title = $(title); // fix for IE
        title.html(params.title);     
        title.insertBefore(messageBox);  
	    
	    confirmOverlay.hide().appendTo($('body', window.parent.document));
        
        confirmOverlay.fadeIn();
        var conWidth = confirmBox.width()/2;
        if(conWidth < 160) conWidth = 230;
        confirmBox.css('marginLeft', -conWidth);   
        hideDiv.click(function(){  $.confirm.hide();});
       // confirmOverlay.show("medium");
      //  confirmOverlay.slideDown("slow") ;
	    var buttons = $('#confirmButtons button', window.parent.document),
		    i = 0;

	    $.each(params.buttons,function(name,obj){
		    buttons.eq(i++).click(function(){
			    obj.action();
			    return false;
		    });
	    });
        $.confirm.timer();
    }

    $.confirm.timer = function(){
        var elem= $('#confirmButtons button:last-child', window.parent.document);
        var value = elem.text(); 
        var sec = parseInt(value.replace(/\D+/g,""));
        if(sec) 
            document.timer = setInterval("AmAjaxObj.oneSec();", 1000);
    }

    $.confirm.hide = function(){
        $('#confirmOverlay', window.parent.document).fadeOut(function(){
            var hasPopupOptions = $('#confirmBox .super-attribute-select', window.parent.document).length;
            $(this).remove();
            if($('.col-main', window.parent.document).length && hasPopupOptions){
                if('undefined' != typeof(currentOptionsPrice)){
                    optionsPrice = currentOptionsPrice;
                }
                if('undefined' != typeof(currentopConfig)){
                    opConfig = currentopConfig;
                }
                if('undefined' != typeof(currentspConfig)){
                    spConfig = currentspConfig;
                }
                //jQuery(function($){$('.col-main').html().replace(/var /g, "").evalScripts();})
            }
            if('undefined' != typeof(parent.jQuery.fancybox)){
                parent.jQuery.fancybox.close();
            }

        });
    }

    })(jQuery);
    
});