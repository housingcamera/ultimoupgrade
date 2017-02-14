var $testimonialSidebar = jQuery.noConflict();				  
$testimonialSidebar(document).ready(function() {				
	$testimonialSidebar('#testimonialSidebar').bxSlider({					
		auto: true,					
		mode: 'fade',					
		speed: 2000,					
		preloadImages: 'visible',					
		controls: false,						
		pager: true				  
	});			  
});	