/*  Returning Customers from Adroll Source=returning Coupon Code Bar */
function getQueryVariable(variable) {
	var query = window.location.search.substring(1);
	var vars = query.split("&");
	for (var i=0;i<vars.length;i++) {
		var pair = vars[i].split("=");
		if (pair[0] == variable) {
		  return pair[1];
		}
	} 
}
function closeTopBar() {
	jQuery( ".custom-top-bar").hide();
	jQuery.removeCookie("source");
  }

/* END Returning Customers from Adroll Source=returning Coupon Code Bar */