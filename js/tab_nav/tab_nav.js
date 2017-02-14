jQuery(function() {
			jQuery('#navigation a').stop().animate({'marginLeft':'-70px'},1000);
		  
			jQuery('#navigation > li').hover(
				function () {
					jQuery('a',jQuery(this)).stop().animate({'marginLeft':'-2px'},200);
				},
				function () {
					jQuery('a',jQuery(this)).stop().animate({'marginLeft':'-70px'},200);
				}
			);
		  });
		  